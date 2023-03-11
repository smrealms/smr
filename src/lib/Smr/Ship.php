<?php declare(strict_types=1);

namespace Smr;

use Smr\Combat\Weapon\Weapon;

/**
 * Adds a database layer to an AbstractShip instance.
 * Loads and saves ship properties from/to the database.
 */
class Ship extends AbstractShip {

	/** @var array<int, array<int, self>> */
	protected static array $CACHE_SHIPS = [];

	public const SQL = 'account_id = :account_id AND game_id = :game_id';
	/** @var array{account_id: int, game_id: int} */
	public readonly array $SQLID;

	public static function clearCache(): void {
		self::$CACHE_SHIPS = [];
	}

	public static function saveShips(): void {
		foreach (self::$CACHE_SHIPS as $gameShips) {
			foreach ($gameShips as $ship) {
				$ship->update();
			}
		}
	}

	public static function getShip(AbstractPlayer $player, bool $forceUpdate = false): self {
		if ($forceUpdate || !isset(self::$CACHE_SHIPS[$player->getGameID()][$player->getAccountID()])) {
			$s = new self($player);
			self::$CACHE_SHIPS[$player->getGameID()][$player->getAccountID()] = $s;
		}
		return self::$CACHE_SHIPS[$player->getGameID()][$player->getAccountID()];
	}

	protected function __construct(AbstractPlayer $player) {
		parent::__construct($player);
		$db = Database::getInstance();
		$this->SQLID = [
			'account_id' => $db->escapeNumber($this->getAccountID()),
			'game_id' => $db->escapeNumber($this->getGameID()),
		];

		$this->loadHardware();
		$this->loadWeapons();
		$this->loadCargo();
		$this->loadCloak();
		$this->loadIllusion();
	}

	public function update(): void {
		$this->updateHardware();
		$this->updateWeapons();
		$this->updateCargo();
		$this->updateCloak();
		$this->updateIllusion();
		// note: Ship::setTypeID updates the Player only
		$this->getPlayer()->update();
	}

