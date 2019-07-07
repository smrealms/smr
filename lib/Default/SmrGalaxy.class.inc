<?php
class SmrGalaxy {
	protected static $CACHE_GALAXIES = array();
	protected static $CACHE_GAME_GALAXIES = array();
	
	protected $db;
	protected $SQL;

	protected $gameID;
	protected $galaxyID;
	protected $name;
	protected $width;
	protected $height;
	protected $galaxyType;
	protected $maxForceTime;
	
	protected $startSector = false;
	
	protected $hasChanged = false;
	protected $isNew = false;

	public static function &getGameGalaxies($gameID, $forceUpdate = false) {
		if ($forceUpdate || !isset(self::$CACHE_GAME_GALAXIES[$gameID])) {
			$db = new SmrMySqlDatabase();
			$db->query('SELECT * FROM game_galaxy WHERE game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY galaxy_id ASC');
			$galaxies = array();
			while ($db->nextRecord()) {
				$galaxyID = $db->getField('galaxy_id');
				$galaxies[$galaxyID] = self::getGalaxy($gameID, $galaxyID, $forceUpdate, $db);
			}
			self::$CACHE_GAME_GALAXIES[$gameID] = $galaxies;
		}
		return self::$CACHE_GAME_GALAXIES[$gameID];
	}

	public static function &getGalaxy($gameID, $galaxyID, $forceUpdate = false, $db = null) {
		if ($forceUpdate || !isset(self::$CACHE_GALAXIES[$gameID][$galaxyID])) {
			$g = new SmrGalaxy($gameID, $galaxyID, false, $db);
			self::$CACHE_GALAXIES[$gameID][$galaxyID] = $g;
		}
		return self::$CACHE_GALAXIES[$gameID][$galaxyID];
	}
	
	public static function saveGalaxies() {
		foreach (self::$CACHE_GALAXIES as $gameGalaxies) {
			foreach ($gameGalaxies as $galaxy) {
				$galaxy->save();
			}
		}
	}
	
	public static function &createGalaxy($gameID, $galaxyID) {
		if (!isset(self::$CACHE_GALAXIES[$gameID][$galaxyID])) {
			$g = new SmrGalaxy($gameID, $galaxyID, true);
			self::$CACHE_GALAXIES[$gameID][$galaxyID] = $g;
		}
		return self::$CACHE_GALAXIES[$gameID][$galaxyID];
	}
	
