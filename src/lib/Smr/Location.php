<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\Combat\Weapon\Weapon;
use Smr\Pages\Admin\EditLocations;
use Smr\Pages\Player\Bank\PersonalBank;
use Smr\Pages\Player\Bar\BarMain;
use Smr\Pages\Player\Headquarters\Government;
use Smr\Pages\Player\Headquarters\Underground;
use Smr\Pages\Player\ShopHardware;
use Smr\Pages\Player\ShopShip;
use Smr\Pages\Player\ShopWeapon;

class Location {

	/** @var array<int, self> */
	protected static array $CACHE_ALL_LOCATIONS;
	/** @var array<int, self> */
	protected static array $CACHE_LOCATIONS = [];
	/** @var array<int, array<int, array<int, self>>> */
	protected static array $CACHE_SECTOR_LOCATIONS = [];

	public const string SQL = 'location_type_id = :location_type_id';
	/** @var array{location_type_id: int} */
	public readonly array $SQLID;

	protected string $name;
	protected ?string $processor;
	protected string $image;

	protected bool $fed;
	protected bool $bank;
	protected bool $bar;
	protected bool $HQ;
	protected bool $UG;

	/** @var array<int, \Smr\HardwareType> */
	protected array $hardwareSold;
	/** @var array<int, ShipType> */
	protected array $shipsSold;
	/** @var array<int, Weapon> */
	protected array $weaponsSold;

	public static function clearCache(): void {
		self::$CACHE_ALL_LOCATIONS = [];
		self::$CACHE_LOCATIONS = [];
		self::$CACHE_SECTOR_LOCATIONS = [];
	}

