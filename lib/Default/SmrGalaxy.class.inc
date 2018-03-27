<?php
class SmrGalaxy {
	protected static $CACHE_GALAXIES = array();
	protected static $CACHE_GAME_GALAXIES = array();
	
	protected $db;

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

	public static function &getGameGalaxies($gameID,$forceUpdate = false) {
		if($forceUpdate || !isset(self::$CACHE_GAME_GALAXIES[$gameID])) {
			$db = new SmrMySqlDatabase();
			$db->query('SELECT galaxy_id FROM game_galaxy WHERE game_id = ' . $db->escapeNumber($gameID) .' ORDER BY galaxy_id ASC');
			$galaxies = array();
			while($db->nextRecord()) {
				$galaxyID = $db->getField('galaxy_id');
				$galaxies[$galaxyID] = self::getGalaxy($gameID,$galaxyID,$forceUpdate);
			}
			self::$CACHE_GAME_GALAXIES[$gameID] = $galaxies;
		}
		return self::$CACHE_GAME_GALAXIES[$gameID];
	}

	public static function &getGalaxy($gameID,$galaxyID,$forceUpdate = false) {
		if($forceUpdate || !isset(self::$CACHE_GALAXIES[$gameID][$galaxyID])) {
			$g = new SmrGalaxy($gameID,$galaxyID);
			self::$CACHE_GALAXIES[$gameID][$galaxyID] = $g;
		}
		return self::$CACHE_GALAXIES[$gameID][$galaxyID];
	}
	
	public static function saveGalaxies() {
		foreach(self::$CACHE_GALAXIES as $gameGalaxies) {
			foreach($gameGalaxies as $galaxy) {
				$galaxy->save();
			}
		}
	}
	
	public static function &createGalaxy($gameID,$galaxyID) {
		if(!isset(self::$CACHE_GALAXIES[$gameID][$galaxyID])) {
			$g = new SmrGalaxy($gameID,$galaxyID,true);
			self::$CACHE_GALAXIES[$gameID][$galaxyID] = $g;
		}
		return self::$CACHE_GALAXIES[$gameID][$galaxyID];
	}
	