	protected function __construct($gameID, $galaxyID, $create = false, $db = null) {
		$this->db = new SmrMySqlDatabase();
		$this->SQL = 'game_id = ' . $this->db->escapeNumber($gameID) . '
		              AND galaxy_id = ' . $this->db->escapeNumber($galaxyID);

		if (isset($db)) {
			$this->isNew = false;
		} else {
			$db = $this->db;
			$db->query('SELECT * FROM game_galaxy WHERE ' . $this->SQL);
			$this->isNew = !$db->nextRecord();
		}

		$this->gameID = (int)$gameID;
		$this->galaxyID = (int)$galaxyID;
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

	public function save() {
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
	
	public function getGameID() {
		return $this->gameID;
	}
	
	public function getGalaxyID() {
		return $this->galaxyID;
	}
	
	public function getGalaxyMapHREF() {
		return 'map_galaxy.php?galaxy_id=' . $this->getGalaxyID();
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$name = htmlentities($name, ENT_COMPAT, 'utf-8');
		if ($this->name == $name) {
			return;
		}
		$this->name = $name;
		$this->hasChanged = true;
	}
	
	public function getWidth() {
		return $this->width;
	}
	
	public function setWidth($width) {
		if ($this->width == $width) {
			return;
		}
		$this->width = $width;
		$this->hasChanged = true;
	}
	
	public function getHeight() {
		return $this->height;
	}
	
	public function setHeight($height) {
		if ($this->height == $height) {
			return;
		}
		$this->height = $height;
		$this->hasChanged = true;
	}
	
	public function getStartSector() {
		if ($this->startSector === false) {
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
	
	public function getEndSector() {
		return $this->getStartSector() + $this->getSize() - 1;
	}
	
	public function getSize() {
		return $this->getHeight() * $this->getWidth();
	}
	
	public function &getSectors() {
		return SmrSector::getGalaxySectors($this->getGameID(), $this->getGalaxyID());
	}

	public function getPorts() {
		return SmrPort::getGalaxyPorts($this->getGameID(), $this->getGalaxyID());
	}

	public function getLocations() {
		return SmrLocation::getGalaxyLocations($this->getGameID(), $this->getGalaxyID());
	}

	public function getPlanets() {
		return SmrPlanet::getGalaxyPlanets($this->getGameID(), $this->getGalaxyID());
	}

	public function getForces() {
		return SmrForce::getGalaxyForces($this->getGameID(), $this->getGalaxyID());
	}

	public function getPlayers() {
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
	public function getMapSectors($centerSectorID = false, $dist = false) {
		if ($centerSectorID === false) {
			$topLeft = SmrSector::getSector($this->getGameID(), $this->getStartSector());
		} else {
			$topLeft = SmrSector::getSector($this->getGameID(), $centerSectorID);
			// go left then up
			for ($i = 0; ($dist === false || $i < $dist) && $i < floor($this->getWidth() / 2); $i++) {
				$topLeft = $topLeft->getNeighbourSector('Left');
			}
			for ($i = 0; ($dist === false || $i < $dist) && $i < floor($this->getHeight() / 2); $i++) {
				$topLeft = $topLeft->getNeighbourSector('Up');
			}
		}

		$mapSectors = array();
		for ($i = 0; ($dist === false || $i < 2 * $dist + 1) && $i < $this->getHeight(); $i++) {
			$mapSectors[$i] = array();
			// get left most sector for this row
			$rowLeft = $i == 0 ? $topLeft : $rowLeft->getNeighbourSector('Down');

			// iterate through the columns
			for ($j = 0; ($dist === false || $j < 2 * $dist + 1) && $j < $this->getWidth(); $j++) {
				$nextSec = $j == 0 ? $rowLeft : $nextSec->getNeighbourSector('Right');
				$mapSectors[$i][$j] = $nextSec;
			}
		}
		return $mapSectors;
	}

	public function getGalaxyType() {
		return $this->galaxyType;
	}
	
	public function setGalaxyType($galaxyType) {
		if ($this->galaxyType == $galaxyType) {
			return;
		}
		$this->galaxyType = $galaxyType;
		$this->hasChanged = true;
	}
	
	public function getMaxForceTime() {
		return $this->maxForceTime;
	}
	
	public function setMaxForceTime($maxForceTime) {
		if ($this->maxForceTime == $maxForceTime) {
			return;
		}
		$this->maxForceTime = $maxForceTime;
		$this->hasChanged = true;
	}
	
	public function generateSectors() {
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
	public function setConnectivity($connectivity) {
		// Only set down/right, otherwise we double-hit every link
		$linkDirs = array('Down', 'Right');

		$problem = true;
		$problemTimes = 0;
		while ($problem) {
			$problem = false;
		
			foreach ($this->getSectors() as $galSector) {
				foreach ($linkDirs as $linkDir) {
					if (mt_rand(1, 100) <= $connectivity) {
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
	public function getConnectivity() {
		$totalLinks = 0;
		foreach ($this->getSectors() as $galSector) {
			$totalLinks += $galSector->getNumberOfLinks();
		}
		// There are 4 possible links per sector
		$connectivity = 100 * $totalLinks / (4 * $this->getSize());
		return $connectivity;
	}

	public function contains($sectorID) {
		if ($sectorID instanceof SmrSector) {
			return $sectorID->getGalaxyID() == $this->getGalaxyID();
		}
		return $sectorID >= $this->getStartSector() && $sectorID <= $this->getEndSector();
	}
	
	public static function &getGalaxyContaining($gameID, $sectorID) {
		return SmrSector::getSector($gameID, $sectorID)->getGalaxy();
	}
	
	public function equals(SmrGalaxy $otherGalaxy) {
		return $otherGalaxy->getGalaxyID() == $this->getGalaxyID() && $otherGalaxy->getGameID() == $this->getGameID();
	}
}
