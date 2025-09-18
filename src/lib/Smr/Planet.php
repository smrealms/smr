<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\Combat\Weapon\CombatDrones;
use Smr\Combat\Weapon\Weapon;
use Smr\Exceptions\UserError;
use Smr\Pages\Player\AttackPlanetProcessor;
use Smr\Pages\Player\ExaminePlanet;
use Smr\Pages\Player\Planet\BondConfirm;
use Smr\Pages\Player\Planet\ConstructionProcessor;
use Smr\Pages\Player\Planet\FinancialProcessor;
use Smr\Pages\Player\Planet\LandProcessor;
use Smr\PlanetTypes\PlanetType;

class Planet {

	/** @var array<int, array<int, self>> */
	protected static array $CACHE_PLANETS = [];

	public const int DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE = 100;
	protected const int CHANCE_TO_DOWNGRADE = 15; // percent
	protected const int TIME_TO_CREDIT_BUST = 10800; // 3 hours
	protected const int TIME_ATTACK_NEWS_COOLDOWN = 3600; // 1 hour
	public const int MAX_STOCKPILE = 600;

	public const string SQL = 'game_id = :game_id AND sector_id = :sector_id';
	/** @var array{game_id: int, sector_id: int} */
	public readonly array $SQLID;

	protected bool $exists = false;
	protected string $planetName;
	protected int $ownerID;
	protected string $password;
	protected int $shields;
	protected int $armour;
	protected int $drones;
	protected int $credits;
	protected int $bonds;
	protected int $maturity;
	/** @var array<int, int> */
	protected array $stockpile;
	/** @var array<int, int> */
	protected array $buildings;
	protected int $inhabitableTime;
	/** @var array<int, array<string, int>> */
	protected array $currentlyBuilding;
	/** @var array<int, Weapon> */
	protected array $mountedWeapons;
	protected int $typeID;
	protected PlanetType $typeInfo;

	protected bool $hasChanged = false;
	protected bool $hasChangedFinancial = false; // for credits, bond, maturity
	protected bool $hasChangedStockpile = false;
	/** @var array<int, bool> */
	protected array $hasChangedWeapons = [];
	/** @var array<int, bool> */
	protected array $hasChangedBuildings = [];
	/** @var array<int> */
	protected array $hasStoppedBuilding = [];

	public function __sleep() {
		return ['sectorID', 'gameID', 'planetName', 'ownerID', 'typeID'];
	}

	public static function clearCache(): void {
		self::$CACHE_PLANETS = [];
	}

	public static function savePlanets(): void {
		foreach (self::$CACHE_PLANETS as $gamePlanets) {
			foreach ($gamePlanets as $planet) {
				$planet->update();
			}
		}
	}

