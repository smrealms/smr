<?php declare(strict_types=1);

use Smr\Database;
use Smr\DatabaseRecord;
use Smr\ShipClass;

class AbstractSmrLocation {

	/** @var array<int, SmrLocation> */
	protected static array $CACHE_ALL_LOCATIONS;
	/** @var array<int, SmrLocation> */
	protected static array $CACHE_LOCATIONS = [];
	/** @var array<int, array<int, array<int, SmrLocation>>> */
	protected static array $CACHE_SECTOR_LOCATIONS = [];

	protected Database $db;
	protected readonly string $SQL;

	protected string $name;
	protected ?string $processor;
	protected string $image;

	protected bool $fed;
	protected bool $bank;
	protected bool $bar;
	protected bool $HQ;
	protected bool $UG;

	/** @var array<int, array<string, string|int>> */
	protected array $hardwareSold;
	/** @var array<int, SmrShipType> */
	protected array $shipsSold;
	/** @var array<int, SmrWeapon> */
	protected array $weaponsSold;

	public static function clearCache(): void {
		self::$CACHE_ALL_LOCATIONS = [];
		self::$CACHE_LOCATIONS = [];
		self::$CACHE_SECTOR_LOCATIONS = [];
	}

	/**
	 * @return array<int, SmrLocation>
	 */
	public static function getAllLocations(int $gameID, bool $forceUpdate = false): array {
		if ($forceUpdate || !isset(self::$CACHE_ALL_LOCATIONS)) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM location_type ORDER BY location_type_id');
			$locations = [];
			foreach ($dbResult->records() as $dbRecord) {
				$locationTypeID = $dbRecord->getInt('location_type_id');
				$locations[$locationTypeID] = SmrLocation::getLocation($gameID, $locationTypeID, $forceUpdate, $dbRecord);
			}
			self::$CACHE_ALL_LOCATIONS = $locations;
		}
		return self::$CACHE_ALL_LOCATIONS;
	}

	/**
	 * @return array<int, array<int, SmrLocation>>
	 */
	public static function getGalaxyLocations(int $gameID, int $galaxyID, bool $forceUpdate = false): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT location_type.*, sector_id FROM location LEFT JOIN sector USING(game_id, sector_id) LEFT JOIN location_type USING (location_type_id) WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND galaxy_id = ' . $db->escapeNumber($galaxyID));
		$galaxyLocations = [];
		foreach ($dbResult->records() as $dbRecord) {
			$sectorID = $dbRecord->getInt('sector_id');
			$locationTypeID = $dbRecord->getInt('location_type_id');
			$location = self::getLocation($gameID, $locationTypeID, $forceUpdate, $dbRecord);
			self::$CACHE_SECTOR_LOCATIONS[$gameID][$sectorID][$locationTypeID] = $location;
			$galaxyLocations[$sectorID][$locationTypeID] = $location;
		}
		return $galaxyLocations;
	}

	/**
	 * @return array<int, SmrLocation>
	 */
	public static function getSectorLocations(int $gameID, int $sectorID, bool $forceUpdate = false): array {
		if ($forceUpdate || !isset(self::$CACHE_SECTOR_LOCATIONS[$gameID][$sectorID])) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM location LEFT JOIN location_type USING (location_type_id) WHERE sector_id = ' . $db->escapeNumber($sectorID) . ' AND game_id=' . $db->escapeNumber($gameID));
			$locations = [];
			foreach ($dbResult->records() as $dbRecord) {
				$locationTypeID = $dbRecord->getInt('location_type_id');
				$locations[$locationTypeID] = self::getLocation($gameID, $locationTypeID, $forceUpdate, $dbRecord);
			}
			self::$CACHE_SECTOR_LOCATIONS[$gameID][$sectorID] = $locations;
		}
		return self::$CACHE_SECTOR_LOCATIONS[$gameID][$sectorID];
	}

	public static function addSectorLocation(int $gameID, int $sectorID, SmrLocation $location): void {
		self::getSectorLocations($gameID, $sectorID); // make sure cache is populated
		$db = Database::getInstance();
		$db->insert('location', [
			'game_id' => $db->escapeNumber($gameID),
			'sector_id' => $db->escapeNumber($sectorID),
			'location_type_id' => $db->escapeNumber($location->getTypeID()),
		]);
		self::$CACHE_SECTOR_LOCATIONS[$gameID][$sectorID][$location->getTypeID()] = $location;
	}

	public static function moveSectorLocation(int $gameID, int $oldSectorID, int $newSectorID, SmrLocation $location): void {
		if ($oldSectorID === $newSectorID) {
			return;
		}

		// Make sure cache is populated
		self::getSectorLocations($gameID, $oldSectorID);
		self::getSectorLocations($gameID, $newSectorID);

		$db = Database::getInstance();
		$db->write('UPDATE location SET sector_id = ' . $db->escapeNumber($newSectorID) . ' WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND sector_id = ' . $db->escapeNumber($oldSectorID) . ' AND location_type_id = ' . $location->getTypeID());
		unset(self::$CACHE_SECTOR_LOCATIONS[$gameID][$oldSectorID][$location->getTypeID()]);
		self::$CACHE_SECTOR_LOCATIONS[$gameID][$newSectorID][$location->getTypeID()] = $location;

		// Preserve the same element order that we'd have in getSectorLocations
		ksort(self::$CACHE_SECTOR_LOCATIONS[$gameID][$newSectorID]);
	}

	public static function removeSectorLocations(int $gameID, int $sectorID): void {
		$db = Database::getInstance();
		$db->write('DELETE FROM location WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND sector_id = ' . $db->escapeNumber($sectorID));
		self::$CACHE_SECTOR_LOCATIONS[$gameID][$sectorID] = [];
	}

	public static function getLocation(int $gameID, int $locationTypeID, bool $forceUpdate = false, DatabaseRecord $dbRecord = null): SmrLocation {
		if ($forceUpdate || !isset(self::$CACHE_LOCATIONS[$locationTypeID])) {
			self::$CACHE_LOCATIONS[$locationTypeID] = new SmrLocation($gameID, $locationTypeID, $dbRecord);
		}
		return self::$CACHE_LOCATIONS[$locationTypeID];
	}

	protected function __construct(
		protected readonly int $gameID, // use 0 to be independent of game
		protected readonly int $typeID,
		DatabaseRecord $dbRecord = null
	) {
		$this->db = Database::getInstance();
		$this->SQL = 'location_type_id = ' . $this->db->escapeNumber($typeID);

		if ($dbRecord === null) {
			$dbResult = $this->db->read('SELECT * FROM location_type WHERE ' . $this->SQL);
			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
			}
		}
		$locationExists = $dbRecord !== null;

		if ($locationExists) {
			$this->name = $dbRecord->getString('location_name');
			$this->processor = $dbRecord->getNullableString('location_processor');
			$this->image = $dbRecord->getString('location_image');
		} else {
			throw new Exception('Cannot find location: ' . $typeID);
		}
	}

	public function getGameID(): int {
		return $this->gameID;
	}

	public function getTypeID(): int {
		return $this->typeID;
	}

	public function getRaceID(): int {
		if ($this->isFed() && $this->getTypeID() != LOCATION_TYPE_FEDERAL_BEACON) {
			return $this->getTypeID() - LOCATION_GROUP_RACIAL_BEACONS;
		}
		if ($this->isHQ() && $this->getTypeID() != LOCATION_TYPE_FEDERAL_HQ) {
			return $this->getTypeID() - LOCATION_GROUP_RACIAL_HQS;
		}
		return RACE_NEUTRAL;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name): void {
		$name = htmlentities($name, ENT_COMPAT, 'utf-8');
		if ($this->name === $name) {
			return;
		}
		$this->name = $name;
		$this->db->write('UPDATE location_type SET location_name=' . $this->db->escapeString($this->name) . ' WHERE ' . $this->SQL);
	}

	public function hasAction(): bool {
		return $this->processor !== null;
	}

	public function getAction(): ?string {
		return $this->processor;
	}

	public function getImage(): string {
		return $this->image;
	}

	public function isFed(): bool {
		if (!isset($this->fed)) {
			$dbResult = $this->db->read('SELECT 1 FROM location_is_fed WHERE ' . $this->SQL);
			$this->fed = $dbResult->hasRecord();
		}
		return $this->fed;
	}

	public function setFed(bool $bool): void {
		if ($this->fed === $bool) {
			return;
		}
		if ($bool) {
			$this->db->write('INSERT IGNORE INTO location_is_fed (location_type_id) values (' . $this->db->escapeNumber($this->getTypeID()) . ')');
		} else {
			$this->db->write('DELETE FROM location_is_fed WHERE ' . $this->SQL);
		}
		$this->fed = $bool;
	}

	public function isBank(): bool {
		if (!isset($this->bank)) {
			$dbResult = $this->db->read('SELECT 1 FROM location_is_bank WHERE ' . $this->SQL);
			$this->bank = $dbResult->hasRecord();
		}
		return $this->bank;
	}

	public function setBank(bool $bool): void {
		if ($this->bank === $bool) {
			return;
		}
		if ($bool) {
			$this->db->insert('location_is_bank', [
				'location_type_id' => $this->db->escapeNumber($this->getTypeID()),
			]);
		} else {
			$this->db->write('DELETE FROM location_is_bank WHERE ' . $this->SQL);
		}
		$this->bank = $bool;
	}

	public function isBar(): bool {
		if (!isset($this->bar)) {
			$dbResult = $this->db->read('SELECT 1 FROM location_is_bar WHERE ' . $this->SQL);
			$this->bar = $dbResult->hasRecord();
		}
		return $this->bar;
	}

	public function setBar(bool $bool): void {
		if ($this->bar === $bool) {
			return;
		}
		if ($bool) {
			$this->db->write('INSERT IGNORE INTO location_is_bar (location_type_id) values (' . $this->db->escapeNumber($this->getTypeID()) . ')');
		} else {
			$this->db->write('DELETE FROM location_is_bar WHERE ' . $this->SQL);
		}
		$this->bar = $bool;
	}

	public function isHQ(): bool {
		if (!isset($this->HQ)) {
			$dbResult = $this->db->read('SELECT 1 FROM location_is_hq WHERE ' . $this->SQL);
			$this->HQ = $dbResult->hasRecord();
		}
		return $this->HQ;
	}

	public function setHQ(bool $bool): void {
		if ($this->HQ === $bool) {
			return;
		}
		if ($bool) {
			$this->db->write('INSERT IGNORE INTO location_is_hq (location_type_id) values (' . $this->db->escapeNumber($this->getTypeID()) . ')');
		} else {
			$this->db->write('DELETE FROM location_is_hq WHERE ' . $this->SQL);
		}
		$this->HQ = $bool;
	}

	public function isUG(): bool {
		if (!isset($this->UG)) {
			$dbResult = $this->db->read('SELECT 1 FROM location_is_ug WHERE ' . $this->SQL);
			$this->UG = $dbResult->hasRecord();
		}
		return $this->UG;
	}

	public function setUG(bool $bool): void {
		if ($this->UG === $bool) {
			return;
		}
		if ($bool) {
			$this->db->insert('location_is_ug', [
				'location_type_id' => $this->db->escapeNumber($this->getTypeID()),
			]);
		} else {
			$this->db->write('DELETE FROM location_is_ug WHERE ' . $this->SQL);
		}
		$this->UG = $bool;
	}

	/**
	 * @return array<int, array<string, string|int>>
	 */
	public function getHardwareSold(): array {
		if (!isset($this->hardwareSold)) {
			$this->hardwareSold = [];
			$dbResult = $this->db->read('SELECT hardware_type_id FROM location_sells_hardware WHERE ' . $this->SQL);
			foreach ($dbResult->records() as $dbRecord) {
				$this->hardwareSold[$dbRecord->getInt('hardware_type_id')] = Globals::getHardwareTypes($dbRecord->getInt('hardware_type_id'));
			}
		}
		return $this->hardwareSold;
	}

	public function isHardwareSold(int $hardwareTypeID = null): bool {
		$hardware = $this->getHardwareSold();
		if ($hardwareTypeID === null) {
			return count($hardware) != 0;
		}
		return isset($hardware[$hardwareTypeID]);
	}

	public function addHardwareSold(int $hardwareTypeID): void {
		if ($this->isHardwareSold($hardwareTypeID)) {
			return;
		}
		$dbResult = $this->db->read('SELECT 1 FROM hardware_type WHERE hardware_type_id = ' . $this->db->escapeNumber($hardwareTypeID));
		if (!$dbResult->hasRecord()) {
			throw new Exception('Invalid hardware type id given');
		}
		$this->db->insert('location_sells_hardware', [
			'location_type_id' => $this->db->escapeNumber($this->getTypeID()),
			'hardware_type_id' => $this->db->escapeNumber($hardwareTypeID),
		]);
		$this->hardwareSold[$hardwareTypeID] = Globals::getHardwareTypes($hardwareTypeID);
	}

	public function removeHardwareSold(int $hardwareTypeID): void {
		if (!$this->isHardwareSold($hardwareTypeID)) {
			return;
		}
		$this->db->write('DELETE FROM location_sells_hardware WHERE ' . $this->SQL . ' AND hardware_type_id = ' . $this->db->escapeNumber($hardwareTypeID));
		unset($this->hardwareSold[$hardwareTypeID]);
	}

	/**
	 * @return array<int, SmrShipType>
	 */
	public function getShipsSold(): array {
		if (!isset($this->shipsSold)) {
			$this->shipsSold = [];
			$dbResult = $this->db->read('SELECT * FROM location_sells_ships JOIN ship_type USING (ship_type_id) WHERE ' . $this->SQL);
			foreach ($dbResult->records() as $dbRecord) {
				$shipTypeID = $dbRecord->getInt('ship_type_id');
				$this->shipsSold[$shipTypeID] = SmrShipType::get($shipTypeID, $dbRecord);
			}

			if ($this->gameID > 0 && SmrGame::getGame($this->gameID)->isGameType(SmrGame::GAME_TYPE_HUNTER_WARS)) {
				// Remove ships that are not allowed in Hunter Wars
				unset($this->shipsSold[SHIP_TYPE_PLANETARY_SUPER_FREIGHTER]);
				foreach ($this->shipsSold as $shipID => $ship) {
					if ($ship->getClass() === ShipClass::Raider) {
						unset($this->shipsSold[$shipID]);
					}
				}
			}
		}
		return $this->shipsSold;
	}

	public function isShipSold(int $shipTypeID = null): bool {
		$ships = $this->getShipsSold();
		if ($shipTypeID === null) {
			return count($ships) != 0;
		}
		return isset($ships[$shipTypeID]);
	}

	public function addShipSold(int $shipTypeID): void {
		if ($this->isShipSold($shipTypeID)) {
			return;
		}
		$ship = SmrShipType::get($shipTypeID);
		$this->db->insert('location_sells_ships', [
			'location_type_id' => $this->db->escapeNumber($this->getTypeID()),
			'ship_type_id' => $this->db->escapeNumber($shipTypeID),
		]);
		$this->shipsSold[$shipTypeID] = $ship;
	}

	public function removeShipSold(int $shipTypeID): void {
		if (!$this->isShipSold($shipTypeID)) {
			return;
		}
		$this->db->write('DELETE FROM location_sells_ships WHERE ' . $this->SQL . ' AND ship_type_id = ' . $this->db->escapeNumber($shipTypeID));
		unset($this->shipsSold[$shipTypeID]);
	}

	/**
	 * @return array<int, SmrWeapon>
	 */
	public function getWeaponsSold(): array {
		if (!isset($this->weaponsSold)) {
			$this->weaponsSold = [];
			$dbResult = $this->db->read('SELECT * FROM location_sells_weapons JOIN weapon_type USING (weapon_type_id) WHERE ' . $this->SQL);
			foreach ($dbResult->records() as $dbRecord) {
				$weaponTypeID = $dbRecord->getInt('weapon_type_id');
				$this->weaponsSold[$weaponTypeID] = SmrWeapon::getWeapon($weaponTypeID, $dbRecord);
			}
		}
		return $this->weaponsSold;
	}

	public function isWeaponSold(int $weaponTypeID = null): bool {
		$weapons = $this->getWeaponsSold();
		if ($weaponTypeID === null) {
			return count($weapons) != 0;
		}
		return isset($weapons[$weaponTypeID]);
	}

	public function addWeaponSold(int $weaponTypeID): void {
		if ($this->isWeaponSold($weaponTypeID)) {
			return;
		}
		$weapon = SmrWeapon::getWeapon($weaponTypeID);
		$this->db->insert('location_sells_weapons', [
			'location_type_id' => $this->db->escapeNumber($this->getTypeID()),
			'weapon_type_id' => $this->db->escapeNumber($weaponTypeID),
		]);
		$this->weaponsSold[$weaponTypeID] = $weapon;
	}

	public function removeWeaponSold(int $weaponTypeID): void {
		if (!$this->isWeaponSold($weaponTypeID)) {
			return;
		}
		$this->db->write('DELETE FROM location_sells_weapons WHERE ' . $this->SQL . ' AND weapon_type_id = ' . $this->db->escapeNumber($weaponTypeID));
		unset($this->weaponsSold[$weaponTypeID]);
	}

	/**
	 * @return array<SmrLocation>
	 */
	public function getLinkedLocations(): array {
		$linkedLocations = [];
		if ($this->isHQ()) {
			if ($this->getTypeID() == LOCATION_TYPE_FEDERAL_HQ) {
				$linkedLocations[] = SmrLocation::getLocation($this->gameID, LOCATION_TYPE_FEDERAL_BEACON);
				$linkedLocations[] = SmrLocation::getLocation($this->gameID, LOCATION_TYPE_FEDERAL_MINT);
			} else {
				$raceID = $this->getRaceID();
				$linkedLocations[] = SmrLocation::getLocation($this->gameID, LOCATION_GROUP_RACIAL_BEACONS + $raceID);
				$linkedLocations[] = SmrLocation::getLocation($this->gameID, LOCATION_GROUP_RACIAL_SHIPS + $raceID);
				$linkedLocations[] = SmrLocation::getLocation($this->gameID, LOCATION_GROUP_RACIAL_SHOPS + $raceID);
			}
		}
		return $linkedLocations;
	}

	public function getExamineHREF(): string {
		$container = Page::create($this->getAction());
		$container['LocationID'] = $this->getTypeID();
		return $container->href();
	}

	public function getEditHREF(): string {
		$container = Page::create('location_edit.php');
		$container['location_type_id'] = $this->getTypeID();
		return $container->href();
	}

	public function equals(SmrLocation $otherLocation): bool {
		return $this->getTypeID() == $otherLocation->getTypeID();
	}

	public function hasX(mixed $x, AbstractSmrPlayer $player = null): bool {
		if ($x instanceof SmrWeaponType) {
			return $this->isWeaponSold($x->getWeaponTypeID());
		}
		if ($x instanceof SmrShipType) {
			return $this->isShipSold($x->getTypeID());
		}
		if (is_array($x) && $x['Type'] == 'Hardware') { // instanceof ShipEquipment)
			return $this->isHardwareSold($x['ID']);
		}
		if (is_string($x)) {
			if ($x == 'Bank') {
				return $this->isBank();
			}
			if ($x == 'Bar') {
				return $this->isBar();
			}
			if ($x == 'Fed') {
				return $this->isFed();
			}
			if ($x == 'SafeFed') {
				return $player != null && $this->isFed() && $player->canBeProtectedByRace($this->getRaceID());
			}
			if ($x == 'HQ') {
				return $this->isHQ();
			}
			if ($x == 'UG') {
				return $this->isUG();
			}
			if ($x == 'Hardware') {
				return $this->isHardwareSold();
			}
			if ($x == 'Ship') {
				return $this->isShipSold();
			}
			if ($x == 'Weapon') {
				return $this->isWeaponSold();
			}
		}
		return false;
	}

}
