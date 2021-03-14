<?php declare(strict_types=1);

/**
 * Adds a database layer to an AbstractSmrShip instance.
 * Loads and saves ship properties from/to the database.
 */
class SmrShip extends AbstractSmrShip {
	protected static array $CACHE_SHIPS = [];

	protected MySqlDatabase $db;
	protected string $SQL;

	public static function refreshCache() : void {
		foreach (self::$CACHE_SHIPS as &$gameShips) {
			foreach ($gameShips as &$ship) {
				$ship = self::getShip($ship->getPlayer(), true);
			}
		}
	}

	public static function clearCache() : void {
		self::$CACHE_SHIPS = array();
	}

	public static function saveShips() : void {
		foreach (self::$CACHE_SHIPS as $gameShips) {
			foreach ($gameShips as $ship) {
				$ship->update();
			}
		}
	}

	public static function getShip(AbstractSmrPlayer $player, bool $forceUpdate = false) : self {
		if ($forceUpdate || !isset(self::$CACHE_SHIPS[$player->getGameID()][$player->getAccountID()])) {
			$s = new SmrShip($player);
			self::$CACHE_SHIPS[$player->getGameID()][$player->getAccountID()] = $s;
		}
		return self::$CACHE_SHIPS[$player->getGameID()][$player->getAccountID()];
	}

	protected function __construct(AbstractSmrPlayer $player) {
		parent::__construct($player);
		$this->db = MySqlDatabase::getInstance();
		$this->SQL = 'account_id=' . $this->db->escapeNumber($this->getAccountID()) . ' AND game_id=' . $this->db->escapeNumber($this->getGameID());

		$this->loadHardware();
		$this->loadWeapons();
		$this->loadCargo();
		$this->loadCloak();
		$this->loadIllusion();
	}

	public function update() : void {
		$this->updateHardware();
		$this->updateWeapons();
		$this->updateCargo();
		$this->updateCloak();
		$this->updateIllusion();
		// note: SmrShip::setShipTypeID updates the SmrPlayer only
		$this->getPlayer()->update();
	}