	/**
	 * @return array<int, self>
	 */
	public static function getGalaxyPlanets(int $gameID, int $galaxyID, bool $forceUpdate = false): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT planet.* FROM planet LEFT JOIN sector USING (game_id, sector_id) WHERE game_id = :game_id AND galaxy_id = :galaxy_id', [
			'game_id' => $db->escapeNumber($gameID),
			'galaxy_id' => $db->escapeNumber($galaxyID),
		]);
		$galaxyPlanets = [];
		foreach ($dbResult->records() as $dbRecord) {
			$sectorID = $dbRecord->getInt('sector_id');
			$galaxyPlanets[$sectorID] = self::getPlanet($gameID, $sectorID, $forceUpdate, $dbRecord);
		}
		return $galaxyPlanets;
	}

	public static function getPlanet(int $gameID, int $sectorID, bool $forceUpdate = false, ?DatabaseRecord $dbRecord = null): self {
		if ($forceUpdate || !isset(self::$CACHE_PLANETS[$gameID][$sectorID])) {
			self::$CACHE_PLANETS[$gameID][$sectorID] = new self($gameID, $sectorID, $dbRecord);
		}
		return self::$CACHE_PLANETS[$gameID][$sectorID];
	}

	public static function createPlanet(int $gameID, int $sectorID, int $typeID = 1, ?int $inhabitableTime = null): self {
		if (self::getPlanet($gameID, $sectorID)->exists()) {
			throw new Exception('Planet already exists in sector ' . $sectorID . ' game ' . $gameID);
		}

		if ($inhabitableTime === null) {
			$minTime = Game::getGame($gameID)->getStartTime();
			$inhabitableTime = $minTime + pow(rand(45, 85), 3);
		}

		// insert planet into db
		$db = Database::getInstance();
		$db->insert('planet', [
			'game_id' => $gameID,
			'sector_id' => $sectorID,
			'inhabitable_time' => $inhabitableTime,
			'planet_type_id' => $typeID,
		]);
		return self::getPlanet($gameID, $sectorID, true);
	}

	public static function removePlanet(int $gameID, int $sectorID): void {
		$db = Database::getInstance();
		$SQLID = [
			'game_id' => $db->escapeNumber($gameID),
			'sector_id' => $db->escapeNumber($sectorID),
		];
		$db->delete('planet', $SQLID);
		$db->delete('planet_has_weapon', $SQLID);
		$db->delete('planet_has_cargo', $SQLID);
		$db->delete('planet_has_building', $SQLID);
		$db->delete('planet_is_building', $SQLID);
		//kick everyone from planet
		$db->update('player', ['land_on_planet' => 'FALSE'], $SQLID);

		unset(self::$CACHE_PLANETS[$gameID][$sectorID]);
	}

	protected function __construct(
		protected readonly int $gameID,
		protected readonly int $sectorID,
		?DatabaseRecord $dbRecord = null,
	) {
		$db = Database::getInstance();
		$this->SQLID = [
			'game_id' => $db->escapeNumber($gameID),
			'sector_id' => $db->escapeNumber($sectorID),
		];

		if ($dbRecord === null) {
			$dbResult = $db->read('SELECT * FROM planet WHERE ' . self::SQL, $this->SQLID);
			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
			}
		}

		if ($dbRecord !== null) {
			$this->exists = true;
			$this->planetName = $dbRecord->getString('planet_name');
			$this->ownerID = $dbRecord->getInt('owner_id');
			$this->password = $dbRecord->getString('password');
			$this->shields = $dbRecord->getInt('shields');
			$this->armour = $dbRecord->getInt('armour');
			$this->drones = $dbRecord->getInt('drones');
			$this->credits = $dbRecord->getInt('credits');
			$this->bonds = $dbRecord->getInt('bonds');
			$this->maturity = $dbRecord->getInt('maturity');
			$this->inhabitableTime = $dbRecord->getInt('inhabitable_time');
			$this->typeID = $dbRecord->getInt('planet_type_id');

			$this->typeInfo = PlanetType::getTypeInfo($this->getTypeID());
			$this->checkBondMaturity();
		}
	}

	public function getInterestRate(): float {
		$level = $this->getLevel();
		return match (true) {
			$level < 9 => .0404,
			$level < 19 => .0609,
			$level < 29 => .1236,
			$level < 39 => .050625,
			$level < 49 => .0404,
			$level < 59 => .030225,
			$level < 69 => .0201,
			default => .018081,
		};
	}

	public function checkBondMaturity(bool $partial = false): void {
		if ($this->getMaturity() > 0 && ($partial === true || $this->getMaturity() < Epoch::time())) {
			// calc the interest for the time
			$interest = $this->getBonds() * $this->getInterestRate();

			if ($partial === true && $this->getMaturity() > Epoch::time()) {
				// Adjust interest based upon how much of the bond duration has passed.
				$interest -= ($interest / $this->getBondTime()) * ($this->getMaturity() - Epoch::time());
			}

			// transfer money to free avail cash
			$this->increaseCredits($this->getBonds() + IFloor($interest));

			// reset bonds
			$this->setBonds(0);

			// reset maturity
			$this->setMaturity(0);
		}
	}

	public function getBondTime(): int {
		return IRound(BOND_TIME / $this->getGame()->getGameSpeed());
	}

	public function bond(): void {
		$this->checkBondMaturity(true);

		// add it to bond
		$this->increaseBonds($this->getCredits());

		// set free cash to 0
		$this->setCredits(0);

		// initialize time
		$this->setMaturity(Epoch::time() + $this->getBondTime());
	}

	public function getGameID(): int {
		return $this->gameID;
	}

	public function getGame(): Game {
		return Game::getGame($this->gameID);
	}

	public function getSectorID(): int {
		return $this->sectorID;
	}

	public function getGalaxy(): Galaxy {
		return Galaxy::getGalaxyContaining($this->getGameID(), $this->getSectorID());
	}

	public function getOwnerID(): int {
		return $this->ownerID;
	}

	public function hasOwner(): bool {
		return $this->ownerID !== 0;
	}

	public function removeOwner(): void {
		$this->setOwnerID(0);
	}

	public function setOwnerID(int $claimerID): void {
		if ($this->ownerID === $claimerID) {
			return;
		}
		$this->ownerID = $claimerID;
		$this->hasChanged = true;
	}

	public function getOwner(): AbstractPlayer {
		return Player::getPlayer($this->getOwnerID(), $this->getGameID());
	}

	public function getPassword(): string {
		return $this->password;
	}

	public function setPassword(string $password): void {
		if ($this->password === $password) {
			return;
		}
		$this->password = $password;
		$this->hasChanged = true;
	}

	public function removePassword(): void {
		$this->setPassword('');
	}

	public function getCredits(): int {
		return $this->credits;
	}

	public function setCredits(int $num): void {
		if ($this->credits === $num) {
			return;
		}
		if ($num < 0) {
			throw new Exception('You cannot set negative credits.');
		}
		if ($num > MAX_MONEY) {
			throw new Exception('You cannot set more than the max credits.');
		}
		$this->credits = $num;
		$this->hasChangedFinancial = true;
	}

	/**
	 * Increases planet credits up to the maximum allowed credits.
	 * Returns the amount that was actually added to handle overflow.
	 */
	public function increaseCredits(int $num): int {
		if ($num === 0) {
			return 0;
		}
		$newTotal = min($this->credits + $num, MAX_MONEY);
		$actualAdded = $newTotal - $this->credits;
		$this->setCredits($newTotal);
		return $actualAdded;
	}

	public function decreaseCredits(int $num): void {
		if ($num === 0) {
			return;
		}
		$newTotal = $this->credits - $num;
		$this->setCredits($newTotal);
	}

	public function getMaturity(): int {
		return $this->maturity;
	}

	public function setMaturity(int $num): void {
		if ($this->maturity === $num) {
			return;
		}
		if ($num < 0) {
			throw new Exception('You cannot set negative maturity.');
		}
		$this->maturity = $num;
		$this->hasChangedFinancial = true;
	}

	public function getBonds(): int {
		return $this->bonds;
	}

	public function setBonds(int $num): void {
		if ($this->bonds === $num) {
			return;
		}
		if ($num < 0) {
			throw new Exception('You cannot set negative bonds.');
		}
		$this->bonds = $num;
		$this->hasChangedFinancial = true;
	}

	public function increaseBonds(int $num): void {
		if ($num === 0) {
			return;
		}
		$this->setBonds($this->getBonds() + $num);
	}

	public function decreaseBonds(int $num): void {
		if ($num === 0) {
			return;
		}
		$this->setBonds($this->getBonds() - $num);
	}

	public function checkForExcessDefense(): void {
		if ($this->getShields() > $this->getMaxShields()) {
			$this->setShields($this->getMaxShields());
		}
		if ($this->getCDs() > $this->getMaxCDs()) {
			$this->setCDs($this->getMaxCDs());
		}
		if ($this->getArmour() > $this->getMaxArmour()) {
				$this->setArmour($this->getMaxArmour());
		}
		// Remove a random (0-indexed) mounted weapon, if over max mount slots
		while (count($this->getMountedWeapons()) > 0 && max(array_keys($this->getMountedWeapons())) >= $this->getMaxMountedWeapons()) {
			$removeID = array_rand($this->getMountedWeapons());
			$this->removeMountedWeapon($removeID);
			foreach ($this->getMountedWeapons() as $orderID => $weapon) {
				if ($orderID > $removeID) {
					$this->moveMountedWeaponUp($orderID);
				}
			}
		}
	}

	public function getShields(): int {
		return $this->shields;
	}

	public function hasShields(): bool {
		return $this->getShields() > 0;
	}

	public function setShields(int $shields): void {
		$shields = max(0, min($shields, $this->getMaxShields()));
		if ($this->shields === $shields) {
			return;
		}
		$this->shields = $shields;
		$this->hasChanged = true;
	}

	public function decreaseShields(int $number): void {
		if ($number === 0) {
			return;
		}
		$this->setShields($this->getShields() - $number);
	}

	public function increaseShields(int $number): void {
		if ($number === 0) {
			return;
		}
		$this->setShields($this->getShields() + $number);
	}

	public function getMaxShields(): int {
		return $this->getBuilding(PLANET_GENERATOR) * PLANET_GENERATOR_SHIELDS;
	}

	public function getArmour(): int {
		return $this->armour;
	}

	public function hasArmour(): bool {
		return $this->getArmour() > 0;
	}

	public function setArmour(int $armour): void {
		$armour = max(0, min($armour, $this->getMaxArmour()));
		if ($this->armour === $armour) {
			return;
		}
		$this->armour = $armour;
		$this->hasChanged = true;
	}

	public function decreaseArmour(int $number): void {
		if ($number === 0) {
			return;
		}
		$this->setArmour($this->getArmour() - $number);
	}

	public function increaseArmour(int $number): void {
		if ($number === 0) {
			return;
		}
		$this->setArmour($this->getArmour() + $number);
	}

	public function getMaxArmour(): int {
		return $this->getBuilding(PLANET_BUNKER) * PLANET_BUNKER_ARMOUR;
	}

	public function getCDs(): int {
		return $this->drones;
	}

	public function hasCDs(): bool {
		return $this->getCDs() > 0;
	}

	public function setCDs(int $combatDrones): void {
		$combatDrones = max(0, min($combatDrones, $this->getMaxCDs()));
		if ($this->drones === $combatDrones) {
			return;
		}
		$this->drones = $combatDrones;
		$this->hasChanged = true;
	}

	public function decreaseCDs(int $number): void {
		if ($number === 0) {
			return;
		}
		$this->setCDs($this->getCDs() - $number);
	}

	public function increaseCDs(int $number): void {
		if ($number === 0) {
			return;
		}
		$this->setCDs($this->getCDs() + $number);
	}

	public function getMaxCDs(): int {
		return $this->getBuilding(PLANET_HANGAR) * PLANET_HANGAR_DRONES;
	}

	public function getMaxMountedWeapons(): int {
		return $this->getBuilding(PLANET_WEAPON_MOUNT);
	}

	/**
	 * @return array<int, Weapon>
	 */
	public function getMountedWeapons(): array {
		if (!isset($this->mountedWeapons)) {
			$this->mountedWeapons = [];
			if ($this->hasBuilding(PLANET_WEAPON_MOUNT)) {
				$db = Database::getInstance();
				$dbResult = $db->read('SELECT * FROM planet_has_weapon JOIN weapon_type USING (weapon_type_id) WHERE ' . self::SQL, $this->SQLID);
				foreach ($dbResult->records() as $dbRecord) {
					$weaponTypeID = $dbRecord->getInt('weapon_type_id');
					$orderID = $dbRecord->getInt('order_id');
					$weapon = Weapon::getWeapon($weaponTypeID, $dbRecord);
					$weapon->setBonusAccuracy($dbRecord->getBoolean('bonus_accuracy'));
					$weapon->setBonusDamage($dbRecord->getBoolean('bonus_damage'));
					$this->mountedWeapons[$orderID] = $weapon;
				}
			}
		}
		return $this->mountedWeapons;
	}

	public function hasMountedWeapon(int $orderID): bool {
		$this->getMountedWeapons(); // Make sure array is initialized
		return isset($this->mountedWeapons[$orderID]);
	}

	public function addMountedWeapon(Weapon $weapon, int $orderID): void {
		$this->getMountedWeapons(); // Make sure array is initialized
		$this->mountedWeapons[$orderID] = $weapon;
		$this->hasChangedWeapons[$orderID] = true;
	}

	public function removeMountedWeapon(int $orderID): void {
		$this->getMountedWeapons(); // Make sure array is initialized
		unset($this->mountedWeapons[$orderID]);
		$this->hasChangedWeapons[$orderID] = true;
	}

	private function swapMountedWeapons(int $orderID1, int $orderID2): void {
		$this->getMountedWeapons(); // Make sure array is initialized
		if (isset($this->mountedWeapons[$orderID1])) {
			$saveWeapon = $this->mountedWeapons[$orderID1];
		}
		if (isset($this->mountedWeapons[$orderID2])) {
			$this->mountedWeapons[$orderID1] = $this->mountedWeapons[$orderID2];
		} else {
			unset($this->mountedWeapons[$orderID1]);
		}
		if (isset($saveWeapon)) {
			$this->mountedWeapons[$orderID2] = $saveWeapon;
		} else {
			unset($this->mountedWeapons[$orderID2]);
		}
		$this->hasChangedWeapons[$orderID1] = true;
		$this->hasChangedWeapons[$orderID2] = true;
	}

	public function moveMountedWeaponUp(int $orderID): void {
		if ($orderID === 0) {
			throw new Exception('Cannot move this weapon up!');
		}
		$this->swapMountedWeapons($orderID - 1, $orderID);
	}

	public function moveMountedWeaponDown(int $orderID): void {
		if ($orderID === $this->getMaxMountedWeapons() - 1) {
			throw new Exception('Cannot move this weapon down!');
		}
		$this->swapMountedWeapons($orderID + 1, $orderID);
	}

	public function isBusted(): bool {
		return !$this->hasCDs() && !$this->hasShields() && !$this->hasArmour();
	}

	public function exists(): bool {
		return $this->exists;
	}

	/**
	 * @return ($goodID is null ? array<int, int> : int)
	 */
	public function getStockpile(?int $goodID = null): int|array {
		if (!isset($this->stockpile)) {
			// initialize cargo array
			$this->stockpile = [];
			// get supplies from db
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT good_id, amount FROM planet_has_cargo WHERE ' . self::SQL, $this->SQLID);
			// adding cargo and amount to array
			foreach ($dbResult->records() as $dbRecord) {
				$this->stockpile[$dbRecord->getInt('good_id')] = $dbRecord->getInt('amount');
			}
		}
		if ($goodID === null) {
			return $this->stockpile;
		}
		return $this->stockpile[$goodID] ?? 0;
	}

	public function hasStockpile(?int $goodID = null): bool {
		if ($goodID === null) {
			$stockpile = $this->getStockpile();
			return count($stockpile) > 0 && max($stockpile) > 0;
		}
		return $this->getStockpile($goodID) > 0;
	}

	public function setStockpile(int $goodID, int $amount): void {
		if ($this->getStockpile($goodID) === $amount) {
			return;
		}
		if ($amount < 0) {
			throw new Exception('Trying to set negative stockpile.');
		}
		$this->stockpile[$goodID] = $amount;
		$this->hasChangedStockpile = true;
	}

	public function decreaseStockpile(int $goodID, int $amount): void {
		if ($amount < 0) {
			throw new Exception('Trying to decrease negative stockpile.');
		}
		$this->setStockpile($goodID, $this->getStockpile($goodID) - $amount);
	}

	public function increaseStockpile(int $goodID, int $amount): void {
		if ($amount < 0) {
			throw new Exception('Trying to increase negative stockpile.');
		}
		$this->setStockpile($goodID, $this->getStockpile($goodID) + $amount);
	}

	/**
	 * @return array<int, int>
	 */
	public function getBuildings(): array {
		if (!isset($this->buildings)) {
			$this->buildings = [];

			// get buildingss from db
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT construction_id, amount FROM planet_has_building WHERE ' . self::SQL, $this->SQLID);
			// adding building and amount to array
			foreach ($dbResult->records() as $dbRecord) {
				$this->buildings[$dbRecord->getInt('construction_id')] = $dbRecord->getInt('amount');
			}

			// Update building counts if construction has finished
			$this->getCurrentlyBuilding();
		}
		return $this->buildings;
	}

	public function getBuilding(int $buildingTypeID): int {
		$buildings = $this->getBuildings();
		return $buildings[$buildingTypeID] ?? 0;
	}

	public function hasBuilding(int $buildingTypeID): bool {
		return $this->getBuilding($buildingTypeID) > 0;
	}

	public function setBuilding(int $buildingTypeID, int $number): void {
		if ($this->getBuilding($buildingTypeID) === $number) {
			return;
		}
		if ($number < 0) {
			throw new Exception('Cannot set negative number of buildings.');
		}

		$this->buildings[$buildingTypeID] = $number;
		$this->hasChangedBuildings[$buildingTypeID] = true;
	}

	public function increaseBuilding(int $buildingTypeID, int $number): void {
		$this->setBuilding($buildingTypeID, $this->getBuilding($buildingTypeID) + $number);
	}

	public function destroyBuilding(int $buildingTypeID, int $number): void {
		$this->setBuilding($buildingTypeID, $this->getBuilding($buildingTypeID) - $number);
	}

	/**
	 * @return array<int, array<string, int>>
	 */
	public function getCurrentlyBuilding(): array {
		if (!isset($this->currentlyBuilding)) {
			$this->currentlyBuilding = [];

			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM planet_is_building WHERE ' . self::SQL, $this->SQLID);
			foreach ($dbResult->records() as $dbRecord) {
				$this->currentlyBuilding[$dbRecord->getInt('building_slot_id')] = [
					'BuildingSlotID' => $dbRecord->getInt('building_slot_id'),
					'ConstructionID' => $dbRecord->getInt('construction_id'),
					'ConstructorID' => $dbRecord->getInt('constructor_id'),
					'Finishes' => $dbRecord->getInt('time_complete'),
					'TimeRemaining' => $dbRecord->getInt('time_complete') - Epoch::time(),
				];
			}

			// Check if construction has completed
			foreach ($this->currentlyBuilding as $id => $building) {
				if ($building['TimeRemaining'] <= 0) {
					unset($this->currentlyBuilding[$id]);
					$expGain = $this->getConstructionExp($building['ConstructionID']);
					$player = Player::getPlayer($building['ConstructorID'], $this->getGameID());
					$player->increaseHOF(1, ['Planet', 'Buildings', 'Built'], HOF_ALLIANCE);
					$player->increaseExperience($expGain);
					$player->increaseHOF($expGain, ['Planet', 'Buildings', 'Experience'], HOF_ALLIANCE);
					$this->hasStoppedBuilding[] = $building['BuildingSlotID'];
					$this->increaseBuilding($building['ConstructionID'], 1);

					// WARNING: The above modifications to the player/planet are dangerous because
					// they may not be part of the current sector lock. But since they might not be,
					// we may as well just update now to avoid either a) needing to remember to call
					// this explicitly in all appropriate engine files or b) inconsistent exp display
					// if this is called during the template display only and therefore unsaved.
					$player->update();
					$this->update();
				}
			}
		}
		return $this->currentlyBuilding;
	}

	/**
	 * @return ($buildingTypeID is null ? array<int, int> : int)
	 */
	public function getMaxBuildings(?int $buildingTypeID = null): int|array {
		if ($buildingTypeID === null) {
			$maxBuildings = [];
			foreach ($this->getStructureTypes() as $ID => $type) {
				$maxBuildings[$ID] = $type->maxAmount();
			}
			return $maxBuildings;
		}
		return $this->getStructureTypes($buildingTypeID)->maxAmount();
	}

	public function getTypeID(): int {
		return $this->typeID;
	}

	public function setTypeID(int $num): void {
		if (isset($this->typeID) && $this->typeID === $num) {
			return;
		}
		$this->typeID = $num;
		$db = Database::getInstance();
		$db->update(
			'planet',
			['planet_type_id' => $num],
			$this->SQLID,
		);
		$this->typeInfo = PlanetType::getTypeInfo($this->getTypeID());

		//trim buildings first
		foreach ($this->getBuildings() as $id => $amt) {
			if ($this->getMaxBuildings($id) < $amt) {
				$this->destroyBuilding($id, $amt - $this->getMaxBuildings($id));
			}
		}

		//trim excess defenses
		$this->checkForExcessDefense();

		$this->hasChanged = true;
		$this->update();
	}

	public function getTypeImage(): string {
		return $this->typeInfo->imageLink();
	}

	public function getTypeName(): string {
		return $this->typeInfo->name();
	}

	public function getTypeDescription(): string {
		return $this->typeInfo->description();
	}

	public function getMaxAttackers(): int {
		return $this->typeInfo->maxAttackers();
	}

	public function getMaxLanded(): int {
		return $this->typeInfo->maxLanded();
	}

	/**
	 * @return ($structureID is null ? array<int, PlanetStructureType> : PlanetStructureType)
	 */
	public function getStructureTypes(?int $structureID = null): PlanetStructureType|array {
		return $this->typeInfo->structureTypes($structureID);
	}

	public function hasStructureType(int $structureID): bool {
		return isset($this->getStructureTypes()[$structureID]);
	}

	/**
	 * Specifies which menu options the planet has.
	 */
	public function hasMenuOption(PlanetMenuOption $option): bool {
		// We do not set options that are unavailable
		return in_array($option, $this->typeInfo->menuOptions(), true);
	}

	public function update(): void {
		if (!$this->exists()) {
			return;
		}
		$db = Database::getInstance();
		if ($this->hasChanged) {
			$db->update(
				'planet',
				[
					'owner_id' => $this->ownerID,
					'password' => $this->password,
					'planet_name' => $this->planetName,
					'shields' => $this->shields,
					'armour' => $this->armour,
					'drones' => $this->drones,
				],
				$this->SQLID,
			);
			$this->hasChanged = false;
		}

		// Separate update for financial since these can be modified by looking
		// at the planet list (i.e. you might not have sector lock and could
		// cause a race condition with events happening in the planet sector).
		if ($this->hasChangedFinancial) {
			$db->update(
				'planet',
				[
					'credits' => $this->credits,
					'bonds' => $this->bonds,
					'maturity' => $this->maturity,
				],
				$this->SQLID,
			);
			$this->hasChangedFinancial = false;
		}

		if ($this->hasChangedStockpile) {
			// write stockpile info
			foreach ($this->getStockpile() as $id => $amount) {
				if ($amount !== 0) {
					$db->replace('planet_has_cargo', [
						...$this->SQLID,
						'good_id' => $id,
						'amount' => $amount,
					]);
				} else {
					$db->delete('planet_has_cargo', [
						...$this->SQLID,
						'good_id' => $id,
					]);
				}
			}
		}

		if (count($this->hasChangedWeapons) > 0) {
			foreach (array_keys($this->hasChangedWeapons) as $orderID) {
				if (isset($this->mountedWeapons[$orderID])) {
					$db->replace('planet_has_weapon', [
						...$this->SQLID,
						'order_id' => $orderID,
						'weapon_type_id' => $this->mountedWeapons[$orderID]->getWeaponTypeID(),
						'bonus_accuracy' => $db->escapeBoolean($this->mountedWeapons[$orderID]->hasBonusAccuracy()),
						'bonus_damage' => $db->escapeBoolean($this->mountedWeapons[$orderID]->hasBonusDamage()),
					]);
				} else {
					$db->delete('planet_has_weapon', [
						...$this->SQLID,
						'order_id' => $orderID,
					]);
				}
			}
			$this->hasChangedWeapons = [];
		}

		if (count($this->hasStoppedBuilding) > 0) {
			$db->write('DELETE FROM planet_is_building WHERE ' . self::SQL . '
						AND building_slot_id IN (:building_slot_ids) LIMIT :limit', [
				...$this->SQLID,
				'building_slot_ids' => $db->escapeArray($this->hasStoppedBuilding),
				'limit' => count($this->hasStoppedBuilding),
			]);
			$this->hasStoppedBuilding = [];
		}
		// write building info
		foreach ($this->hasChangedBuildings as $id => $hasChanged) {
			if ($hasChanged === true) {
				if ($this->hasBuilding($id)) {
					$db->replace('planet_has_building', [
						...$this->SQLID,
						'construction_id' => $id,
						'amount' => $this->getBuilding($id),
					]);
				} else {
					$db->delete('planet_has_building', [
						...$this->SQLID,
						'construction_id' => $id,
					]);
				}
				$this->hasChangedBuildings[$id] = false;
			}
		}
	}

	public function getLevel(): float {
		return array_sum($this->getBuildings()) / 3;
	}

	public function getMaxLevel(): float {
		return array_sum($this->getMaxBuildings()) / 3;
	}

	/**
	 * Returns the modified accuracy of turrets on this planet.
	 * Only used for display purposes.
	 */
	public function getTurretAccuracy(): float {
		return Weapon::getWeapon(WEAPON_PLANET_TURRET)->getModifiedPlanetAccuracy($this);
	}

	/**
	 * Returns the accuracy bonus for mounted weaons (as a percent)
	 */
	public function getAccuracyBonus(): int {
		return 5 * $this->getBuilding(PLANET_RADAR);
	}

	public function getRemainingStockpile(int $id): int {
		return self::MAX_STOCKPILE - $this->getStockpile($id);
	}

	/**
	 * Returns true if there is a building in progress
	 */
	public function hasCurrentlyBuilding(): bool {
		return count($this->getCurrentlyBuilding()) > 0;
	}

	/**
	 * Returns the reason a build cannot be performed, or false if there is
	 * no restriction.
	 */
	public function getBuildRestriction(AbstractPlayer $constructor, int $constructionID): string|false {
		if ($this->hasCurrentlyBuilding()) {
			return 'There is already a building in progress!';
		}
		if ($this->getBuilding($constructionID) >= $this->getMaxBuildings($constructionID)) {
			return 'This planet has reached the maximum buildings of that type.';
		}
		$cost = $this->getStructureTypes($constructionID)->creditCost();
		if ($constructor->getCredits() < $cost) {
			return 'You do not have enough credits.';
		}
		if ($constructor->getTurns() < TURNS_TO_BUILD) {
			return 'You do not have enough turns to build.';
		}
		foreach ($this->getStructureTypes($constructionID)->hardwareCost() as $hardwareID) {
			if ($constructor->getShip()->getHardware($hardwareID) === 0) {
				return 'You do not have the hardware needed for this type of building!';
			}
		}
		// take the goods that are needed
		foreach ($this->getStructureTypes($constructionID)->goods() as $goodID => $amount) {
			if ($this->getStockpile($goodID) < $amount) {
				return 'There are not enough goods available.';
			}
		}
		return false;
	}

	// Modifier for planet building based on the number of buildings.
	// The average value of this modifier should roughly be 1.
	private function getCompletionModifier(int $constructionID): float {
		$currentBuildings = $this->getBuilding($constructionID);
		$maxBuildings = $this->getMaxBuildings($constructionID);
		return 0.01 + 2.97 * pow($currentBuildings / $maxBuildings, 2);
	}

	// Amount of exp gained to build the next building of this type
	private function getConstructionExp(int $constructionID): int {
		return $this->getStructureTypes($constructionID)->expGain();
	}

	// Amount of time (in seconds) to build the next building of this type
	public function getConstructionTime(int $constructionID): int {
		$baseTime = $this->getStructureTypes($constructionID)->baseTime();
		return ICeil($baseTime * $this->getCompletionModifier($constructionID) / $this->getGame()->getGameSpeed());
	}

	/**
	 * @throws \Smr\Exceptions\UserError If the player cannot build the structure.
	 */
	public function startBuilding(AbstractPlayer $constructor, int $constructionID): void {
		$restriction = $this->getBuildRestriction($constructor, $constructionID);
		if ($restriction !== false) {
			throw new UserError('Unable to start building: ' . $restriction);
		}

		// gets the time for the buildings
		$timeComplete = Epoch::time() + $this->getConstructionTime($constructionID);
		$db = Database::getInstance();
		$insertID = $db->insertAutoIncrement('planet_is_building', [
			'game_id' => $this->getGameID(),
			'sector_id' => $this->getSectorID(),
			'construction_id' => $constructionID,
			'constructor_id' => $constructor->getAccountID(),
			'time_complete' => $timeComplete,
		]);

		$this->currentlyBuilding[$insertID] = [
			'BuildingSlotID' => $insertID,
			'ConstructionID' => $constructionID,
			'ConstructorID' => $constructor->getAccountID(),
			'Finishes' => $timeComplete,
			'TimeRemaining' => $timeComplete - Epoch::time(),
		];

		// Consume the required resources
		$constructor->decreaseCredits($this->getStructureTypes($constructionID)->creditCost());
		$constructor->takeTurns(TURNS_TO_BUILD, TURNS_TO_BUILD);
		foreach ($this->getStructureTypes($constructionID)->goods() as $goodID => $amount) {
			$this->decreaseStockpile($goodID, $amount);
		}
		foreach ($this->getStructureTypes($constructionID)->hardwareCost() as $hardwareID) {
			$constructor->getShip()->setHardware($hardwareID, 0);
		}
	}

	public function stopBuilding(int $constructionID): bool {
		$matchingBuilding = null;
		$latestFinish = 0;
		foreach ($this->getCurrentlyBuilding() as $building) {
			if ($building['ConstructionID'] === $constructionID && $building['Finishes'] > $latestFinish) {
				$latestFinish = $building['Finishes'];
				$matchingBuilding = $building;
			}
		}
		if ($matchingBuilding !== null) {
			$this->hasStoppedBuilding[] = $matchingBuilding['BuildingSlotID'];
			unset($this->currentlyBuilding[$matchingBuilding['BuildingSlotID']]);
			return true;
		}
		return false;
	}

	public function setName(string $name): void {
		if ($this->planetName === $name) {
			return;
		}
		$this->planetName = $name;
		$this->hasChanged = true;
	}

	/**
	 * Returns the name of the planet, suitably escaped for HTML display.
	 */
	public function getDisplayName(): string {
		return htmlentities($this->planetName);
	}

	/**
	 * Returns the name of the planet, intended for combat messages.
	 */
	public function getCombatName(): string {
		return '<span style="color:yellow;font-variant:small-caps">' . $this->getDisplayName() . ' (#' . $this->getSectorID() . ')</span>';
	}

	public function isInhabitable(): bool {
		return $this->inhabitableTime <= Epoch::time();
	}

	public function getInhabitableTime(): int {
		return $this->inhabitableTime;
	}

	public function getExamineHREF(): string {
		return (new ExaminePlanet())->href();
	}

	public function getLandHREF(): string {
		return (new LandProcessor())->href();
	}

	public function getAttackHREF(): string {
		return (new AttackPlanetProcessor())->href();
	}

	public function getBuildHREF(int $structureID): string {
		$container = new ConstructionProcessor('Build', $structureID);
		return $container->href();
	}

	public function getCancelHREF(int $structureID): string {
		$container = new ConstructionProcessor('Cancel', $structureID);
		return $container->href();
	}

	public function getFinancesHREF(): string {
		return (new FinancialProcessor())->href();
	}

	public function getBondConfirmationHREF(): string {
		return (new BondConfirm())->href();
	}

	/**
	 * @param array<AbstractPlayer> $attackers
	 */
	public function attackedBy(AbstractPlayer $trigger, array $attackers): void {
		$trigger->increaseHOF(1, ['Combat', 'Planet', 'Number Of Triggers'], HOF_PUBLIC);
		$db = Database::getInstance();
		foreach ($attackers as $attacker) {
			$attacker->increaseHOF(1, ['Combat', 'Planet', 'Number Of Attacks'], HOF_PUBLIC);
			$db->replace('player_attacks_planet', [
				'game_id' => $this->getGameID(),
				'account_id' => $attacker->getAccountID(),
				'sector_id' => $this->getSectorID(),
				'time' => Epoch::time(),
				'level' => $this->getLevel(),
			]);
		}

		// Add each unique attack to news unless it was already added recently.
		// Note: Attack uniqueness determined by planet owner.
		$owner = $this->getOwner();
		$dbResult = $db->read('SELECT 1 FROM news WHERE type = \'BREAKING\' AND game_id = :game_id AND dead_id = :dead_id AND time > :news_time LIMIT 1', [
			'game_id' => $db->escapeNumber($trigger->getGameID()),
			'dead_id' => $db->escapeNumber($owner->getAccountID()),
			'news_time' => $db->escapeNumber(Epoch::time() - self::TIME_ATTACK_NEWS_COOLDOWN),
		]);
		if (!$dbResult->hasRecord()) {
			if (count($attackers) >= 5) {
				$text = count($attackers) . ' members of ' . $trigger->getAllianceBBLink() . ' have been spotted attacking '
					. $this->getDisplayName() . ' in sector ' . Globals::getSectorBBLink($this->getSectorID()) . '. The planet is owned by ' . $owner->getBBLink();
				if ($owner->hasAlliance()) {
					$text .= ', a member of ' . $owner->getAllianceBBLink();
				}
				$text .= '.';
				$db->insert('news', [
					'game_id' => $this->getGameID(),
					'time' => Epoch::time(),
					'news_message' => $text,
					'type' => 'breaking',
					'killer_id' => $trigger->getAccountID(),
					'killer_alliance' => $trigger->getAllianceID(),
					'dead_id' => $owner->getAccountID(),
					'dead_alliance' => $owner->getAllianceID(),
				]);
			}
		}
	}

	/**
	 * @return array<int, Player>
	 */
	public function getPlayers(): array {
		return Player::getPlanetPlayers($this->getGameID(), $this->getSectorID());
	}

	public function countPlayers(): int {
		return count($this->getPlayers());
	}

	public function hasPlayers(): bool {
		return $this->countPlayers() > 0;
	}

	/**
	 * @return array<int, Player>
	 */
	public function getOtherTraders(AbstractPlayer $player): array {
		$players = Player::getPlanetPlayers($this->getGameID(), $this->getSectorID()); //Do not use & because we unset something and only want that in what we return
		unset($players[$player->getAccountID()]);
		return $players;
	}

	public function hasOtherTraders(AbstractPlayer $player): bool {
		return count($this->getOtherTraders($player)) > 0;
	}

	public function hasEnemyTraders(AbstractPlayer $player): bool {
		if (!$this->hasOtherTraders($player)) {
			return false;
		}
		$otherPlayers = $this->getOtherTraders($player);
		foreach ($otherPlayers as $otherPlayer) {
			if (!$player->traderNAPAlliance($otherPlayer)) {
				return true;
			}
		}
		return false;
	}

	public function hasFriendlyTraders(AbstractPlayer $player): bool {
		if (!$this->hasOtherTraders($player)) {
			return false;
		}
		$otherPlayers = $this->getOtherTraders($player);
		foreach ($otherPlayers as $otherPlayer) {
			if ($player->traderNAPAlliance($otherPlayer)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return array<Weapon>
	 */
	public function getWeapons(): array {
		$weapons = $this->getMountedWeapons();
		return array_pad(
			$weapons,
			count($weapons) + $this->getBuilding(PLANET_TURRET),
			Weapon::getWeapon(WEAPON_PLANET_TURRET),
		);
	}

	/**
	 * @param array<AbstractPlayer> $targetPlayers
	 * @return PlanetCombatResults
	 */
	public function shootPlayers(array $targetPlayers): array {
		$results = ['Planet' => $this, 'TotalDamage' => 0, 'TotalDamagePerTargetPlayer' => []];
		foreach ($targetPlayers as $targetPlayer) {
			$results['TotalDamagePerTargetPlayer'][$targetPlayer->getAccountID()] = 0;
		}
		if ($this->isBusted()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		$weapons = $this->getWeapons();
		foreach ($weapons as $orderID => $weapon) {
			$results['Weapons'][$orderID] = $weapon->shootPlayerAsPlanet($this, array_rand_value($targetPlayers));
			if ($results['Weapons'][$orderID]['Hit']) {
				if (!isset($results['Weapons'][$orderID]['ActualDamage'])) {
					throw new Exception('Weapon hit without providing ActualDamage!');
				}
				$totalDamage = $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
				$results['TotalDamage'] += $totalDamage;
				$results['TotalDamagePerTargetPlayer'][$results['Weapons'][$orderID]['Target']->getAccountID()] += $totalDamage;
			}
		}
		if ($this->hasCDs()) {
			$thisCDs = new CombatDrones($this->getCDs(), true);
			$results['Drones'] = $thisCDs->shootPlayerAsPlanet($this, array_rand_value($targetPlayers));
			$totalDamage = $results['Drones']['ActualDamage']['TotalDamage'];
			$results['TotalDamage'] += $totalDamage;
			$results['TotalDamagePerTargetPlayer'][$results['Drones']['Target']->getAccountID()] += $totalDamage;
		}
		return $results;
	}

	/**
	 * Returns an array of structure losses due to damage taken.
	 *
	 * @return array<int, int>
	 */
	public function checkForDowngrade(int $damage): array {
		$results = [];
		// For every 70 damage there is a 15% chance of destroying a structure.
		// Which structure is destroyed depends on the ratio of buildings and
		// the time it takes to build them.
		$numChances = floor($damage / self::DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE);
		for ($i = 0; $i < $numChances; $i++) {
			// Stop if the planet has no more buildlings
			if ($this->getLevel() === 0.0) {
				break;
			}
			//15% chance to destroy something
			if (rand(1, 100) <= self::CHANCE_TO_DOWNGRADE) {
				$chanceFactors = [];
				foreach ($this->getStructureTypes() as $structureID => $structure) {
					$chanceFactors[$structureID] = ($this->getBuilding($structureID) / $this->getMaxBuildings($structureID)) / $structure->baseTime();
				}
				$destroyID = getWeightedRandom($chanceFactors);
				$this->destroyBuilding($destroyID, 1);
				$this->checkForExcessDefense();
				if (isset($results[$destroyID])) {
					$results[$destroyID] += 1;
				} else {
					$results[$destroyID] = 1;
				}
			}
		}
		return $results;
	}

	/**
	 * @param WeaponDamageData $damage
	 * @return TakenDamageData
	 */
	public function takeDamage(array $damage): array {
		$alreadyDead = $this->isBusted();
		$shieldDamage = 0;
		$cdDamage = 0;
		$armourDamage = 0;
		if (!$alreadyDead) {
			$shieldDamage = $this->takeDamageToShields($damage['Shield']);
			if ($shieldDamage === 0 || $damage['Rollover']) {
				$cdMaxDamage = $damage['Armour'] - $shieldDamage;
				if ($shieldDamage === 0 && $this->hasShields()) {
					$cdMaxDamage = IFloor($cdMaxDamage * DRONES_BEHIND_SHIELDS_DAMAGE_PERCENT);
				}
				$cdDamage = $this->takeDamageToCDs($cdMaxDamage);
				if (!$this->hasShields() && ($cdDamage === 0 || $damage['Rollover'])) {
					$armourMaxDamage = $damage['Armour'] - $shieldDamage - $cdDamage;
					$armourDamage = $this->takeDamageToArmour($armourMaxDamage);
				}
			}
		}

		return [
			'KillingShot' => !$alreadyDead && $this->isBusted(),
			'TargetAlreadyDead' => $alreadyDead,
			'Shield' => $shieldDamage,
			'CDs' => $cdDamage,
			'NumCDs' => $cdDamage / CD_ARMOUR,
			'HasCDs' => $this->hasCDs(),
			'Armour' => $armourDamage,
			'TotalDamage' => $shieldDamage + $cdDamage + $armourDamage,
		];
	}

	protected function takeDamageToShields(int $damage): int {
		$actualDamage = min($this->getShields(), $damage);
		$this->decreaseShields($actualDamage);
		return $actualDamage;
	}

	protected function takeDamageToCDs(int $damage): int {
		$actualDamage = min($this->getCDs(), IFloor($damage / CD_ARMOUR));
		$this->decreaseCDs($actualDamage);
		return $actualDamage * CD_ARMOUR;
	}

	protected function takeDamageToArmour(int $damage): int {
		$actualDamage = min($this->getArmour(), $damage);
		$this->decreaseArmour($actualDamage);
		return $actualDamage;
	}

	public function creditCurrentAttackersForKill(): void {
		//get all players involved for HoF
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT account_id,level FROM player_attacks_planet WHERE ' . self::SQL . ' AND time > :credit_time', [
			...$this->SQLID,
			'credit_time' => $db->escapeNumber(Epoch::time() - self::TIME_TO_CREDIT_BUST),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$currPlayer = Player::getPlayer($dbRecord->getInt('account_id'), $this->getGameID());
			$currPlayer->increaseHOF($dbRecord->getFloat('level'), ['Combat', 'Planet', 'Levels'], HOF_PUBLIC);
			$currPlayer->increaseHOF(1, ['Combat', 'Planet', 'Completed'], HOF_PUBLIC);
		}
		$db->delete('player_attacks_planet', $this->SQLID);
	}

	/**
	 * @return array{}
	 */
	public function killPlanetByPlayer(AbstractPlayer $killer): array {
		$this->creditCurrentAttackersForKill();

		//kick everyone from planet
		$db = Database::getInstance();
		$db->update('player', ['land_on_planet' => 'FALSE'], $this->SQLID);
		$this->removeOwner();
		$this->removePassword();
		return [];
	}

}
