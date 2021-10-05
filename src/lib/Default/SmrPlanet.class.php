<?php declare(strict_types=1);

// This file defines more than just one class, which is not handled by
// the autoloader. So we must include it explicitly.
require_once('SmrPlanetType.class.php');

class SmrPlanet {
	protected static $CACHE_PLANETS = array();

	const DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE = 100;
	const CHANCE_TO_DOWNGRADE = 15; // percent
	const TIME_TO_CREDIT_BUST = 10800; // 3 hours
	const TIME_ATTACK_NEWS_COOLDOWN = 3600; // 1 hour
	const MAX_STOCKPILE = 600;

	protected Smr\Database $db;
	protected string $SQL;

	protected bool $exists;
	protected int $sectorID;
	protected int $gameID;
	protected string $planetName;
	protected int $ownerID;
	protected string $password;
	protected int $shields;
	protected int $armour;
	protected int $drones;
	protected int $credits;
	protected int $bonds;
	protected int $maturity;
	protected array $stockpile;
	protected array $buildings;
	protected int $inhabitableTime;
	protected array $currentlyBuilding;
	protected array $mountedWeapons;
	protected int $typeID;
	protected SmrPlanetType $typeInfo;

	protected bool $hasChanged = false;
	protected bool $hasChangedFinancial = false; // for credits, bond, maturity
	protected bool $hasChangedStockpile = false;
	protected array $hasChangedWeapons = array();
	protected array $hasChangedBuildings = array();
	protected array $hasStoppedBuilding = array();
	protected bool $isNew = false;

	protected int $delayedShieldsDelta = 0;
	protected int $delayedCDsDelta = 0;
	protected int $delayedArmourDelta = 0;

	public function __sleep() {
		return ['sectorID', 'gameID', 'planetName', 'ownerID', 'typeID'];
	}

	public static function clearCache() : void {
		self::$CACHE_PLANETS = array();
	}

	public static function savePlanets() : void {
		foreach (self::$CACHE_PLANETS as $gamePlanets) {
			foreach ($gamePlanets as $planet) {
				$planet->update();
			}
		}
	}

	public static function getGalaxyPlanets(int $gameID, int $galaxyID, bool $forceUpdate = false) : array {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT planet.* FROM planet LEFT JOIN sector USING (game_id, sector_id) WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND galaxy_id = ' . $db->escapeNumber($galaxyID));
		$galaxyPlanets = [];
		foreach ($dbResult->records() as $dbRecord) {
			$sectorID = $dbRecord->getInt('sector_id');
			$galaxyPlanets[$sectorID] = self::getPlanet($gameID, $sectorID, $forceUpdate, $dbRecord);
		}
		return $galaxyPlanets;
	}

	public static function getPlanet(int $gameID, int $sectorID, bool $forceUpdate = false, Smr\DatabaseRecord $dbRecord = null) : self {
		if ($forceUpdate || !isset(self::$CACHE_PLANETS[$gameID][$sectorID])) {
			self::$CACHE_PLANETS[$gameID][$sectorID] = new SmrPlanet($gameID, $sectorID, $dbRecord);
		}
		return self::$CACHE_PLANETS[$gameID][$sectorID];
	}

	public static function createPlanet(int $gameID, int $sectorID, int $typeID = 1, int $inhabitableTime = null) : self {
		if (self::getPlanet($gameID, $sectorID)->exists()) {
			throw new Exception('Planet already exists in sector ' . $sectorID . ' game ' . $gameID);
		}

		if ($inhabitableTime === null) {
			$minTime = SmrGame::getGame($gameID)->getStartTime();
			$inhabitableTime = $minTime + pow(rand(45, 85), 3);
		}

		// insert planet into db
		$db = Smr\Database::getInstance();
		$db->write('INSERT INTO planet (game_id, sector_id, inhabitable_time, planet_type_id)
				VALUES (' . $db->escapeNumber($gameID) . ', ' . $db->escapeNumber($sectorID) . ', ' . $db->escapeNumber($inhabitableTime) . ', ' . $db->escapeNumber($typeID) . ')');
		return self::getPlanet($gameID, $sectorID, true);
	}

	public static function removePlanet(int $gameID, int $sectorID) : void {
		$db = Smr\Database::getInstance();
		$SQL = 'game_id = ' . $db->escapeNumber($gameID) . ' AND sector_id = ' . $db->escapeNumber($sectorID);
		$db->write('DELETE FROM planet WHERE ' . $SQL);
		$db->write('DELETE FROM planet_has_weapon WHERE ' . $SQL);
		$db->write('DELETE FROM planet_has_cargo WHERE ' . $SQL);
		$db->write('DELETE FROM planet_has_building WHERE ' . $SQL);
		$db->write('DELETE FROM planet_is_building WHERE ' . $SQL);
		//kick everyone from planet
		$db->write('UPDATE player SET land_on_planet = \'FALSE\' WHERE ' . $SQL);

		self::$CACHE_PLANETS[$gameID][$sectorID] = null;
		unset(self::$CACHE_PLANETS[$gameID][$sectorID]);
	}

	protected function __construct(int $gameID, int $sectorID, Smr\DatabaseRecord $dbRecord = null) {
		$this->db = Smr\Database::getInstance();
		$this->SQL = 'game_id = ' . $this->db->escapeNumber($gameID) . ' AND sector_id = ' . $this->db->escapeNumber($sectorID);

		if ($dbRecord === null) {
			$dbResult = $this->db->read('SELECT * FROM planet WHERE ' . $this->SQL);
			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
			}
		}
		$this->exists = $dbRecord !== null;

		if ($this->exists) {
			$this->gameID = $gameID;
			$this->sectorID = $sectorID;
			$this->planetName = $dbRecord->getString('planet_name');
			$this->ownerID = $dbRecord->getInt('owner_id');
			$this->password = $dbRecord->getField('password');
			$this->shields = $dbRecord->getInt('shields');
			$this->armour = $dbRecord->getInt('armour');
			$this->drones = $dbRecord->getInt('drones');
			$this->credits = $dbRecord->getInt('credits');
			$this->bonds = $dbRecord->getInt('bonds');
			$this->maturity = $dbRecord->getInt('maturity');
			$this->inhabitableTime = $dbRecord->getInt('inhabitable_time');
			$this->typeID = $dbRecord->getInt('planet_type_id');

			$this->typeInfo = SmrPlanetType::getTypeInfo($this->getTypeID());
			$this->checkBondMaturity();
		}
	}