	/**
	 * Initialize the weapons onboard this ship.
	 */
	protected function loadWeapons() : void {
		// determine weapon
		$this->db->query('SELECT * FROM ship_has_weapon JOIN weapon_type USING (weapon_type_id)
							WHERE ' . $this->SQL . '
							ORDER BY order_id LIMIT ' . $this->db->escapeNumber($this->getHardpoints()));

		$this->weapons = array();
		// generate list of weapon names the user transports
		while ($this->db->nextRecord()) {
			$weaponTypeID = $this->db->getInt('weapon_type_id');
			$orderID = $this->db->getInt('order_id');
			$weapon = SmrWeapon::getWeapon($weaponTypeID, $this->db);
			$weapon->setBonusAccuracy($this->db->getBoolean('bonus_accuracy'));
			$weapon->setBonusDamage($this->db->getBoolean('bonus_damage'));
			$this->weapons[$orderID] = $weapon;
		}
		$this->checkForExcessWeapons();
	}

	protected function loadHardware() : void {
		$this->hardware = array();
		$this->oldHardware = array();

		// get currently hardware from db
		$this->db->query('SELECT *
							FROM ship_has_hardware
							JOIN hardware_type USING(hardware_type_id)
							WHERE ' . $this->SQL);

		while ($this->db->nextRecord()) {
			$hardwareTypeID = $this->db->getInt('hardware_type_id');

			// adding hardware to array
			$this->hardware[$hardwareTypeID] = $this->db->getInt('amount');
			$this->oldHardware[$hardwareTypeID] = $this->db->getInt('old_amount');
		}
		$this->checkForExcessHardware();
	}

	protected function loadCargo() : void {
		// initialize cargo array
		$this->cargo = array();

		// get cargo from db
		$this->db->query('SELECT * FROM ship_has_cargo WHERE ' . $this->SQL);
		while ($this->db->nextRecord()) {
			// adding cargo and amount to array
			$this->cargo[$this->db->getInt('good_id')] = $this->db->getInt('amount');
		}
		$this->checkForExcessCargo();
	}

	public function updateCargo() : void {
		if ($this->hasChangedCargo === false) {
			return;
		}
		// write cargo info
		foreach ($this->getCargo() as $id => $amount) {
			if ($amount > 0) {
				$this->db->query('REPLACE INTO ship_has_cargo (account_id, game_id, good_id, amount) VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($id) . ', ' . $this->db->escapeNumber($amount) . ')');
			} else {
				$this->db->query('DELETE FROM ship_has_cargo WHERE ' . $this->SQL . ' AND good_id = ' . $this->db->escapeNumber($id) . ' LIMIT 1');
				// Unset now to omit displaying this good with 0 amount
				// before the next page is loaded.
				unset($this->cargo[$id]);
			}
		}
		$this->hasChangedCargo = false;
	}

	public function updateHardware() : void {
		// write hardware info only for hardware that has changed
		foreach ($this->hasChangedHardware as $hardwareTypeID => $hasChanged) {
			if ($hasChanged === false) {
				continue;
			}
			$amount = $this->getHardware($hardwareTypeID);
			if ($amount > 0) {
				$this->db->query('REPLACE INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($hardwareTypeID) . ', ' . $this->db->escapeNumber($amount) . ', ' . $this->db->escapeNumber($this->getOldHardware($hardwareTypeID)) . ')');
			} else {
				$this->db->query('DELETE FROM ship_has_hardware WHERE ' . $this->SQL . ' AND hardware_type_id = ' . $this->db->escapeNumber($hardwareTypeID));
			}
		}
		$this->hasChangedHardware = array();
	}

	private function updateWeapons() : void {
		if ($this->hasChangedWeapons === false) {
			return;
		}
		// write weapon info
		$this->db->query('DELETE FROM ship_has_weapon WHERE ' . $this->SQL);
		foreach ($this->weapons as $orderID => $weapon) {
			$this->db->query('INSERT INTO ship_has_weapon (account_id, game_id, order_id, weapon_type_id, bonus_accuracy, bonus_damage)
							VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($orderID) . ', ' . $this->db->escapeNumber($weapon->getWeaponTypeID()) . ', ' . $this->db->escapeBoolean($weapon->hasBonusAccuracy()) . ', ' . $this->db->escapeBoolean($weapon->hasBonusDamage()) . ')');
		}
		$this->hasChangedWeapons = false;
	}

	public function loadCloak() : void {
		$this->isCloaked = false;
		if ($this->hasCloak() === false) {
			return;
		}
		$this->db->query('SELECT 1 FROM ship_is_cloaked WHERE ' . $this->SQL . ' LIMIT 1');
		$this->isCloaked = $this->db->getNumRows() > 0;
	}

	public function updateCloak() : void {
		if ($this->hasChangedCloak === false) {
			return;
		}
		if ($this->isCloaked === false) {
			$this->db->query('DELETE FROM ship_is_cloaked WHERE ' . $this->SQL . ' LIMIT 1');
		} else {
			$this->db->query('INSERT INTO ship_is_cloaked VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ')');
		}
	}

	public function loadIllusion() : void {
		$this->illusionShip = false;
		if ($this->hasIllusion() === false) {
			return;
		}
		$this->db->query('SELECT * FROM ship_has_illusion WHERE ' . $this->SQL . ' LIMIT 1');
		if ($this->db->nextRecord()) {
			$this->illusionShip = [
				'ID' => $this->db->getInt('ship_type_id'),
				'Attack' => $this->db->getInt('attack'),
				'Defense' => $this->db->getInt('defense'),
			];
		}
	}

	public function updateIllusion() : void {
		if ($this->hasChangedIllusion === false) {
			return;
		}
		if ($this->illusionShip === false) {
			$this->db->query('DELETE FROM ship_has_illusion WHERE ' . $this->SQL . ' LIMIT 1');
		} else {
			$this->db->query('REPLACE INTO ship_has_illusion VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($this->illusionShip['ID']) . ', ' . $this->db->escapeNumber($this->illusionShip['Attack']) . ', ' . $this->db->escapeNumber($this->illusionShip['Defense']) . ')');
		}
		$this->hasChangedIllusion = false;
	}

}
