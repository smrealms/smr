<?php declare(strict_types=1);

use Smr\PlanetTypes\PlanetType;

class SmrPlanet {

	protected static $CACHE_PLANETS = [];

	public const DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE = 100;
	protected const CHANCE_TO_DOWNGRADE = 15; // percent
	protected const TIME_TO_CREDIT_BUST = 10800; // 3 hours
	protected const TIME_ATTACK_NEWS_COOLDOWN = 3600; // 1 hour
	public const MAX_STOCKPILE = 600;

	protected Smr\Database $db;
	protected readonly string $SQL;

	protected bool $exists;
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
	protected PlanetType $typeInfo;

	protected bool $hasChanged = false;
	protected bool $hasChangedFinancial = false; // for credits, bond, maturity
	protected bool $hasChangedStockpile = false;
	protected array $hasChangedWeapons = [];
	protected array $hasChangedBuildings = [];
	protected array $hasStoppedBuilding = [];
	protected bool $isNew = false;

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

	public static function getGalaxyPlanets(int $gameID, int $galaxyID, bool $forceUpdate = false): array {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT planet.* FROM planet LEFT JOIN sector USING (game_id, sector_id) WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND galaxy_id = ' . $db->escapeNumber($galaxyID));
		$galaxyPlanets = [];
		foreach ($dbResult->records() as $dbRecord) {
			$sectorID = $dbRecord->getInt('sector_id');
			$galaxyPlanets[$sectorID] = self::getPlanet($gameID, $sectorID, $forceUpdate, $dbRecord);
		}
		return $galaxyPlanets;
	}

	public static function getPlanet(int $gameID, int $sectorID, bool $forceUpdate = false, Smr\DatabaseRecord $dbRecord = null): self {
		if ($forceUpdate || !isset(self::$CACHE_PLANETS[$gameID][$sectorID])) {
			self::$CACHE_PLANETS[$gameID][$sectorID] = new self($gameID, $sectorID, $dbRecord);
		}
		return self::$CACHE_PLANETS[$gameID][$sectorID];
	}

	public static function createPlanet(int $gameID, int $sectorID, int $typeID = 1, int $inhabitableTime = null): self {
		if (self::getPlanet($gameID, $sectorID)->exists()) {
			throw new Exception('Planet already exists in sector ' . $sectorID . ' game ' . $gameID);
		}

		if ($inhabitableTime === null) {
			$minTime = SmrGame::getGame($gameID)->getStartTime();
			$inhabitableTime = $minTime + pow(rand(45, 85), 3);
		}

		// insert planet into db
		$db = Smr\Database::getInstance();
		$db->insert('planet', [
			'game_id' => $db->escapeNumber($gameID),
			'sector_id' => $db->escapeNumber($sectorID),
			'inhabitable_time' => $db->escapeNumber($inhabitableTime),
			'planet_type_id' => $db->escapeNumber($typeID),
		]);
		return self::getPlanet($gameID, $sectorID, true);
	}

