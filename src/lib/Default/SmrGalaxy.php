<?php declare(strict_types=1);
class SmrGalaxy {

	/** @var array<int, array<int, self>> */
	protected static array $CACHE_GALAXIES = [];
	/** @var array<int, array<int, self>> */
	protected static array $CACHE_GAME_GALAXIES = [];

	public const TYPE_RACIAL = 'Racial';
	public const TYPE_NEUTRAL = 'Neutral';
	public const TYPE_PLANET = 'Planet';
	public const TYPES = [self::TYPE_RACIAL, self::TYPE_NEUTRAL, self::TYPE_PLANET];

	protected Smr\Database $db;
	protected readonly string $SQL;

	protected string $name;
	protected int $width;
	protected int $height;
	protected string $galaxyType;
	protected int $maxForceTime;

	protected int $startSector;

	protected bool $hasChanged = false;
	protected bool $isNew;

	public static function clearCache(): void {
		self::$CACHE_GALAXIES = [];
		self::$CACHE_GAME_GALAXIES = [];
	}

	/**
	 * @return array<int, self>
	 */
	public static function getGameGalaxies(int $gameID, bool $forceUpdate = false): array {
		if ($forceUpdate || !isset(self::$CACHE_GAME_GALAXIES[$gameID])) {
			$db = Smr\Database::getInstance();
			$dbResult = $db->read('SELECT * FROM game_galaxy WHERE game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY galaxy_id ASC');
			$galaxies = [];
			foreach ($dbResult->records() as $dbRecord) {
				$galaxyID = $dbRecord->getInt('galaxy_id');
				$galaxies[$galaxyID] = self::getGalaxy($gameID, $galaxyID, $forceUpdate, $dbRecord);
			}
			self::$CACHE_GAME_GALAXIES[$gameID] = $galaxies;
		}
		return self::$CACHE_GAME_GALAXIES[$gameID];
	}

	public static function getGalaxy(int $gameID, int $galaxyID, bool $forceUpdate = false, Smr\DatabaseRecord $dbRecord = null): self {
		if ($forceUpdate || !isset(self::$CACHE_GALAXIES[$gameID][$galaxyID])) {
			$g = new self($gameID, $galaxyID, false, $dbRecord);
			self::$CACHE_GALAXIES[$gameID][$galaxyID] = $g;
		}
		return self::$CACHE_GALAXIES[$gameID][$galaxyID];
	}

	public static function saveGalaxies(): void {
		foreach (self::$CACHE_GALAXIES as $gameGalaxies) {
			foreach ($gameGalaxies as $galaxy) {
				$galaxy->save();
			}
		}
	}

	public static function createGalaxy(int $gameID, int $galaxyID): self {
		if (!isset(self::$CACHE_GALAXIES[$gameID][$galaxyID])) {
			$g = new self($gameID, $galaxyID, true);
			self::$CACHE_GALAXIES[$gameID][$galaxyID] = $g;
		}
		return self::$CACHE_GALAXIES[$gameID][$galaxyID];
	}

	protected function __construct(
		protected readonly int $gameID,
		protected readonly int $galaxyID,
		bool $create = false,
		Smr\DatabaseRecord $dbRecord = null
	) {
		$this->db = Smr\Database::getInstance();
		$this->SQL = 'game_id = ' . $this->db->escapeNumber($gameID) . '
		              AND galaxy_id = ' . $this->db->escapeNumber($galaxyID);

		if ($dbRecord === null) {
			$dbResult = $this->db->read('SELECT * FROM game_galaxy WHERE ' . $this->SQL);
			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
			}
		}
		$this->isNew = $dbRecord === null;

