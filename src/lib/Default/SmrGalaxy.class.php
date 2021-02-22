<?php declare(strict_types=1);
class SmrGalaxy {
	protected static $CACHE_GALAXIES = array();
	protected static $CACHE_GAME_GALAXIES = array();

	const TYPES = ['Racial', 'Neutral', 'Planet'];

	protected MySqlDatabase $db;
	protected string $SQL;

	protected int $gameID;
	protected int $galaxyID;
	protected string $name;
	protected int $width;
	protected int $height;
	protected string $galaxyType;
	protected int $maxForceTime;

	protected int $startSector;

	protected bool $hasChanged = false;
	protected bool $isNew = false;

	public static function getGameGalaxies(int $gameID, bool $forceUpdate = false) : array {
		if ($forceUpdate || !isset(self::$CACHE_GAME_GALAXIES[$gameID])) {
			$db = MySqlDatabase::getInstance();
			$db->query('SELECT * FROM game_galaxy WHERE game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY galaxy_id ASC');
			$galaxies = array();
			while ($db->nextRecord()) {
				$galaxyID = $db->getInt('galaxy_id');
				$galaxies[$galaxyID] = self::getGalaxy($gameID, $galaxyID, $forceUpdate, $db);
			}
			self::$CACHE_GAME_GALAXIES[$gameID] = $galaxies;
		}
		return self::$CACHE_GAME_GALAXIES[$gameID];
	}

	public static function getGalaxy(int $gameID, int $galaxyID, bool $forceUpdate = false, MySqlDatabase $db = null) : SmrGalaxy {
		if ($forceUpdate || !isset(self::$CACHE_GALAXIES[$gameID][$galaxyID])) {
			$g = new SmrGalaxy($gameID, $galaxyID, false, $db);
			self::$CACHE_GALAXIES[$gameID][$galaxyID] = $g;
		}
		return self::$CACHE_GALAXIES[$gameID][$galaxyID];
	}

	public static function saveGalaxies() : void {
		foreach (self::$CACHE_GALAXIES as $gameGalaxies) {
			foreach ($gameGalaxies as $galaxy) {
				$galaxy->save();
			}
		}
	}

	public static function createGalaxy(int $gameID, int $galaxyID) : SmrGalaxy {
		if (!isset(self::$CACHE_GALAXIES[$gameID][$galaxyID])) {
			$g = new SmrGalaxy($gameID, $galaxyID, true);
			self::$CACHE_GALAXIES[$gameID][$galaxyID] = $g;
		}
		return self::$CACHE_GALAXIES[$gameID][$galaxyID];
	}

	protected function __construct(int $gameID, int $galaxyID, bool $create = false, MySqlDatabase $db = null) {
		$this->db = MySqlDatabase::getInstance();
		$this->SQL = 'game_id = ' . $this->db->escapeNumber($gameID) . '
		              AND galaxy_id = ' . $this->db->escapeNumber($galaxyID);

		if (isset($db)) {
			$this->isNew = false;
		} else {
			$db = $this->db;
			$db->query('SELECT * FROM game_galaxy WHERE ' . $this->SQL);
			$this->isNew = !$db->nextRecord();
		}

		$this->gameID = $gameID;
		$this->galaxyID = $galaxyID;
		if (!$this->isNew) {
			$this->name = $db->getField('galaxy_name');
			$this->width = $db->getInt('width');
			$this->height = $db->getInt('height');
			$this->galaxyType = $db->getField('galaxy_type');
			$this->maxForceTime = $db->getInt('max_force_time');
		} elseif ($create === false) {
			throw new Exception('No such galaxy: ' . $gameID . '-' . $galaxyID);
		}
	}