	protected function __construct($gameID, $galaxyID,$create=false) {
		$this->db = new SmrMySqlDatabase();

		$this->db->query('SELECT * FROM game_galaxy WHERE game_id = ' . $this->db->escapeNumber($gameID) . '
							AND galaxy_id = ' . $this->db->escapeNumber($galaxyID) . ' LIMIT 1');
		if($this->db->nextRecord()) {
			$this->gameID		= $this->db->getInt('game_id');
			$this->galaxyID		= $this->db->getInt('galaxy_id');
			$this->name			= $this->db->getField('galaxy_name');
			$this->width		= $this->db->getInt('width');
			$this->height		= $this->db->getInt('height');
			$this->galaxyType	= $this->db->getField('galaxy_type');
			$this->maxForceTime	= $this->db->getInt('max_force_time');
		}
		else if($create===true) {
			$this->gameID		= (int)$gameID;
			$this->galaxyID		= (int)$galaxyID;
			$this->isNew		= true;
			$this->maxForceTime	= 0;
		}
		else {
			throw new Exception('No such galaxy: '.$gameID.'-'.$galaxyID);
		}
	}

	public function save() {
		if($this->hasChanged) {
			if($this->isNew==false) {
				$this->db->query('UPDATE game_galaxy SET galaxy_name = ' . $this->db->escapeString($this->getName()) .
										', width = ' . $this->db->escapeNumber($this->getWidth()) .
										', height = ' . $this->db->escapeNumber($this->getHeight()) .
										', galaxy_type = ' . $this->db->escapeString($this->getGalaxyType()) .
										', max_force_time = ' . $this->db->escapeNumber($this->getMaxForceTime()) .
									' WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()) .
										' AND galaxy_id = ' . $this->db->escapeNumber($this->getGalaxyID()) . ' LIMIT 1');
			}
			else {
				$this->db->query('INSERT INTO game_galaxy (game_id,galaxy_id,galaxy_name,width,height,galaxy_type,max_force_time)
									values
									(' . $this->db->escapeNumber($this->getGameID()) .
									',' . $this->db->escapeNumber($this->getGalaxyID()) .
									',' . $this->db->escapeString($this->getName()) .
									',' . $this->db->escapeNumber($this->getWidth()) .
									',' . $this->db->escapeNumber($this->getHeight()) .
									',' . $this->db->escapeString($this->getGalaxyType()) .
									',' . $this->db->escapeNumber($this->getMaxForceTime()) . ')');
				$this->isNew = false;
			}
		}
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
		$name = htmlentities($name,ENT_COMPAT,'utf-8');
		if($this->name == $name) {
			return;
		}
		$this->name = $name;
		$this->hasChanged = true;
	}
	
	public function getWidth() {
		return $this->width;
	}
	
	public function setWidth($width) {
		if($this->width == $width) {
			return;
		}
		$this->width = $width;
		$this->hasChanged = true;
	}
	
	public function getHeight() {
		return $this->height;
	}
	
	public function setHeight($height) {
		if($this->height == $height) {
			return;
		}
		$this->height = $height;
		$this->hasChanged = true;
	}
	
	public function getStartSector() {
		if($this->startSector === false) {
			$this->startSector = 1;
			if ($this->getGalaxyID() != 1) {
				$galaxies = SmrGalaxy::getGameGalaxies($this->getGameID());
				for ($i=1;$i < $this->getGalaxyID();$i++) {
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
	
	public function getGalaxyType() {
		return $this->galaxyType;
	}
	
	public function setGalaxyType($galaxyType) {
		if($this->galaxyType == $galaxyType) {
			return;
		}
		$this->galaxyType = $galaxyType;
		$this->hasChanged = true;
	}
	
	public function getMaxForceTime() {
		return $this->maxForceTime;
	}
	
	public function setMaxForceTime($maxForceTime) {
		if($this->maxForceTime == $maxForceTime) {
			return;
		}
		$this->maxForceTime = $maxForceTime;
		$this->hasChanged = true;
	}
	
	public function generateSectors($connectivity = 100) {
		if($connectivity < 1) {
			$connectivity = 1;
		}
		$problem = true;
		$problemTimes = 0;
		
		$sectorID = $this->getStartSector();
		for ($i = 0; $i < $this->getSize(); $i++) {
			$sector = SmrSector::createSector($this->gameID, $sectorID);
			$sector->setGalaxyID($this->getGalaxyID());
			$sector->update(); //Have to save sectors after creating them
			$sectorID++;
		}
		$linkDirs = array('Up','Down','Left','Right');
		while ($problem) {
			$problem = false;
		
			$galSectors = SmrSector::getGalaxySectors($this->getGameID(),$this->getGalaxyID());
			foreach($galSectors as $galSector) {
				foreach($linkDirs as $linkDir) {
					$galSector->disableLink($linkDir);
				}
			}
			$sectorID = $this->getStartSector();
			for ($row = 1; $row <= $this->getHeight(); $row++) {
				for ($col = 1; $col <= $this->getWidth(); $col++) {
					$sector = SmrSector::getSector($this->gameID,$sectorID);
					foreach($linkDirs as $linkDir) {
						if (mt_rand(1,100) <= $connectivity) {
							$sector->enableLink($linkDir);
						}
					}
					$sectorID++;
				}
			}
			
			$sectorID = $this->getStartSector();
			if($this->getSize() > 1) { //1 sector gal will have no connections but that's not a problem.
				for ($i = 0; $i < $this->getSize(); $i++) {
					if(SmrSector::getSector($this->getGameID(), $sectorID)->getNumberOfConnections() == 0) {
						$problem=true;
						break;
					}
					$sectorID++;
				}
			}
			if ($problem && $problemTimes++ > 350) {
				$connectivity = 100;
			}
		}
		return $problemTimes <= 350;
	}
	
	public function contains($sectorID) {
		if($sectorID instanceof SmrSector) {
			return $sectorID->getGalaxyID()==$this->getGalaxyID();
		}
		return $sectorID >= $this->getStartSector() && $sectorID <= $this->getEndSector();
	}
	
	public static function &getGalaxyContaining($gameID, $sectorID) {
		return SmrSector::getSector($gameID, $sectorID)->getGalaxy();
	}
	
	function equals(SmrGalaxy $otherGalaxy) {
		return $otherGalaxy->getGalaxyID() == $this->getGalaxyID() && $otherGalaxy->getGameID() == $this->getGameID();
	}
}
?>