	public function getInterestRate() : float {
		$level = $this->getLevel();
		if ($level < 9) {
			return .0404;
		} elseif ($level < 19) {
			return .0609;
		} elseif ($level < 29) {
			return .1236;
		} elseif ($level < 39) {
			return .050625;
		} elseif ($level < 49) {
			return .0404;
		} elseif ($level < 59) {
			return .030225;
		} elseif ($level < 69) {
			return .0201;
		} else {
			return .018081;
		}
	}

	public function checkBondMaturity(bool $partial = false) : void {
		if ($this->getMaturity() > 0 && ($partial === true || $this->getMaturity() < Smr\Epoch::time())) {
			// calc the interest for the time
			$interest = $this->getBonds() * $this->getInterestRate();

			if ($partial === true && $this->getMaturity() > Smr\Epoch::time()) {
				// Adjust interest based upon how much of the bond duration has passed.
				$interest -= ($interest / $this->getBondTime()) * ($this->getMaturity() - Smr\Epoch::time());
			}

			// transfer money to free avail cash
			$this->increaseCredits($this->getBonds() + IFloor($interest));

			// reset bonds
			$this->setBonds(0);

			// reset maturity
			$this->setMaturity(0);
		}
	}

	public function getBondTime() : int {
		return IRound(BOND_TIME / $this->getGame()->getGameSpeed());
	}

	public function bond() : void {
		$this->checkBondMaturity(true);

		// add it to bond
		$this->increaseBonds($this->getCredits());

		// set free cash to 0
		$this->setCredits(0);

		// initialize time
		$this->setMaturity(Smr\Epoch::time() + $this->getBondTime());
	}

	public function getGameID() : int {
		return $this->gameID;
	}

	public function getGame() : SmrGame {
		return SmrGame::getGame($this->gameID);
	}

	public function getSectorID() : int {
		return $this->sectorID;
	}

	public function getGalaxy() : SmrGalaxy {
		return SmrGalaxy::getGalaxyContaining($this->getGameID(), $this->getSectorID());
	}

	public function getOwnerID() : int {
		return $this->ownerID;
	}

	public function hasOwner() : bool {
		return $this->ownerID != 0;
	}

	public function removeOwner() : void {
		$this->setOwnerID(0);
	}

	public function setOwnerID(int $claimerID) : void {
		if ($this->ownerID === $claimerID) {
			return;
		}
		$this->ownerID = $claimerID;
		$this->hasChanged = true;
	}

	public function getOwner() : SmrPlayer {
		return SmrPlayer::getPlayer($this->getOwnerID(), $this->getGameID());
	}

	public function getPassword() : string {
		return $this->password;
	}

	public function setPassword(string $password) : void {
		if ($this->password === $password) {
			return;
		}
		$this->password = $password;
		$this->hasChanged = true;
	}

	public function removePassword() : void {
		$this->setPassword('');
	}

	public function getCredits() : int {
		return $this->credits;
	}