	public function save() : void {
		if ($this->isNew) {
				$this->db->query('INSERT INTO game_galaxy (game_id,galaxy_id,galaxy_name,width,height,galaxy_type,max_force_time)
									values
									(' . $this->db->escapeNumber($this->getGameID()) .
									',' . $this->db->escapeNumber($this->getGalaxyID()) .
									',' . $this->db->escapeString($this->getName()) .
									',' . $this->db->escapeNumber($this->getWidth()) .
									',' . $this->db->escapeNumber($this->getHeight()) .
									',' . $this->db->escapeString($this->getGalaxyType()) .
									',' . $this->db->escapeNumber($this->getMaxForceTime()) . ')');
		} elseif ($this->hasChanged) {
				$this->db->query('UPDATE game_galaxy SET galaxy_name = ' . $this->db->escapeString($this->getName()) .
										', width = ' . $this->db->escapeNumber($this->getWidth()) .
										', height = ' . $this->db->escapeNumber($this->getHeight()) .
										', galaxy_type = ' . $this->db->escapeString($this->getGalaxyType()) .
										', max_force_time = ' . $this->db->escapeNumber($this->getMaxForceTime()) .
									' WHERE ' . $this->SQL);
		}
		$this->isNew = false;
		$this->hasChanged = false;
	}

	public function getGameID() : int {
		return $this->gameID;
	}

	public function getGalaxyID() : int {
		return $this->galaxyID;
	}

	public function getGalaxyMapHREF() : string {
		return 'map_galaxy.php?galaxy_id=' . $this->getGalaxyID();
	}

	/**
	 * Returns the galaxy name.
	 * Use getDisplayName for an HTML-safe version.
	 */
	public function getName() : string {
		return $this->name;
	}

	/**
	 * Returns the galaxy name, suitable for HTML display.
	 */
	public function getDisplayName() : string {
		return htmlentities($this->name);
	}

	public function setName(string $name) : void {
		if (!$this->isNew && $this->name === $name) {
			return;
		}
		$this->name = $name;
		$this->hasChanged = true;
	}

	public function getWidth() : int {
		return $this->width;
	}

	public function setWidth(int $width) : void {
		if (!$this->isNew && $this->width === $width) {
			return;
		}
		$this->width = $width;
		$this->hasChanged = true;
	}

	public function getHeight() : int {
		return $this->height;
	}

	public function setHeight(int $height) : void {
		if (!$this->isNew && $this->height === $height) {
			return;
		}
		$this->height = $height;
		$this->hasChanged = true;
	}

	public function getStartSector() : int {
		if (!isset($this->startSector)) {
			$this->startSector = 1;
			if ($this->getGalaxyID() != 1) {
				$galaxies = SmrGalaxy::getGameGalaxies($this->getGameID());
				for ($i = 1; $i < $this->getGalaxyID(); $i++) {
					$this->startSector += $galaxies[$i]->getSize();
				}
			}
		}
		return $this->startSector;
	}

	public function getEndSector() : int {
		return $this->getStartSector() + $this->getSize() - 1;
	}

	public function getSize() : int {
		return $this->getHeight() * $this->getWidth();
	}

	public function getSectors() : array {
		return SmrSector::getGalaxySectors($this->getGameID(), $this->getGalaxyID());
	}

	public function getPorts() : array {
		return SmrPort::getGalaxyPorts($this->getGameID(), $this->getGalaxyID());
	}

	public function getLocations() : array {
		return SmrLocation::getGalaxyLocations($this->getGameID(), $this->getGalaxyID());
	}

	public function getPlanets() : array {
		return SmrPlanet::getGalaxyPlanets($this->getGameID(), $this->getGalaxyID());
	}

	public function getForces() : array {
		return SmrForce::getGalaxyForces($this->getGameID(), $this->getGalaxyID());
	}

	public function getPlayers() : array {
		return SmrPlayer::getGalaxyPlayers($this->getGameID(), $this->getGalaxyID());
	}

	/**
	 * Returns a 2D array of sectors in the galaxy.
	 * If $centerSectorID is specified, it will be in the center of the array.
	 * If $dist is also specified, only include sectors $dist away from center.
	 *
	 * NOTE: This routine queries sectors inefficiently. You may want to
	 * construct the cache efficiently before calling this.
	 */
	public function getMapSectors(int $centerSectorID = null, int $dist = null) : array {
		if (is_null($centerSectorID)) {
			$topLeft = SmrSector::getSector($this->getGameID(), $this->getStartSector());
		} else {
			$topLeft = SmrSector::getSector($this->getGameID(), $centerSectorID);
			// go left then up
			for ($i = 0; (is_null($dist) || $i < $dist) && $i < floor($this->getWidth() / 2); $i++) {
				$topLeft = $topLeft->getNeighbourSector('Left');
			}
			for ($i = 0; (is_null($dist) || $i < $dist) && $i < floor($this->getHeight() / 2); $i++) {
				$topLeft = $topLeft->getNeighbourSector('Up');
			}
		}

		$mapSectors = array();
		for ($i = 0; (is_null($dist) || $i < 2 * $dist + 1) && $i < $this->getHeight(); $i++) {
			$mapSectors[$i] = array();
			// get left most sector for this row
			$rowLeft = $i == 0 ? $topLeft : $rowLeft->getNeighbourSector('Down');

			// iterate through the columns
			for ($j = 0; (is_null($dist) || $j < 2 * $dist + 1) && $j < $this->getWidth(); $j++) {
				$nextSec = $j == 0 ? $rowLeft : $nextSec->getNeighbourSector('Right');
				$mapSectors[$i][$j] = $nextSec;
			}
		}
		return $mapSectors;
	}

	public function getGalaxyType() : string {
		return $this->galaxyType;
	}

	public function setGalaxyType(string $galaxyType) : void {
		if (!$this->isNew && $this->galaxyType === $galaxyType) {
			return;
		}
		$this->galaxyType = $galaxyType;
		$this->hasChanged = true;
	}

	public function getMaxForceTime() : int {
		return $this->maxForceTime;
	}

	public function setMaxForceTime(int $maxForceTime) : void {
		if (!$this->isNew && $this->maxForceTime === $maxForceTime) {
			return;
		}
		$this->maxForceTime = $maxForceTime;
		$this->hasChanged = true;
	}

	public function generateSectors() : void {
		$sectorID = $this->getStartSector();
		for ($i = 0; $i < $this->getSize(); $i++) {
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
	public function setConnectivity(float $connectivity) : bool {
		// Only set down/right, otherwise we double-hit every link
		$linkDirs = array('Down', 'Right');

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
	public function getConnectivity() : float {
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
	public function contains(int | SmrSector $sectorID) : bool {
		if ($sectorID instanceof SmrSector) {
			return $sectorID->getGalaxyID() == $this->getGalaxyID();
		}
		return $sectorID >= $this->getStartSector() && $sectorID <= $this->getEndSector();
	}

	public static function getGalaxyContaining(int $gameID, int $sectorID) : SmrGalaxy {
		return SmrSector::getSector($gameID, $sectorID)->getGalaxy();
	}

	public function equals(SmrGalaxy $otherGalaxy) : bool {
		return $otherGalaxy->getGalaxyID() == $this->getGalaxyID() && $otherGalaxy->getGameID() == $this->getGameID();
	}
}