		if (!$this->isNew) {
			$this->name = $dbRecord->getString('galaxy_name');
			$this->width = $dbRecord->getInt('width');
			$this->height = $dbRecord->getInt('height');
			$this->galaxyType = $dbRecord->getString('galaxy_type');
			$this->maxForceTime = $dbRecord->getInt('max_force_time');
		} elseif ($create === false) {
			throw new Smr\Exceptions\GalaxyNotFound('No such galaxy: ' . $gameID . '-' . $galaxyID);
		}
	}

	public function save(): void {
		if ($this->isNew) {
			$this->db->insert('game_galaxy', [
				'game_id' => $this->db->escapeNumber($this->getGameID()),
				'galaxy_id' => $this->db->escapeNumber($this->getGalaxyID()),
				'galaxy_name' => $this->db->escapeString($this->getName()),
				'width' => $this->db->escapeNumber($this->getWidth()),
				'height' => $this->db->escapeNumber($this->getHeight()),
				'galaxy_type' => $this->db->escapeString($this->getGalaxyType()),
				'max_force_time' => $this->db->escapeNumber($this->getMaxForceTime()),
			]);
		} elseif ($this->hasChanged) {
			$this->db->write('UPDATE game_galaxy SET galaxy_name = ' . $this->db->escapeString($this->getName()) .
										', width = ' . $this->db->escapeNumber($this->getWidth()) .
										', height = ' . $this->db->escapeNumber($this->getHeight()) .
										', galaxy_type = ' . $this->db->escapeString($this->getGalaxyType()) .
										', max_force_time = ' . $this->db->escapeNumber($this->getMaxForceTime()) .
									' WHERE ' . $this->SQL);
		}
		$this->isNew = false;
		$this->hasChanged = false;
	}

	public function getGameID(): int {
		return $this->gameID;
	}

	public function getGalaxyID(): int {
		return $this->galaxyID;
	}

	public function getGalaxyMapHREF(): string {
		return 'map_galaxy.php?galaxy_id=' . $this->getGalaxyID();
	}

	/**
	 * Returns the galaxy name.
	 * Use getDisplayName for an HTML-safe version.
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Returns the galaxy name, suitable for HTML display.
	 */
	public function getDisplayName(): string {
		return htmlentities($this->name);
	}

	public function setName(string $name): void {
		if (!$this->isNew && $this->name === $name) {
			return;
		}
		$this->name = $name;
		$this->hasChanged = true;
	}

	public function getWidth(): int {
		return $this->width;
	}

	public function setWidth(int $width): void {
		if (!$this->isNew && $this->width === $width) {
			return;
		}
		$this->width = $width;
		$this->hasChanged = true;
	}

	public function getHeight(): int {
		return $this->height;
	}

	public function setHeight(int $height): void {
		if (!$this->isNew && $this->height === $height) {
			return;
		}
		$this->height = $height;
		$this->hasChanged = true;
	}

	public function getStartSector(): int {
		if (!isset($this->startSector)) {
			$this->startSector = 1;
			if ($this->galaxyID != 1) {
				$galaxies = self::getGameGalaxies($this->gameID);
				for ($i = 1; $i < $this->galaxyID; $i++) {
					$this->startSector += $galaxies[$i]->getSize();
				}
			}
		}
		return $this->startSector;
	}

	public function getEndSector(): int {
		return $this->getStartSector() + $this->getSize() - 1;
	}

	public function getSize(): int {
		return $this->getHeight() * $this->getWidth();
	}

	/**
	 * @return array<int, SmrSector>
	 */
	public function getSectors(): array {
		return SmrSector::getGalaxySectors($this->getGameID(), $this->getGalaxyID());
	}

	/**
	 * @return array<int, SmrPort>
	 */
	public function getPorts(): array {
		return SmrPort::getGalaxyPorts($this->getGameID(), $this->getGalaxyID());
	}

	/**
	 * @return array<int, array<int, SmrLocation>>
	 */
	public function getLocations(): array {
		return SmrLocation::getGalaxyLocations($this->getGameID(), $this->getGalaxyID());
	}

	/**
	 * @return array<int, SmrPlanet>
	 */
	public function getPlanets(): array {
		return SmrPlanet::getGalaxyPlanets($this->getGameID(), $this->getGalaxyID());
	}

	/**
	 * @return array<int, array<int, SmrForce>>
	 */
	public function getForces(): array {
		return SmrForce::getGalaxyForces($this->getGameID(), $this->getGalaxyID());
	}

	/**
	 * @return array<int, array<int, SmrPlayer>>
	 */
	public function getPlayers(): array {
		return SmrPlayer::getGalaxyPlayers($this->getGameID(), $this->getGalaxyID());
	}

	/**
	 * Returns a 2D array of sectors in the galaxy.
	 * If $centerSectorID is specified, it will be in the center of the array.
	 * If $dist is also specified, only include sectors $dist away from center.
	 *
	 * NOTE: This routine queries sectors inefficiently. You may want to
	 * construct the cache efficiently before calling this.
	 *
	 * @return array<int, array<int, SmrSector>>
	 */
	public function getMapSectors(int $centerSectorID = null, int $dist = null): array {
		if ($centerSectorID === null) {
			$topLeft = SmrSector::getSector($this->getGameID(), $this->getStartSector());
		} else {
			$topLeft = SmrSector::getSector($this->getGameID(), $centerSectorID);
			// go left then up
			$halfWidth = floor($this->width / 2);
			for ($i = 0; ($dist === null || $i < $dist) && $i < $halfWidth; $i++) {
				$topLeft = $topLeft->getNeighbourSector('Left');
			}
			$halfHeight = floor($this->height / 2);
			for ($i = 0; ($dist === null || $i < $dist) && $i < $halfHeight; $i++) {
				$topLeft = $topLeft->getNeighbourSector('Up');
			}
		}

		$mapSectors = [];
		$rowLeft = $topLeft;
		for ($i = 0; ($dist === null || $i < 2 * $dist + 1) && $i < $this->height; $i++) {
			$mapSectors[$i] = [];
			// get left most sector for this row
			if ($i > 0) {
				$rowLeft = $rowLeft->getNeighbourSector('Down');
			}

			// iterate through the columns
			$nextSector = $rowLeft;
			for ($j = 0; ($dist === null || $j < 2 * $dist + 1) && $j < $this->width; $j++) {
				if ($j > 0) {
					$nextSector = $nextSector->getNeighbourSector('Right');
				}
				$mapSectors[$i][$j] = $nextSector;
			}
		}
		return $mapSectors;
	}

	public function getGalaxyType(): string {
		return $this->galaxyType;
	}

	public function setGalaxyType(string $galaxyType): void {
		if (!$this->isNew && $this->galaxyType === $galaxyType) {
			return;
		}
		$this->galaxyType = $galaxyType;
		$this->hasChanged = true;
	}

	public function getMaxForceTime(): int {
		return $this->maxForceTime;
	}

	public function setMaxForceTime(int $maxForceTime): void {
		if (!$this->isNew && $this->maxForceTime === $maxForceTime) {
			return;
		}
		$this->maxForceTime = $maxForceTime;
		$this->hasChanged = true;
	}

	public function generateSectors(): void {
		$sectorID = $this->getStartSector();
		$galSize = $this->getSize();
		for ($i = 0; $i < $galSize; $i++) {
			$sector = SmrSector::createSector($this->gameID, $sectorID);
			$sector->setGalaxyID($this->getGalaxyID());
			$sector->update(); //Have to save sectors after creating them
			$sectorID++;
		}
		$this->setConnectivity(100);
	}

	/**
	 * Randomly set the connections between all galaxy sectors.
	 * $connectivity = (average) percent of connections to enable.
	 */
	public function setConnectivity(float $connectivity): bool {
		// Only set down/right, otherwise we double-hit every link
		$linkDirs = ['Down', 'Right'];

		$problem = true;
		$problemTimes = 0;
		while ($problem) {
			$problem = false;

			foreach ($this->getSectors() as $galSector) {
				foreach ($linkDirs as $linkDir) {
					if (rand(1, 100) <= $connectivity) {
						$galSector->enableLink($linkDir);
					} else {
						$galSector->disableLink($linkDir);
					}
				}
			}

			// Try again if any sector has 0 connections (except 1-sector gals)
			if ($this->getSize() > 1) {
				foreach ($this->getSectors() as $galSector) {
					if ($galSector->getNumberOfConnections() == 0) {
						$problem = true;
						break;
					}
				}
			}

			if ($problem && $problemTimes++ > 350) {
				$connectivity = 100;
			}
		}
		return $problemTimes <= 350;
	}

	/**
	 * Returns the sector connectivity of the galaxy as a percent.
	 */
	public function getConnectivity(): float {
		$totalLinks = 0;
		foreach ($this->getSectors() as $galSector) {
			$totalLinks += $galSector->getNumberOfLinks();
		}
		// There are 4 possible links per sector
		$connectivity = 100 * $totalLinks / (4 * $this->getSize());
		return $connectivity;
	}

	/**
	 * Check if the galaxy contains a specific sector.
	 */
	public function contains(int|SmrSector $sectorID): bool {
		if ($sectorID instanceof SmrSector) {
			return $sectorID->getGalaxyID() == $this->getGalaxyID();
		}
		return $sectorID >= $this->getStartSector() && $sectorID <= $this->getEndSector();
	}

	public static function getGalaxyContaining(int $gameID, int $sectorID): self {
		return SmrSector::getSector($gameID, $sectorID)->getGalaxy();
	}

	public function equals(SmrGalaxy $otherGalaxy): bool {
		return $otherGalaxy->getGalaxyID() == $this->getGalaxyID() && $otherGalaxy->getGameID() == $this->getGameID();
	}

}