	public function setCredits(int $num) : void {
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
	public function increaseCredits(int $num) : int {
		if ($num === 0) {
			return 0;
		}
		$newTotal = min($this->credits + $num, MAX_MONEY);
		$actualAdded = $newTotal - $this->credits;
		$this->setCredits($newTotal);
		return $actualAdded;
	}

	public function decreaseCredits(int $num) : void {
		if ($num === 0) {
			return;
		}
		$newTotal = $this->credits - $num;
		$this->setCredits($newTotal);
	}

	public function getMaturity() : int {
		return $this->maturity;
	}

	public function setMaturity(int $num) : void {
		if ($this->maturity === $num) {
			return;
		}
		if ($num < 0) {
			throw new Exception('You cannot set negative maturity.');
		}
		$this->maturity = $num;
		$this->hasChangedFinancial = true;
	}

	public function getBonds() : int {
		return $this->bonds;
	}

	public function setBonds(int $num) : void {
		if ($this->bonds === $num) {
			return;
		}
		if ($num < 0) {
			throw new Exception('You cannot set negative bonds.');
		}
		$this->bonds = $num;
		$this->hasChangedFinancial = true;
	}

	public function increaseBonds(int $num) : void {
		if ($num === 0) {
			return;
		}
		$this->setBonds($this->getBonds() + $num);
	}

	public function decreaseBonds(int $num) : void {
		if ($num === 0) {
			return;
		}
		$this->setBonds($this->getBonds() - $num);
	}

	public function checkForExcessDefense() : void {
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
		while ($this->getMountedWeapons() && max(array_keys($this->getMountedWeapons())) >= $this->getMaxMountedWeapons()) {
			$removeID = array_rand($this->getMountedWeapons());
			$this->removeMountedWeapon($removeID);
			foreach ($this->getMountedWeapons() as $orderID => $weapon) {
				if ($orderID > $removeID) {
					$this->moveMountedWeaponUp($orderID);
				}
			}
		}
	}

	public function getShields(bool $delayed = false) : int {
		return $this->shields + ($delayed ? $this->delayedShieldsDelta : 0);
	}

	public function hasShields(bool $delayed = false) : bool {
		return $this->getShields($delayed) > 0;
	}

	public function setShields(int $shields) : void {
		$shields = max(0, min($shields, $this->getMaxShields()));
		if ($this->shields === $shields) {
			return;
		}
		$this->shields = $shields;
		$this->hasChanged = true;
	}

	public function decreaseShields(int $number, bool $delayed = false) : void {
		if ($number === 0) {
			return;
		}
		if ($delayed === false) {
			$this->setShields($this->getShields() - $number);
		} else {
			$this->delayedShieldsDelta -= $number;
		}
	}

	public function increaseShields(int $number, bool $delayed = false) : void {
		if ($number === 0) {
			return;
		}
		if ($delayed === false) {
			$this->setShields($this->getShields() + $number);
		} else {
			$this->delayedShieldsDelta += $number;
		}
	}

	public function getMaxShields() : int {
		return $this->getBuilding(PLANET_GENERATOR) * PLANET_GENERATOR_SHIELDS;
	}

	public function getArmour(bool $delayed = false) : int {
		return $this->armour + ($delayed ? $this->delayedArmourDelta : 0);
	}

	public function hasArmour(bool $delayed = false) : bool {
		return $this->getArmour($delayed) > 0;
	}

	public function setArmour(int $armour) : void {
		$armour = max(0, min($armour, $this->getMaxArmour()));
		if ($this->armour === $armour) {
			return;
		}
		$this->armour = $armour;
		$this->hasChanged = true;
	}

	public function decreaseArmour(int $number, bool $delayed = false) : void {
		if ($number === 0) {
			return;
		}
		if ($delayed === false) {
			$this->setArmour($this->getArmour() - $number);
		} else {
			$this->delayedArmourDelta -= $number;
		}
	}

	public function increaseArmour(int $number, bool $delayed = false) : void {
		if ($number === 0) {
			return;
		}
		if ($delayed === false) {
			$this->setArmour($this->getArmour() + $number);
		} else {
			$this->delayedArmourDelta += $number;
		}
	}

	public function getMaxArmour() : int {
		return $this->getBuilding(PLANET_BUNKER) * PLANET_BUNKER_ARMOUR;
	}

	public function getCDs(bool $delayed = false) : int {
		return $this->drones + ($delayed ? $this->delayedCDsDelta : 0);
	}

	public function hasCDs(bool $delayed = false) : bool {
		return $this->getCDs($delayed) > 0;
	}

	public function setCDs(int $combatDrones) : void {
		$combatDrones = max(0, min($combatDrones, $this->getMaxCDs()));
		if ($this->drones === $combatDrones) {
			return;
		}
		$this->drones = $combatDrones;
		$this->hasChanged = true;
	}

	public function decreaseCDs(int $number, bool $delayed = false) : void {
		if ($number === 0) {
			return;
		}
		if ($delayed === false) {
			$this->setCDs($this->getCDs() - $number);
		} else {
			$this->delayedCDsDelta -= $number;
		}
	}

	public function increaseCDs(int $number, bool $delayed = false) : void {
		if ($number === 0) {
			return;
		}
		if ($delayed === false) {
			$this->setCDs($this->getCDs() + $number);
		} else {
			$this->delayedCDsDelta += $number;
		}
	}

	public function getMaxCDs() : int {
		return $this->getBuilding(PLANET_HANGAR) * PLANET_HANGAR_DRONES;
	}

	public function getMaxMountedWeapons() : int {
		return $this->getBuilding(PLANET_WEAPON_MOUNT);
	}

	public function getMountedWeapons() : array {
		if (!isset($this->mountedWeapons)) {
			$this->mountedWeapons = [];
			if ($this->hasBuilding(PLANET_WEAPON_MOUNT)) {
				$dbResult = $this->db->read('SELECT * FROM planet_has_weapon JOIN weapon_type USING (weapon_type_id) WHERE ' . $this->SQL);
				foreach ($dbResult->records() as $dbRecord) {
					$weaponTypeID = $dbRecord->getInt('weapon_type_id');
					$orderID = $dbRecord->getInt('order_id');
					$weapon = SmrWeapon::getWeapon($weaponTypeID, $dbRecord);
					$weapon->setBonusAccuracy($dbRecord->getBoolean('bonus_accuracy'));
					$weapon->setBonusDamage($dbRecord->getBoolean('bonus_damage'));
					$this->mountedWeapons[$orderID] = $weapon;
				}
			}
		}
		return $this->mountedWeapons;
	}

	public function hasMountedWeapon(int $orderID) : bool {
		$this->getMountedWeapons(); // Make sure array is initialized
		return isset($this->mountedWeapons[$orderID]);
	}

	public function addMountedWeapon(SmrWeapon $weapon, int $orderID) : void {
		$this->getMountedWeapons(); // Make sure array is initialized
		$this->mountedWeapons[$orderID] = $weapon;
		$this->hasChangedWeapons[$orderID] = true;
	}

	public function removeMountedWeapon(int $orderID) : void {
		$this->getMountedWeapons(); // Make sure array is initialized
		unset($this->mountedWeapons[$orderID]);
		$this->hasChangedWeapons[$orderID] = true;
	}

	private function swapMountedWeapons(int $orderID1, int $orderID2) : void {
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

	public function moveMountedWeaponUp(int $orderID) : void {
		if ($orderID == 0) {
			throw new Exception('Cannot move this weapon up!');
		}
		$this->swapMountedWeapons($orderID - 1, $orderID);
	}

	public function moveMountedWeaponDown(int $orderID) : void {
		if ($orderID == $this->getMaxMountedWeapons() - 1) {
			throw new Exception('Cannot move this weapon down!');
		}
		$this->swapMountedWeapons($orderID + 1, $orderID);
	}


	public function isDestroyed(bool $delayed = false) : bool {
		return !$this->hasCDs($delayed) && !$this->hasShields($delayed) && !$this->hasArmour($delayed);
	}

	public function exists() : bool {
		return $this->exists;
	}

	public function getStockpile(int $goodID = null) : int|array {
		if (!isset($this->stockpile)) {
			// initialize cargo array
			$this->stockpile = array();
			// get supplies from db
			$dbResult = $this->db->read('SELECT good_id, amount FROM planet_has_cargo WHERE ' . $this->SQL);
			// adding cargo and amount to array
			foreach ($dbResult->records() as $dbRecord) {
				$this->stockpile[$dbRecord->getInt('good_id')] = $dbRecord->getInt('amount');
			}
		}
		if ($goodID === null) {
			return $this->stockpile;
		}
		if (isset($this->stockpile[$goodID])) {
			return $this->stockpile[$goodID];
		}
		return 0;
	}

	public function hasStockpile(int $goodID = null) : bool {
		if ($goodID === null) {
			$stockpile = $this->getStockpile();
			return count($stockpile) > 0 && max($stockpile) > 0;
		} else {
			return $this->getStockpile($goodID) > 0;
		}
	}

	public function setStockpile(int $goodID, int $amount) : void {
		if ($this->getStockpile($goodID) === $amount) {
			return;
		}
		if ($amount < 0) {
			throw new Exception('Trying to set negative stockpile.');
		}
		$this->stockpile[$goodID] = $amount;
		$this->hasChangedStockpile = true;
	}

	public function decreaseStockpile(int $goodID, int $amount) : void {
		if ($amount < 0) {
			throw new Exception('Trying to decrease negative stockpile.');
		}
		$this->setStockpile($goodID, $this->getStockpile($goodID) - $amount);
	}

	public function increaseStockpile(int $goodID, int $amount) : void {
		if ($amount < 0) {
			throw new Exception('Trying to increase negative stockpile.');
		}
		$this->setStockpile($goodID, $this->getStockpile($goodID) + $amount);
	}

	public function getBuildings() : array {
		if (!isset($this->buildings)) {
			$this->buildings = array();

			// get buildingss from db
			$dbResult = $this->db->read('SELECT construction_id, amount FROM planet_has_building WHERE ' . $this->SQL);
			// adding building and amount to array
			foreach ($dbResult->records() as $dbRecord) {
				$this->buildings[$dbRecord->getInt('construction_id')] = $dbRecord->getInt('amount');
			}

			// Update building counts if construction has finished
			$this->getCurrentlyBuilding();
		}
		return $this->buildings;
	}

	public function getBuilding(int $buildingTypeID) : int {
		$buildings = $this->getBuildings();
		if (isset($buildings[$buildingTypeID])) {
			return $buildings[$buildingTypeID];
		}
		return 0;
	}

	public function hasBuilding(int $buildingTypeID) : bool {
		return $this->getBuilding($buildingTypeID) > 0;
	}

	public function setBuilding(int $buildingTypeID, int $number) : void {
		if ($this->getBuilding($buildingTypeID) === $number) {
			return;
		}
		if ($number < 0) {
			throw new Exception('Cannot set negative number of buildings.');
		}

		$this->buildings[$buildingTypeID] = $number;
		$this->hasChangedBuildings[$buildingTypeID] = true;
	}

	public function increaseBuilding(int $buildingTypeID, int $number) : void {
		$this->setBuilding($buildingTypeID, $this->getBuilding($buildingTypeID) + $number);
	}

	public function destroyBuilding(int $buildingTypeID, int $number) : void {
		$this->setBuilding($buildingTypeID, $this->getBuilding($buildingTypeID) - $number);
	}

	public function getCurrentlyBuilding() : array {
		if (!isset($this->currentlyBuilding)) {
			$this->currentlyBuilding = array();

			$dbResult = $this->db->read('SELECT * FROM planet_is_building WHERE ' . $this->SQL);
			foreach ($dbResult->records() as $dbRecord) {
				$this->currentlyBuilding[$dbRecord->getInt('building_slot_id')] = array(
					'BuildingSlotID' => $dbRecord->getInt('building_slot_id'),
					'ConstructionID' => $dbRecord->getInt('construction_id'),
					'ConstructorID' => $dbRecord->getInt('constructor_id'),
					'Finishes' => $dbRecord->getInt('time_complete'),
					'TimeRemaining' => $dbRecord->getInt('time_complete') - Smr\Epoch::time(),
				);
			}

			// Check if construction has completed
			foreach ($this->currentlyBuilding as $id => $building) {
				if ($building['TimeRemaining'] <= 0) {
					unset($this->currentlyBuilding[$id]);
					$expGain = $this->getConstructionExp($building['ConstructionID']);
					$player = SmrPlayer::getPlayer($building['ConstructorID'], $this->getGameID());
					$player->increaseHOF(1, array('Planet', 'Buildings', 'Built'), HOF_ALLIANCE);
					$player->increaseExperience($expGain);
					$player->increaseHOF($expGain, array('Planet', 'Buildings', 'Experience'), HOF_ALLIANCE);
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

	public function getMaxBuildings(int $buildingTypeID = null) : int|array {
		if ($buildingTypeID === null) {
			$structs = $this->typeInfo::STRUCTURES;
			return array_combine(array_keys($structs),
			                     array_column($structs, 'max_amount'));
		}
		return $this->getStructureTypes($buildingTypeID)->maxAmount();
	}

	public function getTypeID() : int {
		return $this->typeID;
	}

	public function setTypeID(int $num) : void {
		if (isset($this->typeID) && $this->typeID === $num) {
			return;
		}
		$this->typeID = $num;
		$this->db->write('UPDATE planet SET planet_type_id = ' . $this->db->escapeNumber($num) . ' WHERE ' . $this->SQL);
		$this->typeInfo = SmrPlanetType::getTypeInfo($this->getTypeID());

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

	public function getTypeImage() : string {
		return $this->typeInfo->imageLink();
	}

	public function getTypeName() : string {
		return $this->typeInfo->name();
	}

	public function getTypeDescription() : string {
		return $this->typeInfo->description();
	}

	public function getMaxAttackers() : int {
		return $this->typeInfo->maxAttackers();
	}

	public function getMaxLanded() : int {
		return $this->typeInfo->maxLanded();
	}

	public function getStructureTypes(int $structureID = null) : SmrPlanetStructureType|array {
		return $this->typeInfo->structureTypes($structureID);
	}

	public function hasStructureType(int $structureID) : bool {
		return isset($this->getStructureTypes()[$structureID]);
	}

	/**
	 * Specifies which menu options the planet has.
	 */
	public function hasMenuOption(string $option) : bool {
		// We do not set options that are unavailable
		return in_array($option, $this->typeInfo->menuOptions());
	}

	public function doDelayedUpdates() : void {
		$this->setShields($this->getShields(true));
		$this->delayedShieldsDelta = 0;
		$this->setCDs($this->getCDs(true));
		$this->delayedCDsDelta = 0;
		$this->setArmour($this->getArmour(true));
		$this->delayedArmourDelta = 0;
	}

	public function update() : void {
		if (!$this->exists()) {
			return;
		}
		$this->doDelayedUpdates();
		if ($this->hasChanged) {
			$this->db->write('UPDATE planet SET
									owner_id = ' . $this->db->escapeNumber($this->ownerID) . ',
									password = '.$this->db->escapeString($this->password) . ',
									planet_name = ' . $this->db->escapeString($this->planetName) . ',
									shields = ' . $this->db->escapeNumber($this->shields) . ',
									armour = ' . $this->db->escapeNumber($this->armour) . ',
									drones = ' . $this->db->escapeNumber($this->drones) . '
								WHERE ' . $this->SQL);
			$this->hasChanged = false;
		}

		// Separate update for financial since these can be modified by looking
		// at the planet list (i.e. you might not have sector lock and could
		// cause a race condition with events happening in the planet sector).
		if ($this->hasChangedFinancial) {
			$this->db->write('UPDATE planet SET
									credits = ' . $this->db->escapeNumber($this->credits) . ',
									bonds = ' . $this->db->escapeNumber($this->bonds) . ',
									maturity = ' . $this->db->escapeNumber($this->maturity) . '
								WHERE ' . $this->SQL);
			$this->hasChangedFinancial = false;
		}

		if ($this->hasChangedStockpile) {
			// write stockpile info
			foreach ($this->getStockpile() as $id => $amount) {
				if ($amount != 0) {
					$this->db->write('REPLACE INTO planet_has_cargo (game_id, sector_id, good_id, amount) ' .
										 'VALUES(' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($this->getSectorID()) . ', ' . $this->db->escapeNumber($id) . ', ' . $this->db->escapeNumber($amount) . ')');
				} else {
					$this->db->write('DELETE FROM planet_has_cargo WHERE ' . $this->SQL . '
										AND good_id = ' . $this->db->escapeNumber($id));
				}
			}
		}

		if (count($this->hasChangedWeapons) > 0) {
			foreach (array_keys($this->hasChangedWeapons) as $orderID) {
				if (isset($this->mountedWeapons[$orderID])) {
					$this->db->write('REPLACE INTO planet_has_weapon (game_id, sector_id, order_id, weapon_type_id, bonus_accuracy, bonus_damage) VALUES (' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeNumber($this->getSectorID()) . ',' . $this->db->escapeNumber($orderID) . ',' . $this->db->escapeNumber($this->mountedWeapons[$orderID]->getWeaponTypeID()) . ',' . $this->db->escapeBoolean($this->mountedWeapons[$orderID]->hasBonusAccuracy()) . ',' . $this->db->escapeBoolean($this->mountedWeapons[$orderID]->hasBonusDamage()) . ')');
				} else {
					$this->db->write('DELETE FROM planet_has_weapon WHERE ' . $this->SQL . ' AND order_id=' . $this->db->escapeNumber($orderID));
				}
			}
			$this->hasChangedWeapons = [];
		}

		if (count($this->hasStoppedBuilding) > 0) {
			$this->db->write('DELETE FROM planet_is_building WHERE ' . $this->SQL . '
								AND building_slot_id IN (' . $this->db->escapeArray($this->hasStoppedBuilding) . ') LIMIT ' . count($this->hasStoppedBuilding));
			$this->hasStoppedBuilding = array();
		}
		// write building info
		foreach ($this->hasChangedBuildings as $id => $hasChanged) {
			if ($hasChanged === true) {
				if ($this->hasBuilding($id)) {
					$this->db->write('REPLACE INTO planet_has_building (game_id, sector_id, construction_id, amount) ' .
										'VALUES(' . $this->db->escapeNumber($this->gameID) . ', ' . $this->db->escapeNumber($this->sectorID) . ', ' . $this->db->escapeNumber($id) . ', ' . $this->db->escapeNumber($this->getBuilding($id)) . ')');
				} else {
					$this->db->write('DELETE FROM planet_has_building WHERE ' . $this->SQL . '
										AND construction_id = ' . $this->db->escapeNumber($id));
				}
				$this->hasChangedBuildings[$id] = false;
			}
		}
	}

	public function getLevel() : float {
		return array_sum($this->getBuildings()) / 3;
	}

	public function getMaxLevel() : float {
		return array_sum($this->getMaxBuildings()) / 3;
	}

	public function accuracy() : float {
		if ($this->hasWeapons()) {
			$weapons = $this->getWeapons();
			return $weapons[0]->getModifiedPlanetAccuracy($this);
		}
		return 0;
	}

	/**
	 * Returns the accuracy bonus for mounted weaons (as a percent)
	 */
	public function getAccuracyBonus() : int {
		return 5 * $this->getBuilding(PLANET_RADAR);
	}

	public function getRemainingStockpile(int $id) : int {
		return self::MAX_STOCKPILE - $this->getStockpile($id);
	}

	/**
	 * Returns true if there is a building in progress
	 */
	public function hasCurrentlyBuilding() : bool {
		return count($this->getCurrentlyBuilding()) > 0;
	}

	/**
	 * Returns the reason a build cannot be performed, or false if there is
	 * no restriction.
	 */
	public function getBuildRestriction(AbstractSmrPlayer $constructor, int $constructionID) : string|false {
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
			if (!$constructor->getShip()->getHardware($hardwareID)) {
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
	private function getCompletionModifier(int $constructionID) : float {
		$currentBuildings = $this->getBuilding($constructionID);
		$maxBuildings = $this->getMaxBuildings($constructionID);
		return 0.01 + 2.97 * pow($currentBuildings / $maxBuildings, 2);
	}

	// Amount of exp gained to build the next building of this type
	private function getConstructionExp(int $constructionID) : int {
		$expGain = $this->getStructureTypes($constructionID)->expGain();
		return $expGain;
	}

	// Amount of time (in seconds) to build the next building of this type
	public function getConstructionTime(int $constructionID) : int {
		$baseTime = $this->getStructureTypes($constructionID)->baseTime();
		$constructionTime = ICeil($baseTime * $this->getCompletionModifier($constructionID) / $this->getGame()->getGameSpeed());
		return $constructionTime;
	}

	/**
	 * @throws \Smr\UserException If the player cannot build the structure.
	 */
	public function startBuilding(AbstractSmrPlayer $constructor, int $constructionID) : void {
		$restriction = $this->getBuildRestriction($constructor, $constructionID);
		if ($restriction !== false) {
			throw new \Smr\UserException('Unable to start building: ' . $restriction);
		}

		// gets the time for the buildings
		$timeComplete = Smr\Epoch::time() + $this->getConstructionTime($constructionID);
		$this->db->write('INSERT INTO planet_is_building (game_id, sector_id, construction_id, constructor_id, time_complete) ' .
						'VALUES (' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($this->getSectorID()) . ', ' . $this->db->escapeNumber($constructionID) . ', ' . $this->db->escapeNumber($constructor->getAccountID()) . ',' . $this->db->escapeNumber($timeComplete) . ')');

		$this->currentlyBuilding[$this->db->getInsertID()] = array(
			'BuildingSlotID' => $this->db->getInsertID(),
			'ConstructionID' => $constructionID,
			'ConstructorID' => $constructor->getAccountID(),
			'Finishes' => $timeComplete,
			'TimeRemaining' => $timeComplete - Smr\Epoch::time()
		);

		// Consume the required resources
		$constructor->decreaseCredits($this->getStructureTypes($constructionID)->creditCost());
		$constructor->takeTurns(TURNS_TO_BUILD);
		foreach ($this->getStructureTypes($constructionID)->goods() as $goodID => $amount) {
			$this->decreaseStockpile($goodID, $amount);
		}
		foreach ($this->getStructureTypes($constructionID)->hardwareCost() as $hardwareID) {
			$constructor->getShip()->setHardware($hardwareID, 0);
		}
	}

	public function stopBuilding(int $constructionID) : bool {
		$matchingBuilding = false;
		$latestFinish = 0;
		foreach ($this->getCurrentlyBuilding() as $key => $building) {
			if ($building['ConstructionID'] == $constructionID && $building['Finishes'] > $latestFinish) {
				$latestFinish = $building['Finishes'];
				$matchingBuilding = $building;
			}
		}
		if ($matchingBuilding) {
			$this->hasStoppedBuilding[] = $matchingBuilding['BuildingSlotID'];
			unset($this->currentlyBuilding[$matchingBuilding['BuildingSlotID']]);
			return true;
		}
		return false;
	}

	public function setName(string $name) : void {
		if ($this->planetName === $name) {
			return;
		}
		$this->planetName = $name;
		$this->hasChanged = true;
	}

	/**
	 * Returns the name of the planet, suitably escaped for HTML display.
	 */
	public function getDisplayName() : string {
		return htmlentities($this->planetName);
	}

	/**
	 * Returns the name of the planet, intended for combat messages.
	 */
	public function getCombatName() : string {
		return '<span style="color:yellow;font-variant:small-caps">' . $this->getDisplayName() . ' (#' . $this->getSectorID() . ')</span>';
	}

	public function isInhabitable() : bool {
		return $this->inhabitableTime <= Smr\Epoch::time();
	}

	public function getInhabitableTime() : int {
		return $this->inhabitableTime;
	}

	public function getExamineHREF() : string {
		return Page::create('skeleton.php', 'planet_examine.php')->href();
	}

	public function getLandHREF() : string {
		return Page::create('planet_land_processing.php')->href();
	}

	public function getAttackHREF() : string {
		return Page::create('planet_attack_processing.php')->href();
	}

	public function getBuildHREF(int $structureID) : string {
		$container = Page::create('planet_construction_processing.php');
		$container['construction_id'] = $structureID;
		$container['action'] = 'Build';
		return $container->href();
	}

	public function getCancelHREF(int $structureID) : string {
		$container = Page::create('planet_construction_processing.php');
		$container['construction_id'] = $structureID;
		$container['action'] = 'Cancel';
		return $container->href();
	}

	public function getFinancesHREF() : string {
		return Page::create('planet_financial_processing.php')->href();
	}

	public function getBondConfirmationHREF() : string {
		return Page::create('skeleton.php', 'planet_bond_confirmation.php')->href();
	}

	public function attackedBy(AbstractSmrPlayer $trigger, array $attackers) : void {
		$trigger->increaseHOF(1, array('Combat', 'Planet', 'Number Of Triggers'), HOF_PUBLIC);
		foreach ($attackers as $attacker) {
			$attacker->increaseHOF(1, array('Combat', 'Planet', 'Number Of Attacks'), HOF_PUBLIC);
			$this->db->write('REPLACE INTO player_attacks_planet (game_id, account_id, sector_id, time, level) VALUES ' .
					'(' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($attacker->getAccountID()) . ', ' . $this->db->escapeNumber($this->getSectorID()) . ', ' . $this->db->escapeNumber(Smr\Epoch::time()) . ', ' . $this->db->escapeNumber($this->getLevel()) . ')');
		}

		// Add each unique attack to news unless it was already added recently.
		// Note: Attack uniqueness determined by planet owner.
		$owner = $this->getOwner();
		$dbResult = $this->db->read('SELECT 1 FROM news WHERE type = \'BREAKING\' AND game_id = ' . $this->db->escapeNumber($trigger->getGameID()) . ' AND dead_id=' . $this->db->escapeNumber($owner->getAccountID()) . ' AND time > ' . $this->db->escapeNumber(Smr\Epoch::time() - self::TIME_ATTACK_NEWS_COOLDOWN) . ' LIMIT 1');
		if (!$dbResult->hasRecord()) {
			if (count($attackers) >= 5) {
				$text = count($attackers) . ' members of ' . $trigger->getAllianceBBLink() . ' have been spotted attacking ' .
					$this->getDisplayName() . ' in sector ' . Globals::getSectorBBLink($this->getSectorID()) . '. The planet is owned by ' . $owner->getBBLink();
				if ($owner->hasAlliance()) {
					$text .= ', a member of ' . $owner->getAllianceBBLink();
				}
				$text .= '.';
				$this->db->write('INSERT INTO news (game_id, time, news_message, type,killer_id,killer_alliance,dead_id,dead_alliance) VALUES (' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber(Smr\Epoch::time()) . ', ' . $this->db->escapeString($text) . ', \'BREAKING\',' . $this->db->escapeNumber($trigger->getAccountID()) . ',' . $this->db->escapeNumber($trigger->getAllianceID()) . ',' . $this->db->escapeNumber($owner->getAccountID()) . ',' . $this->db->escapeNumber($owner->getAllianceID()) . ')');
			}
		}
	}


	public function getPlayers() : array {
		return SmrPlayer::getPlanetPlayers($this->getGameID(), $this->getSectorID());
	}

	public function countPlayers() : int {
		return count($this->getPlayers());
	}

	public function hasPlayers() : bool {
		return $this->countPlayers() > 0;
	}

	public function getOtherTraders(AbstractSmrPlayer $player) : array {
		$players = SmrPlayer::getPlanetPlayers($this->getGameID(), $this->getSectorID()); //Do not use & because we unset something and only want that in what we return
		unset($players[$player->getAccountID()]);
		return $players;
	}

	public function hasOtherTraders(AbstractSmrPlayer $player) : bool {
		return count($this->getOtherTraders($player)) > 0;
	}

	public function hasEnemyTraders(AbstractSmrPlayer $player) : bool {
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

	public function hasFriendlyTraders(AbstractSmrPlayer $player) : bool {
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

	public function getWeapons() : array {
		$weapons = $this->getMountedWeapons();
		for ($i = 0; $i < $this->getBuilding(PLANET_TURRET); ++$i) {
			$weapons[] = SmrWeapon::getWeapon(WEAPON_PLANET_TURRET);
		}
		return $weapons;
	}

	public function hasWeapons() : bool {
		return count($this->getWeapons()) > 0;
	}

	public function shootPlayers(array $targetPlayers) : array {
		$results = array('Planet' => $this, 'TotalDamage' => 0, 'TotalDamagePerTargetPlayer' => array());
		foreach ($targetPlayers as $targetPlayer) {
			$results['TotalDamagePerTargetPlayer'][$targetPlayer->getAccountID()] = 0;
		}
		if ($this->isDestroyed()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		$weapons = $this->getWeapons();
		foreach ($weapons as $orderID => $weapon) {
			$results['Weapons'][$orderID] = $weapon->shootPlayerAsPlanet($this, array_rand_value($targetPlayers));
			if ($results['Weapons'][$orderID]['Hit']) {
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
				$results['TotalDamagePerTargetPlayer'][$results['Weapons'][$orderID]['TargetPlayer']->getAccountID()] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
			}
		}
		if ($this->hasCDs()) {
			$thisCDs = new SmrCombatDrones($this->getCDs(), true);
			$results['Drones'] = $thisCDs->shootPlayerAsPlanet($this, array_rand_value($targetPlayers));
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
			$results['TotalDamagePerTargetPlayer'][$results['Drones']['TargetPlayer']->getAccountID()] += $results['Drones']['ActualDamage']['TotalDamage'];
		}
		return $results;
	}

	/**
	 * Returns an array of structure losses due to damage taken.
	 */
	public function checkForDowngrade(int $damage) : array {
		$results = [];
		// For every 70 damage there is a 15% chance of destroying a structure.
		// Which structure is destroyed depends on the ratio of buildings and
		// the time it takes to build them.
		$numChances = floor($damage / self::DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE);
		for ($i = 0; $i < $numChances; $i++) {
			// Stop if the planet has no more buildlings
			if ($this->getLevel() == 0) {
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

	public function takeDamage(array $damage, bool $delayed) : array {
		$alreadyDead = $this->isDestroyed(true);
		$shieldDamage = 0;
		$cdDamage = 0;
		$armourDamage = 0;
		if (!$alreadyDead) {
			if ($damage['Shield'] || !$this->hasShields(true)) {
				$shieldDamage = $this->takeDamageToShields(min($damage['MaxDamage'], $damage['Shield']), $delayed);
				$damage['Shield'] -= $shieldDamage;
				$damage['MaxDamage'] -= $shieldDamage;

				if (!$this->hasShields(true) && ($shieldDamage == 0 || $damage['Rollover'])) {
					if ($this->hasCDs(true)) {
						$cdDamage = $this->takeDamageToCDs(min($damage['MaxDamage'], $damage['Armour']), $delayed);
						$damage['Armour'] -= $cdDamage;
						$damage['MaxDamage'] -= $cdDamage;
					}
					if ($this->hasArmour(true) && ($cdDamage == 0 || $damage['Rollover'])) {
						$armourDamage = $this->takeDamageToArmour(min($damage['MaxDamage'], $damage['Armour']), $delayed);
						$damage['Armour'] -= $armourDamage;
						$damage['MaxDamage'] -= $armourDamage;
					}
				}

			} else { // hit drones behind shields - we should only use this reduced damage branch if we cannot hit shields.
				$cdDamage = $this->takeDamageToCDs(IFloor(min($damage['MaxDamage'], $damage['Armour']) * DRONES_BEHIND_SHIELDS_DAMAGE_PERCENT), $delayed);
			}
		}

		$return = array(
			'KillingShot' => !$alreadyDead && $this->isDestroyed(true),
			'TargetAlreadyDead' => $alreadyDead,
			'Shield' => $shieldDamage,
			'Armour' => $armourDamage,
			'HasShields' => $this->hasShields(true),
			'HasArmour' => $this->hasArmour(true),
			'CDs' => $cdDamage,
			'NumCDs' => $cdDamage / CD_ARMOUR,
			'HasCDs' => $this->hasCDs(true),
			'TotalDamage' => $shieldDamage + $cdDamage + $armourDamage
		);
		return $return;
	}

	protected function takeDamageToShields(int $damage, bool $delayed) : int {
		$actualDamage = min($this->getShields(true), $damage);
		$this->decreaseShields($actualDamage, $delayed);
		return $actualDamage;
	}

	protected function takeDamageToCDs(int $damage, bool $delayed) : int {
		$actualDamage = min($this->getCDs(true), IFloor($damage / CD_ARMOUR));
		$this->decreaseCDs($actualDamage, $delayed);
		return $actualDamage * CD_ARMOUR;
	}

	protected function takeDamageToArmour(int $damage, bool $delayed) : int {
		$actualDamage = min($this->getArmour(true), $damage);
		$this->decreaseArmour($actualDamage, $delayed);
		return $actualDamage;
	}

	public function creditCurrentAttackersForKill() : void {
		//get all players involved for HoF
		$dbResult = $this->db->read('SELECT account_id,level FROM player_attacks_planet WHERE ' . $this->SQL . ' AND time > ' . $this->db->escapeNumber(Smr\Epoch::time() - self::TIME_TO_CREDIT_BUST));
		foreach ($dbResult->records() as $dbRecord) {
			$currPlayer = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $this->getGameID());
			$currPlayer->increaseHOF($dbRecord->getInt('level'), array('Combat', 'Planet', 'Levels'), HOF_PUBLIC);
			$currPlayer->increaseHOF(1, array('Combat', 'Planet', 'Completed'), HOF_PUBLIC);
		}
		$this->db->write('DELETE FROM player_attacks_planet WHERE ' . $this->SQL);
	}

	public function killPlanetByPlayer(AbstractSmrPlayer $killer) : array {
		$return = array();
		$this->creditCurrentAttackersForKill();

		//kick everyone from planet
		$this->db->write('UPDATE player SET land_on_planet = \'FALSE\' WHERE ' . $this->SQL);
		$this->removeOwner();
		$this->removePassword();
		return $return;
	}

}