	/**
	 * Initialize the weapons onboard this ship.
	 */
	protected function loadWeapons(): void {
		// determine weapon
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM ship_has_weapon JOIN weapon_type USING (weapon_type_id)
							WHERE ' . self::SQL . '
							ORDER BY order_id LIMIT :limit', [
			...$this->SQLID,
			'limit' => $db->escapeNumber($this->getHardpoints()),
		]);

		$this->weapons = [];
		// generate list of weapon names the user transports
		foreach ($dbResult->records() as $dbRecord) {
			$weaponTypeID = $dbRecord->getInt('weapon_type_id');
			$orderID = $dbRecord->getInt('order_id');
			$weapon = Weapon::getWeapon($weaponTypeID, $dbRecord);
			$weapon->setBonusAccuracy($dbRecord->getBoolean('bonus_accuracy'));
			$weapon->setBonusDamage($dbRecord->getBoolean('bonus_damage'));
			$this->weapons[$orderID] = $weapon;
		}
		$this->checkForExcessWeapons();
	}

	protected function loadHardware(): void {
		$this->hardware = [];

		// get currently hardware from db
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT *
							FROM ship_has_hardware
							JOIN hardware_type USING(hardware_type_id)
							WHERE ' . self::SQL, $this->SQLID);

		foreach ($dbResult->records() as $dbRecord) {
			$hardwareTypeID = $dbRecord->getInt('hardware_type_id');

			// adding hardware to array
			$this->hardware[$hardwareTypeID] = $dbRecord->getInt('amount');
		}
		$this->checkForExcessHardware();
	}

	protected function loadCargo(): void {
		// initialize cargo array
		$this->cargo = [];

		// get cargo from db
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM ship_has_cargo WHERE ' . self::SQL, $this->SQLID);
		foreach ($dbResult->records() as $dbRecord) {
			// adding cargo and amount to array
			$this->cargo[$dbRecord->getInt('good_id')] = $dbRecord->getInt('amount');
		}
		$this->checkForExcessCargo();
	}

	public function updateCargo(): void {
		if ($this->hasChangedCargo === false) {
			return;
		}
		// write cargo info
		$db = Database::getInstance();
		foreach ($this->getCargo() as $id => $amount) {
			if ($amount > 0) {
				$db->replace('ship_has_cargo', [
					...$this->SQLID,
					'good_id' => $id,
					'amount' => $amount,
				]);
			} else {
				$db->delete('ship_has_cargo', [
					...$this->SQLID,
					'good_id' => $id,
				]);
				// Unset now to omit displaying this good with 0 amount
				// before the next page is loaded.
				unset($this->cargo[$id]);
			}
		}
		$this->hasChangedCargo = false;
	}

	public function updateHardware(): void {
		// write hardware info only for hardware that has changed
		$db = Database::getInstance();
		foreach ($this->hasChangedHardware as $hardwareTypeID => $hasChanged) {
			if ($hasChanged === false) {
				continue;
			}
			$amount = $this->getHardware($hardwareTypeID);
			if ($amount > 0) {
				$db->replace('ship_has_hardware', [
					...$this->SQLID,
					'hardware_type_id' => $hardwareTypeID,
					'amount' => $amount,
				]);
			} else {
				$db->delete('ship_has_hardware', [
					...$this->SQLID,
					'hardware_type_id' => $hardwareTypeID,
				]);
			}
		}
		$this->hasChangedHardware = [];
	}

	private function updateWeapons(): void {
		if ($this->hasChangedWeapons === false) {
			return;
		}
		// write weapon info
		$db = Database::getInstance();
		$db->delete('ship_has_weapon', $this->SQLID);
		foreach ($this->weapons as $orderID => $weapon) {
			$db->insert('ship_has_weapon', [
				...$this->SQLID,
				'order_id' => $orderID,
				'weapon_type_id' => $weapon->getWeaponTypeID(),
				'bonus_accuracy' => $db->escapeBoolean($weapon->hasBonusAccuracy()),
				'bonus_damage' => $db->escapeBoolean($weapon->hasBonusDamage()),
			]);
		}
		$this->hasChangedWeapons = false;
	}

	public function loadCloak(): void {
		$this->isCloaked = false;
		if ($this->hasCloak() === false) {
			return;
		}
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM ship_is_cloaked WHERE ' . self::SQL, $this->SQLID);
		$this->isCloaked = $dbResult->hasRecord();
	}

	public function updateCloak(): void {
		if ($this->hasChangedCloak === false) {
			return;
		}
		$db = Database::getInstance();
		if ($this->isCloaked === false) {
			$db->delete('ship_is_cloaked', $this->SQLID);
		} else {
			$db->insert('ship_is_cloaked', $this->SQLID);
		}
		$this->hasChangedCloak = false;
	}

	public function loadIllusion(): void {
		$this->illusionShip = false;
		if ($this->hasIllusion() === false) {
			return;
		}
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM ship_has_illusion WHERE ' . self::SQL, $this->SQLID);
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$this->illusionShip = new ShipIllusion(
				shipTypeID: $dbRecord->getInt('ship_type_id'),
				attackRating: $dbRecord->getInt('attack'),
				defenseRating: $dbRecord->getInt('defense'),
			);
		}
	}

	public function updateIllusion(): void {
		if ($this->hasChangedIllusion === false) {
			return;
		}
		$db = Database::getInstance();
		if ($this->illusionShip === false) {
			$db->delete('ship_has_illusion', $this->SQLID);
		} else {
			$db->replace('ship_has_illusion', [
				...$this->SQLID,
				'ship_type_id' => $this->illusionShip->shipTypeID,
				'attack' => $this->illusionShip->attackRating,
				'defense' => $this->illusionShip->defenseRating,
			]);
		}
		$this->hasChangedIllusion = false;
	}

}