	public static function removePlanet(int $gameID, int $sectorID): void {
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

	protected function __construct(
		protected readonly int $gameID,
		protected readonly int $sectorID,
		Smr\DatabaseRecord $dbRecord = null
	) {
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
		$this->setMaturity(Smr\Epoch::time() + $this->getBondTime());
	}

	public function getGameID(): int {
		return $this->gameID;
	}

	public function getGame(): SmrGame {
		return SmrGame::getGame($this->gameID);
	}

	public function getSectorID(): int {
		return $this->sectorID;
	}

	public function getGalaxy(): SmrGalaxy {
		return SmrGalaxy::getGalaxyContaining($this->getGameID(), $this->getSectorID());
	}

	public function getOwnerID(): int {
		return $this->ownerID;
	}

	public function hasOwner(): bool {
		return $this->ownerID != 0;
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

	public function getOwner(): SmrPlayer {
		return SmrPlayer::getPlayer($this->getOwnerID(), $this->getGameID());
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

	public function getMountedWeapons(): array {
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

	public function hasMountedWeapon(int $orderID): bool {
		$this->getMountedWeapons(); // Make sure array is initialized
		return isset($this->mountedWeapons[$orderID]);
	}

	public function addMountedWeapon(SmrWeapon $weapon, int $orderID): void {
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
		if ($orderID == 0) {
			throw new Exception('Cannot move this weapon up!');
		}
		$this->swapMountedWeapons($orderID - 1, $orderID);
	}

	public function moveMountedWeaponDown(int $orderID): void {
		if ($orderID == $this->getMaxMountedWeapons() - 1) {
			throw new Exception('Cannot move this weapon down!');
		}
		$this->swapMountedWeapons($orderID + 1, $orderID);
	}


	public function isDestroyed(): bool {
		return !$this->hasCDs() && !$this->hasShields() && !$this->hasArmour();
	}

	public function exists(): bool {
		return $this->exists;
	}

	/**
	 * @return ($goodID is null ? array<int, int> : int)
	 */
	public function getStockpile(int $goodID = null): int|array {
		if (!isset($this->stockpile)) {
			// initialize cargo array
			$this->stockpile = [];
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

	public function hasStockpile(int $goodID = null): bool {
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

	public function getBuildings(): array {
		if (!isset($this->buildings)) {
			$this->buildings = [];

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

	public function getBuilding(int $buildingTypeID): int {
		$buildings = $this->getBuildings();
		if (isset($buildings[$buildingTypeID])) {
			return $buildings[$buildingTypeID];
		}
		return 0;
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

	public function getCurrentlyBuilding(): array {
		if (!isset($this->currentlyBuilding)) {
			$this->currentlyBuilding = [];

			$dbResult = $this->db->read('SELECT * FROM planet_is_building WHERE ' . $this->SQL);
			foreach ($dbResult->records() as $dbRecord) {
				$this->currentlyBuilding[$dbRecord->getInt('building_slot_id')] = [
					'BuildingSlotID' => $dbRecord->getInt('building_slot_id'),
					'ConstructionID' => $dbRecord->getInt('construction_id'),
					'ConstructorID' => $dbRecord->getInt('constructor_id'),
					'Finishes' => $dbRecord->getInt('time_complete'),
					'TimeRemaining' => $dbRecord->getInt('time_complete') - Smr\Epoch::time(),
				];
			}

			// Check if construction has completed
			foreach ($this->currentlyBuilding as $id => $building) {
				if ($building['TimeRemaining'] <= 0) {
					unset($this->currentlyBuilding[$id]);
					$expGain = $this->getConstructionExp($building['ConstructionID']);
					$player = SmrPlayer::getPlayer($building['ConstructorID'], $this->getGameID());
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
	public function getMaxBuildings(int $buildingTypeID = null): int|array {
		if ($buildingTypeID === null) {
			$structs = $this->typeInfo::STRUCTURES;
			return array_combine(
				array_keys($structs),
				array_column($structs, 'max_amount')
			);
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
		$this->db->write('UPDATE planet SET planet_type_id = ' . $this->db->escapeNumber($num) . ' WHERE ' . $this->SQL);
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

	public function getStructureTypes(int $structureID = null): SmrPlanetStructureType|array {
		return $this->typeInfo->structureTypes($structureID);
	}

	public function hasStructureType(int $structureID): bool {
		return isset($this->getStructureTypes()[$structureID]);
	}

	/**
	 * Specifies which menu options the planet has.
	 */
	public function hasMenuOption(string $option): bool {
		// We do not set options that are unavailable
		return in_array($option, $this->typeInfo->menuOptions());
	}

	public function update(): void {
		if (!$this->exists()) {
			return;
		}
		if ($this->hasChanged) {
			$this->db->write('UPDATE planet SET
									owner_id = ' . $this->db->escapeNumber($this->ownerID) . ',
									password = ' . $this->db->escapeString($this->password) . ',
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
					$this->db->replace('planet_has_cargo', [
						'game_id' => $this->db->escapeNumber($this->getGameID()),
						'sector_id' => $this->db->escapeNumber($this->getSectorID()),
						'good_id' => $this->db->escapeNumber($id),
						'amount' => $this->db->escapeNumber($amount),
					]);
				} else {
					$this->db->write('DELETE FROM planet_has_cargo WHERE ' . $this->SQL . '
										AND good_id = ' . $this->db->escapeNumber($id));
				}
			}
		}

		if (count($this->hasChangedWeapons) > 0) {
			foreach (array_keys($this->hasChangedWeapons) as $orderID) {
				if (isset($this->mountedWeapons[$orderID])) {
					$this->db->replace('planet_has_weapon', [
						'game_id' => $this->db->escapeNumber($this->getGameID()),
						'sector_id' => $this->db->escapeNumber($this->getSectorID()),
						'order_id' => $this->db->escapeNumber($orderID),
						'weapon_type_id' => $this->db->escapeNumber($this->mountedWeapons[$orderID]->getWeaponTypeID()),
						'bonus_accuracy' => $this->db->escapeBoolean($this->mountedWeapons[$orderID]->hasBonusAccuracy()),
						'bonus_damage' => $this->db->escapeBoolean($this->mountedWeapons[$orderID]->hasBonusDamage()),
					]);
				} else {
					$this->db->write('DELETE FROM planet_has_weapon WHERE ' . $this->SQL . ' AND order_id=' . $this->db->escapeNumber($orderID));
				}
			}
			$this->hasChangedWeapons = [];
		}

		if (count($this->hasStoppedBuilding) > 0) {
			$this->db->write('DELETE FROM planet_is_building WHERE ' . $this->SQL . '
								AND building_slot_id IN (' . $this->db->escapeArray($this->hasStoppedBuilding) . ') LIMIT ' . count($this->hasStoppedBuilding));
			$this->hasStoppedBuilding = [];
		}
		// write building info
		foreach ($this->hasChangedBuildings as $id => $hasChanged) {
			if ($hasChanged === true) {
				if ($this->hasBuilding($id)) {
					$this->db->replace('planet_has_building', [
						'game_id' => $this->db->escapeNumber($this->gameID),
						'sector_id' => $this->db->escapeNumber($this->sectorID),
						'construction_id' => $this->db->escapeNumber($id),
						'amount' => $this->db->escapeNumber($this->getBuilding($id)),
					]);
				} else {
					$this->db->write('DELETE FROM planet_has_building WHERE ' . $this->SQL . '
										AND construction_id = ' . $this->db->escapeNumber($id));
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
		return SmrWeapon::getWeapon(WEAPON_PLANET_TURRET)->getModifiedPlanetAccuracy($this);
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
	public function getBuildRestriction(AbstractSmrPlayer $constructor, int $constructionID): string|false {
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
	private function getCompletionModifier(int $constructionID): float {
		$currentBuildings = $this->getBuilding($constructionID);
		$maxBuildings = $this->getMaxBuildings($constructionID);
		return 0.01 + 2.97 * pow($currentBuildings / $maxBuildings, 2);
	}

	// Amount of exp gained to build the next building of this type
	private function getConstructionExp(int $constructionID): int {
		$expGain = $this->getStructureTypes($constructionID)->expGain();
		return $expGain;
	}

	// Amount of time (in seconds) to build the next building of this type
	public function getConstructionTime(int $constructionID): int {
		$baseTime = $this->getStructureTypes($constructionID)->baseTime();
		$constructionTime = ICeil($baseTime * $this->getCompletionModifier($constructionID) / $this->getGame()->getGameSpeed());
		return $constructionTime;
	}

	/**
	 * @throws \Smr\Exceptions\UserError If the player cannot build the structure.
	 */
	public function startBuilding(AbstractSmrPlayer $constructor, int $constructionID): void {
		$restriction = $this->getBuildRestriction($constructor, $constructionID);
		if ($restriction !== false) {
			throw new \Smr\Exceptions\UserError('Unable to start building: ' . $restriction);
		}

		// gets the time for the buildings
		$timeComplete = Smr\Epoch::time() + $this->getConstructionTime($constructionID);
		$insertID = $this->db->insert('planet_is_building', [
			'game_id' => $this->db->escapeNumber($this->getGameID()),
			'sector_id' => $this->db->escapeNumber($this->getSectorID()),
			'construction_id' => $this->db->escapeNumber($constructionID),
			'constructor_id' => $this->db->escapeNumber($constructor->getAccountID()),
			'time_complete' => $this->db->escapeNumber($timeComplete),
		]);

		$this->currentlyBuilding[$insertID] = [
			'BuildingSlotID' => $insertID,
			'ConstructionID' => $constructionID,
			'ConstructorID' => $constructor->getAccountID(),
			'Finishes' => $timeComplete,
			'TimeRemaining' => $timeComplete - Smr\Epoch::time(),
		];

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

	public function stopBuilding(int $constructionID): bool {
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
		return $this->inhabitableTime <= Smr\Epoch::time();
	}

	public function getInhabitableTime(): int {
		return $this->inhabitableTime;
	}

	public function getExamineHREF(): string {
		return Page::create('planet_examine.php')->href();
	}

	public function getLandHREF(): string {
		return Page::create('planet_land_processing.php')->href();
	}

	public function getAttackHREF(): string {
		return Page::create('planet_attack_processing.php')->href();
	}

	public function getBuildHREF(int $structureID): string {
		$container = Page::create('planet_construction_processing.php');
		$container['construction_id'] = $structureID;
		$container['action'] = 'Build';
		return $container->href();
	}

	public function getCancelHREF(int $structureID): string {
		$container = Page::create('planet_construction_processing.php');
		$container['construction_id'] = $structureID;
		$container['action'] = 'Cancel';
		return $container->href();
	}

	public function getFinancesHREF(): string {
		return Page::create('planet_financial_processing.php')->href();
	}

	public function getBondConfirmationHREF(): string {
		return Page::create('planet_bond_confirmation.php')->href();
	}

	public function attackedBy(AbstractSmrPlayer $trigger, array $attackers): void {
		$trigger->increaseHOF(1, ['Combat', 'Planet', 'Number Of Triggers'], HOF_PUBLIC);
		foreach ($attackers as $attacker) {
			$attacker->increaseHOF(1, ['Combat', 'Planet', 'Number Of Attacks'], HOF_PUBLIC);
			$this->db->replace('player_attacks_planet', [
				'game_id' => $this->db->escapeNumber($this->getGameID()),
				'account_id' => $this->db->escapeNumber($attacker->getAccountID()),
				'sector_id' => $this->db->escapeNumber($this->getSectorID()),
				'time' => $this->db->escapeNumber(Smr\Epoch::time()),
				'level' => $this->db->escapeNumber($this->getLevel()),
			]);
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
				$this->db->insert('news', [
					'game_id' => $this->db->escapeNumber($this->getGameID()),
					'time' => $this->db->escapeNumber(Smr\Epoch::time()),
					'news_message' => $this->db->escapeString($text),
					'type' => $this->db->escapeString('breaking'),
					'killer_id' => $this->db->escapeNumber($trigger->getAccountID()),
					'killer_alliance' => $this->db->escapeNumber($trigger->getAllianceID()),
					'dead_id' => $this->db->escapeNumber($owner->getAccountID()),
					'dead_alliance' => $this->db->escapeNumber($owner->getAllianceID()),
				]);
			}
		}
	}


	public function getPlayers(): array {
		return SmrPlayer::getPlanetPlayers($this->getGameID(), $this->getSectorID());
	}

	public function countPlayers(): int {
		return count($this->getPlayers());
	}

	public function hasPlayers(): bool {
		return $this->countPlayers() > 0;
	}

	public function getOtherTraders(AbstractSmrPlayer $player): array {
		$players = SmrPlayer::getPlanetPlayers($this->getGameID(), $this->getSectorID()); //Do not use & because we unset something and only want that in what we return
		unset($players[$player->getAccountID()]);
		return $players;
	}

	public function hasOtherTraders(AbstractSmrPlayer $player): bool {
		return count($this->getOtherTraders($player)) > 0;
	}

	public function hasEnemyTraders(AbstractSmrPlayer $player): bool {
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

	public function hasFriendlyTraders(AbstractSmrPlayer $player): bool {
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

	public function getWeapons(): array {
		$weapons = $this->getMountedWeapons();
		return array_pad(
			$weapons,
			count($weapons) + $this->getBuilding(PLANET_TURRET),
			SmrWeapon::getWeapon(WEAPON_PLANET_TURRET)
		);
	}

	public function shootPlayers(array $targetPlayers): array {
		$results = ['Planet' => $this, 'TotalDamage' => 0, 'TotalDamagePerTargetPlayer' => []];
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
	public function checkForDowngrade(int $damage): array {
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

	public function takeDamage(array $damage): array {
		$alreadyDead = $this->isDestroyed();
		$shieldDamage = 0;
		$cdDamage = 0;
		$armourDamage = 0;
		if (!$alreadyDead) {
			if ($damage['Shield'] || !$this->hasShields()) {
				$shieldDamage = $this->takeDamageToShields(min($damage['MaxDamage'], $damage['Shield']));
				$damage['Shield'] -= $shieldDamage;
				$damage['MaxDamage'] -= $shieldDamage;

				if (!$this->hasShields() && ($shieldDamage == 0 || $damage['Rollover'])) {
					if ($this->hasCDs()) {
						$cdDamage = $this->takeDamageToCDs(min($damage['MaxDamage'], $damage['Armour']));
						$damage['Armour'] -= $cdDamage;
						$damage['MaxDamage'] -= $cdDamage;
					}
					if ($this->hasArmour() && ($cdDamage == 0 || $damage['Rollover'])) {
						$armourDamage = $this->takeDamageToArmour(min($damage['MaxDamage'], $damage['Armour']));
						$damage['Armour'] -= $armourDamage;
						$damage['MaxDamage'] -= $armourDamage;
					}
				}

			} else { // hit drones behind shields - we should only use this reduced damage branch if we cannot hit shields.
				$cdDamage = $this->takeDamageToCDs(IFloor(min($damage['MaxDamage'], $damage['Armour']) * DRONES_BEHIND_SHIELDS_DAMAGE_PERCENT));
			}
		}

		$return = [
			'KillingShot' => !$alreadyDead && $this->isDestroyed(),
			'TargetAlreadyDead' => $alreadyDead,
			'Shield' => $shieldDamage,
			'Armour' => $armourDamage,
			'HasShields' => $this->hasShields(),
			'HasArmour' => $this->hasArmour(),
			'CDs' => $cdDamage,
			'NumCDs' => $cdDamage / CD_ARMOUR,
			'HasCDs' => $this->hasCDs(),
			'TotalDamage' => $shieldDamage + $cdDamage + $armourDamage,
		];
		return $return;
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
		$dbResult = $this->db->read('SELECT account_id,level FROM player_attacks_planet WHERE ' . $this->SQL . ' AND time > ' . $this->db->escapeNumber(Smr\Epoch::time() - self::TIME_TO_CREDIT_BUST));
		foreach ($dbResult->records() as $dbRecord) {
			$currPlayer = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $this->getGameID());
			$currPlayer->increaseHOF($dbRecord->getInt('level'), ['Combat', 'Planet', 'Levels'], HOF_PUBLIC);
			$currPlayer->increaseHOF(1, ['Combat', 'Planet', 'Completed'], HOF_PUBLIC);
		}
		$this->db->write('DELETE FROM player_attacks_planet WHERE ' . $this->SQL);
	}

	public function killPlanetByPlayer(AbstractSmrPlayer $killer): array {
		$return = [];
		$this->creditCurrentAttackersForKill();

		//kick everyone from planet
		$this->db->write('UPDATE player SET land_on_planet = \'FALSE\' WHERE ' . $this->SQL);
		$this->removeOwner();
		$this->removePassword();
		return $return;
	}

}