	/**
	 * @return array<int, self>
	 */
	public static function getAllLocations(int $gameID, bool $forceUpdate = false): array {
		if ($forceUpdate || !isset(self::$CACHE_ALL_LOCATIONS)) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM location_type ORDER BY location_type_id');
			$locations = [];
			foreach ($dbResult->records() as $dbRecord) {
				$locationTypeID = $dbRecord->getInt('location_type_id');
				$locations[$locationTypeID] = self::getLocation($gameID, $locationTypeID, $forceUpdate, $dbRecord);
			}
			self::$CACHE_ALL_LOCATIONS = $locations;
		}
		return self::$CACHE_ALL_LOCATIONS;
	}

	/**
	 * @return array<int, array<int, self>>
	 */
	public static function getGalaxyLocations(int $gameID, int $galaxyID, bool $forceUpdate = false): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT location_type.*, sector_id FROM location LEFT JOIN sector USING(game_id, sector_id) LEFT JOIN location_type USING (location_type_id) WHERE game_id = :game_id AND galaxy_id = :galaxy_id', [
			'game_id' => $db->escapeNumber($gameID),
			'galaxy_id' => $db->escapeNumber($galaxyID),
		]);
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
	 * @return array<int, self>
	 */
	public static function getSectorLocations(int $gameID, int $sectorID, bool $forceUpdate = false): array {
		if ($forceUpdate || !isset(self::$CACHE_SECTOR_LOCATIONS[$gameID][$sectorID])) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM location LEFT JOIN location_type USING (location_type_id) WHERE sector_id = :sector_id AND game_id = :game_id', [
				'sector_id' => $db->escapeNumber($sectorID),
				'game_id' => $db->escapeNumber($gameID),
			]);
			$locations = [];
			foreach ($dbResult->records() as $dbRecord) {
				$locationTypeID = $dbRecord->getInt('location_type_id');
				$locations[$locationTypeID] = self::getLocation($gameID, $locationTypeID, $forceUpdate, $dbRecord);
			}
			self::$CACHE_SECTOR_LOCATIONS[$gameID][$sectorID] = $locations;
		}
		return self::$CACHE_SECTOR_LOCATIONS[$gameID][$sectorID];
	}

	public static function addSectorLocation(int $gameID, int $sectorID, Location $location): void {
		self::getSectorLocations($gameID, $sectorID); // make sure cache is populated
		$db = Database::getInstance();
		$db->insert('location', [
			'game_id' => $gameID,
			'sector_id' => $sectorID,
			...$location->SQLID,
		]);
		self::$CACHE_SECTOR_LOCATIONS[$gameID][$sectorID][$location->getTypeID()] = $location;
	}

	public static function moveSectorLocation(int $gameID, int $oldSectorID, int $newSectorID, Location $location): void {
		if ($oldSectorID === $newSectorID) {
			return;
		}

		// Make sure cache is populated
		self::getSectorLocations($gameID, $oldSectorID);
		self::getSectorLocations($gameID, $newSectorID);

		$db = Database::getInstance();
		$db->update(
			'location',
			['sector_id' => $newSectorID],
			[
				'game_id' => $gameID,
				'sector_id' => $oldSectorID,
				...$location->SQLID,
			],
		);
		unset(self::$CACHE_SECTOR_LOCATIONS[$gameID][$oldSectorID][$location->getTypeID()]);
		self::$CACHE_SECTOR_LOCATIONS[$gameID][$newSectorID][$location->getTypeID()] = $location;

		// Preserve the same element order that we'd have in getSectorLocations
		ksort(self::$CACHE_SECTOR_LOCATIONS[$gameID][$newSectorID]);
	}

	public static function removeSectorLocations(int $gameID, int $sectorID): void {
		$db = Database::getInstance();
		$db->delete('location', [
			'game_id' => $gameID,
			'sector_id' => $sectorID,
		]);
		self::$CACHE_SECTOR_LOCATIONS[$gameID][$sectorID] = [];
	}

	public static function getLocation(int $gameID, int $locationTypeID, bool $forceUpdate = false, ?DatabaseRecord $dbRecord = null): self {
		if ($forceUpdate || !isset(self::$CACHE_LOCATIONS[$locationTypeID])) {
			self::$CACHE_LOCATIONS[$locationTypeID] = new self($gameID, $locationTypeID, $dbRecord);
		}
		return self::$CACHE_LOCATIONS[$locationTypeID];
	}

	protected function __construct(
		protected readonly int $gameID, // use 0 to be independent of game
		protected readonly int $typeID,
		?DatabaseRecord $dbRecord = null,
	) {
		$db = Database::getInstance();
		$this->SQLID = ['location_type_id' => $db->escapeNumber($typeID)];

		if ($dbRecord === null) {
			$dbResult = $db->read('SELECT * FROM location_type WHERE ' . self::SQL, $this->SQLID);
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
		if ($this->isFed() && $this->getTypeID() !== LOCATION_TYPE_FEDERAL_BEACON) {
			return $this->getTypeID() - LOCATION_GROUP_RACIAL_BEACONS;
		}
		if ($this->isHQ() && $this->getTypeID() !== LOCATION_TYPE_FEDERAL_HQ) {
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
		$db = Database::getInstance();
		$db->update(
			'location_type',
			['location_name' => $this->name],
			$this->SQLID,
		);
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
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT 1 FROM location_is_fed WHERE ' . self::SQL, $this->SQLID);
			$this->fed = $dbResult->hasRecord();
		}
		return $this->fed;
	}

	public function setFed(bool $bool): void {
		if ($this->fed === $bool) {
			return;
		}
		$db = Database::getInstance();
		if ($bool) {
			$db->write('INSERT IGNORE INTO location_is_fed (location_type_id) values (:location_type_id)', $this->SQLID);
		} else {
			$db->delete('location_is_fed', $this->SQLID);
		}
		$this->fed = $bool;
	}

	public function isBank(): bool {
		if (!isset($this->bank)) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT 1 FROM location_is_bank WHERE ' . self::SQL, $this->SQLID);
			$this->bank = $dbResult->hasRecord();
		}
		return $this->bank;
	}

	public function setBank(bool $bool): void {
		if ($this->bank === $bool) {
			return;
		}
		$db = Database::getInstance();
		if ($bool) {
			$db->insert('location_is_bank', $this->SQLID);
		} else {
			$db->delete('location_is_bank', $this->SQLID);
		}
		$this->bank = $bool;
	}

	public function isBar(): bool {
		if (!isset($this->bar)) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT 1 FROM location_is_bar WHERE ' . self::SQL, $this->SQLID);
			$this->bar = $dbResult->hasRecord();
		}
		return $this->bar;
	}

	public function setBar(bool $bool): void {
		if ($this->bar === $bool) {
			return;
		}
		$db = Database::getInstance();
		if ($bool) {
			$db->write('INSERT IGNORE INTO location_is_bar (location_type_id) values (:location_type_id)', $this->SQLID);
		} else {
			$db->delete('location_is_bar', $this->SQLID);
		}
		$this->bar = $bool;
	}

	public function isHQ(): bool {
		if (!isset($this->HQ)) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT 1 FROM location_is_hq WHERE ' . self::SQL, $this->SQLID);
			$this->HQ = $dbResult->hasRecord();
		}
		return $this->HQ;
	}

	public function setHQ(bool $bool): void {
		if ($this->HQ === $bool) {
			return;
		}
		$db = Database::getInstance();
		if ($bool) {
			$db->write('INSERT IGNORE INTO location_is_hq (location_type_id) values (:location_type_id)', $this->SQLID);
		} else {
			$db->delete('location_is_hq', $this->SQLID);
		}
		$this->HQ = $bool;
	}

	public function isUG(): bool {
		if (!isset($this->UG)) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT 1 FROM location_is_ug WHERE ' . self::SQL, $this->SQLID);
			$this->UG = $dbResult->hasRecord();
		}
		return $this->UG;
	}

	public function setUG(bool $bool): void {
		if ($this->UG === $bool) {
			return;
		}
		$db = Database::getInstance();
		if ($bool) {
			$db->insert('location_is_ug', $this->SQLID);
		} else {
			$db->delete('location_is_ug', $this->SQLID);
		}
		$this->UG = $bool;
	}

	/**
	 * @return array<int, \Smr\HardwareType>
	 */
	public function getHardwareSold(): array {
		if (!isset($this->hardwareSold)) {
			$this->hardwareSold = [];
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT hardware_type_id FROM location_sells_hardware WHERE ' . self::SQL, $this->SQLID);
			foreach ($dbResult->records() as $dbRecord) {
				$hardwareTypeID = $dbRecord->getInt('hardware_type_id');
				$this->hardwareSold[$hardwareTypeID] = HardwareType::get($hardwareTypeID);
			}
		}
		return $this->hardwareSold;
	}

	public function isHardwareSold(?int $hardwareTypeID = null): bool {
		$hardware = $this->getHardwareSold();
		if ($hardwareTypeID === null) {
			return count($hardware) !== 0;
		}
		return isset($hardware[$hardwareTypeID]);
	}

	public function addHardwareSold(int $hardwareTypeID): void {
		if ($this->isHardwareSold($hardwareTypeID)) {
			return;
		}
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM hardware_type WHERE hardware_type_id = :hardware_type_id', [
			'hardware_type_id' => $db->escapeNumber($hardwareTypeID),
		]);
		if (!$dbResult->hasRecord()) {
			throw new Exception('Invalid hardware type id given');
		}
		$db->insert('location_sells_hardware', [
			...$this->SQLID,
			'hardware_type_id' => $hardwareTypeID,
		]);
		$this->hardwareSold[$hardwareTypeID] = HardwareType::get($hardwareTypeID);
	}

	public function removeHardwareSold(int $hardwareTypeID): void {
		if (!$this->isHardwareSold($hardwareTypeID)) {
			return;
		}
		$db = Database::getInstance();
		$db->delete('location_sells_hardware', [
			...$this->SQLID,
			'hardware_type_id' => $hardwareTypeID,
		]);
		unset($this->hardwareSold[$hardwareTypeID]);
	}

	/**
	 * @return array<int, ShipType>
	 */
	public function getShipsSold(): array {
		if (!isset($this->shipsSold)) {
			$this->shipsSold = [];
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM location_sells_ships JOIN ship_type USING (ship_type_id) WHERE ' . self::SQL, $this->SQLID);
			foreach ($dbResult->records() as $dbRecord) {
				$shipTypeID = $dbRecord->getInt('ship_type_id');
				$this->shipsSold[$shipTypeID] = ShipType::get($shipTypeID, $dbRecord);
			}

			if ($this->gameID > 0 && Game::getGame($this->gameID)->isGameType(Game::GAME_TYPE_HUNTER_WARS)) {
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

	public function isShipSold(?int $shipTypeID = null): bool {
		$ships = $this->getShipsSold();
		if ($shipTypeID === null) {
			return count($ships) !== 0;
		}
		return isset($ships[$shipTypeID]);
	}

	public function addShipSold(int $shipTypeID): void {
		if ($this->isShipSold($shipTypeID)) {
			return;
		}
		$ship = ShipType::get($shipTypeID);
		$db = Database::getInstance();
		$db->insert('location_sells_ships', [
			...$this->SQLID,
			'ship_type_id' => $shipTypeID,
		]);
		$this->shipsSold[$shipTypeID] = $ship;
	}

	public function removeShipSold(int $shipTypeID): void {
		if (!$this->isShipSold($shipTypeID)) {
			return;
		}
		$db = Database::getInstance();
		$db->delete('location_sells_ships', [
			...$this->SQLID,
			'ship_type_id' => $shipTypeID,
		]);
		unset($this->shipsSold[$shipTypeID]);
	}

	/**
	 * @return array<int, Weapon>
	 */
	public function getWeaponsSold(): array {
		if (!isset($this->weaponsSold)) {
			$this->weaponsSold = [];
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM location_sells_weapons JOIN weapon_type USING (weapon_type_id) WHERE ' . self::SQL, $this->SQLID);
			foreach ($dbResult->records() as $dbRecord) {
				$weaponTypeID = $dbRecord->getInt('weapon_type_id');
				$this->weaponsSold[$weaponTypeID] = Weapon::getWeapon($weaponTypeID, $dbRecord);
			}
		}
		return $this->weaponsSold;
	}

	public function isWeaponSold(?int $weaponTypeID = null): bool {
		$weapons = $this->getWeaponsSold();
		if ($weaponTypeID === null) {
			return count($weapons) !== 0;
		}
		return isset($weapons[$weaponTypeID]);
	}

	public function addWeaponSold(int $weaponTypeID): void {
		if ($this->isWeaponSold($weaponTypeID)) {
			return;
		}
		$weapon = Weapon::getWeapon($weaponTypeID);
		$db = Database::getInstance();
		$db->insert('location_sells_weapons', [
			...$this->SQLID,
			'weapon_type_id' => $weaponTypeID,
		]);
		$this->weaponsSold[$weaponTypeID] = $weapon;
	}

	public function removeWeaponSold(int $weaponTypeID): void {
		if (!$this->isWeaponSold($weaponTypeID)) {
			return;
		}
		$db = Database::getInstance();
		$db->delete('location_sells_weapons', [
			...$this->SQLID,
			'weapon_type_id' => $weaponTypeID,
		]);
		unset($this->weaponsSold[$weaponTypeID]);
	}

	/**
	 * @return array<self>
	 */
	public function getLinkedLocations(): array {
		$linkedLocations = [];
		if ($this->isHQ()) {
			if ($this->getTypeID() === LOCATION_TYPE_FEDERAL_HQ) {
				$linkedLocations[] = self::getLocation($this->gameID, LOCATION_TYPE_FEDERAL_BEACON);
				$linkedLocations[] = self::getLocation($this->gameID, LOCATION_TYPE_FEDERAL_MINT);
			} else {
				$raceID = $this->getRaceID();
				$linkedLocations[] = self::getLocation($this->gameID, LOCATION_GROUP_RACIAL_BEACONS + $raceID);
				$linkedLocations[] = self::getLocation($this->gameID, LOCATION_GROUP_RACIAL_SHIPS + $raceID);
				$linkedLocations[] = self::getLocation($this->gameID, LOCATION_GROUP_RACIAL_SHOPS + $raceID);
			}
		}
		return $linkedLocations;
	}

	public function getExamineHREF(): string {
		$action = $this->processor;
		$container = match ($action) {
			'shop_hardware.php' => new ShopHardware($this->typeID),
			'shop_ship.php' => new ShopShip($this->typeID),
			'shop_weapon.php' => new ShopWeapon($this->typeID),
			'government.php' => new Government($this->typeID),
			'underground.php' => new Underground($this->typeID),
			'bank_personal.php' => new PersonalBank(),
			'bar_main.php' => new BarMain($this->typeID),
			default => throw new Exception('Unknown action: ' . $action),
		};
		return $container->href();
	}

	public function getEditHREF(): string {
		$container = new EditLocations($this->getTypeID());
		return $container->href();
	}

	public function equals(Location $otherLocation): bool {
		return $this->getTypeID() === $otherLocation->getTypeID();
	}

	public function hasX(mixed $x, ?AbstractPlayer $player = null): bool {
		if ($x instanceof WeaponType) {
			return $this->isWeaponSold($x->getWeaponTypeID());
		}
		if ($x instanceof ShipType) {
			return $this->isShipSold($x->getTypeID());
		}
		if ($x instanceof HardwareType) {
			return $this->isHardwareSold($x->typeID);
		}
		if (is_string($x)) {
			if ($x === 'Bank') {
				return $this->isBank();
			}
			if ($x === 'Bar') {
				return $this->isBar();
			}
			if ($x === 'Fed') {
				return $this->isFed();
			}
			if ($x === 'SafeFed') {
				return $player !== null && $this->isFed() && $player->canBeProtectedByRace($this->getRaceID());
			}
			if ($x === 'HQ') {
				return $this->isHQ();
			}
			if ($x === 'UG') {
				return $this->isUG();
			}
			if ($x === 'Hardware') {
				return $this->isHardwareSold();
			}
			if ($x === 'Ship') {
				return $this->isShipSold();
			}
			if ($x === 'Weapon') {
				return $this->isWeaponSold();
			}
		}
		return false;
	}

}
