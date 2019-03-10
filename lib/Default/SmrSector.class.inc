<?php

// Exception thrown when a sector cannot be found in the database
class SectorNotFoundException extends Exception {}

class SmrSector {
	protected static $CACHE_SECTORS = array();
	protected static $CACHE_GALAXY_SECTORS = array();
	protected static $CACHE_LOCATION_SECTORS = array();

	protected $db;
	protected $SQL;

	protected $gameID;
	protected $sectorID;
	protected $battles;
	protected $galaxyID;
	protected $visited = array();
	protected $links;
	protected $warp;

	protected $hasChanged = false;
	protected $isNew = false;

	/**
	 * Constructs the sector to determine if it exists.
	 * Returns a boolean value.
	 */
	public static function sectorExists($gameID, $sectorID) {
		try {
			self::getSector($gameID, $sectorID);
			return true;
		} catch (SectorNotFoundException $e) {
			return false;
		}
	}

	public static function &getGalaxySectors($gameID,$galaxyID,$forceUpdate = false) {
		if($forceUpdate || !isset(self::$CACHE_GALAXY_SECTORS[$gameID][$galaxyID])) {
			$db = new SmrMySqlDatabase();
			$db->query('SELECT * FROM sector WHERE game_id = ' . $db->escapeNumber($gameID) .' AND galaxy_id=' . $db->escapeNumber($galaxyID) . ' ORDER BY sector_id ASC');
			$sectors = array();
			while($db->nextRecord()) {
				$sectorID = $db->getInt('sector_id');
				$sectors[$sectorID] = self::getSector($gameID, $sectorID, $forceUpdate, $db);
			}
			self::$CACHE_GALAXY_SECTORS[$gameID][$galaxyID] = $sectors;
		}
		return self::$CACHE_GALAXY_SECTORS[$gameID][$galaxyID];
	}

	public static function &getLocationSectors($gameID,$locationTypeID,$forceUpdate = false) {
		if($forceUpdate || !isset(self::$CACHE_LOCATION_SECTORS[$gameID][$locationTypeID])) {
			$db = new SmrMySqlDatabase();
			$db->query('SELECT * FROM location JOIN sector USING (game_id, sector_id) WHERE location_type_id = ' . $db->escapeNumber($locationTypeID) . ' AND game_id=' . $db->escapeNumber($gameID) . ' ORDER BY sector_id ASC');
			$sectors = array();
			while($db->nextRecord()) {
				$sectorID = $db->getInt('sector_id');
				$sectors[$sectorID] = self::getSector($gameID, $sectorID, $forceUpdate, $db);
			}
			self::$CACHE_LOCATION_SECTORS[$gameID][$locationTypeID] = $sectors;
		}
		return self::$CACHE_LOCATION_SECTORS[$gameID][$locationTypeID];
	}

	public static function &getSector($gameID, $sectorID, $forceUpdate=false, $db=null) {
		if(!isset(self::$CACHE_SECTORS[$gameID][$sectorID]) || $forceUpdate) {
			self::$CACHE_SECTORS[$gameID][$sectorID] = new SmrSector($gameID, $sectorID, false, $db);
		}
		return self::$CACHE_SECTORS[$gameID][$sectorID];
	}

	public static function clearCache() {
		self::$CACHE_LOCATION_SECTORS = array();
		self::$CACHE_GALAXY_SECTORS = array();
		self::$CACHE_SECTORS = array();
	}

	public static function saveSectors() {
		foreach(self::$CACHE_SECTORS as $gameSectors) {
			foreach($gameSectors as $sector) {
				$sector->update();
			}
		}
	}

	public static function &createSector($gameID,$sectorID) {
		if(!isset(self::$CACHE_SECTORS[$gameID][$sectorID])) {
			$s = new SmrSector($gameID,$sectorID,true);
			self::$CACHE_SECTORS[$gameID][$sectorID] = $s;
		}
		return self::$CACHE_SECTORS[$gameID][$sectorID];
	}

	protected function __construct($gameID, $sectorID, $create=false, $db=null) {
		$this->db = new SmrMySqlDatabase();
		$this->SQL = 'game_id = ' . $this->db->escapeNumber($gameID) . ' AND sector_id = ' . $this->db->escapeNumber($sectorID);

		// Do we already have a database query for this sector?
		if (isset($db)) {
			$sectorExists = true;
		} else {
			$db = $this->db;
			$db->query('SELECT * FROM sector WHERE ' . $this->SQL . ' LIMIT 1');
			$sectorExists = $db->nextRecord();
		}

		$this->gameID = (int) $gameID;
		$this->sectorID = (int) $sectorID;

		if ($sectorExists) {
			$this->galaxyID = $db->getInt('galaxy_id');
			$this->battles = $db->getInt('battles');

			$this->links = array();
			if ($db->getField('link_up')) {
				$this->links['Up'] = $db->getInt('link_up');
			}
			if ($db->getField('link_down')) {
				$this->links['Down'] = $db->getInt('link_down');
			}
			if ($db->getField('link_left')) {
				$this->links['Left'] = $db->getInt('link_left');
			}
			if ($db->getField('link_right')) {
				$this->links['Right'] = $db->getInt('link_right');
			}
			$this->warp = $db->getInt('warp');
		}
		else if($create) {
			$this->battles		= 0;
			$this->links = array();
			$this->warp = 0;
			$this->isNew		= true;
			return;
		}
		else {
			throw new SectorNotFoundException('No such sector: '.$gameID.'-'.$sectorID);
		}
	}

	function update() {
		if($this->hasChanged) {
			if($this->isNew===false) {
				$this->db->query('UPDATE sector SET battles = ' . $this->db->escapeNumber($this->getBattles()) .
									', galaxy_id=' . $this->db->escapeNumber($this->getGalaxyID()) .
									', link_up=' . $this->db->escapeNumber($this->getLinkUp()) .
									', link_right=' . $this->db->escapeNumber($this->getLinkRight()) .
									', link_down=' . $this->db->escapeNumber($this->getLinkDown()) .
									', link_left=' . $this->db->escapeNumber($this->getLinkLeft()) .
									', warp=' . $this->db->escapeNumber($this->getWarp()) .
								' WHERE ' . $this->SQL . ' LIMIT 1');
			}
			else {
				$this->db->query('INSERT INTO sector(sector_id,game_id,galaxy_id,link_up,link_down,link_left,link_right,warp)
								values
								(' . $this->db->escapeNumber($this->getSectorID()) .
								',' . $this->db->escapeNumber($this->getGameID()) .
								',' . $this->db->escapeNumber($this->getGalaxyID()) .
								',' . $this->db->escapeNumber($this->getLinkUp()) .
								',' . $this->db->escapeNumber($this->getLinkDown()) .
								',' . $this->db->escapeNumber($this->getLinkLeft()) .
								',' . $this->db->escapeNumber($this->getLinkRight()) .
								',' . $this->db->escapeNumber($this->getWarp()) .
								')');
				$this->isNew=false;
			}
		}
	}

	function markVisited(AbstractSmrPlayer $player) {
		if($this->hasPort())
			$this->getPort()->addCachePort($player->getAccountID());

		//now delete the entry from visited
		if(!$this->isVisited($player))
			$this->db->query('DELETE FROM player_visited_sector WHERE ' . $this->SQL . '
								 AND account_id = ' . $this->db->escapeNumber($player->getAccountID()) . ' LIMIT 1');
		$this->visited[$player->getAccountID()]=true;
	}

	function hasWeaponShop() {
		$locations = $this->getLocations();
		foreach($locations as $location) {
			if($location->isWeaponSold())
				return true;
		}
		return false;
	}

	function hasHQ() {
		$locations = $this->getLocations();
		foreach($locations as $location) {
			if($location->isHQ())
				return true;
		}
		return false;
	}

	function hasUG() {
		$locations = $this->getLocations();
		foreach($locations as $location) {
			if($location->isUG())
				return true;
		}
		return false;
	}

	function hasShipShop() {
		$locations = $this->getLocations();
		foreach($locations as $location) {
			if($location->isShipSold())
				return true;
		}
		return false;
	}

	function offersFederalProtection() {
		$locations = $this->getLocations();
		foreach($locations as $location) {
			if($location->isFed())
				return true;
		}
		return false;
	}

	public function getFedRaceIDs() {
		$raceIDs = array();
		$locations = $this->getLocations();
		foreach($locations as $location) {
			if($location->isFed())
				$raceIDs[$location->getRaceID()] = $location->getRaceID();
		}
		return $raceIDs;
	}

	function hasBar() {
		$locations = $this->getLocations();
		foreach($locations as $location) {
			if($location->isBar())
				return true;
		}
		return false;
	}

	function hasHardwareShop() {
		$locations = $this->getLocations();
		foreach($locations as $location) {
			if($location->isHardwareSold())
				return true;
		}
		return false;
	}

	function hasBank() {
		$locations = $this->getLocations();
		foreach($locations as $location) {
			if($location->isBank())
				return true;
		}
		return false;
	}

	function enteringSector(AbstractSmrPlayer $player, $movementType) {
		// send scout messages to user
		$message = 'Your forces have spotted ' . $player->getBBLink() . ' ';
		switch($movementType) {
			case MOVEMENT_JUMP:
				$message .= 'jumping into';
			break;
			case MOVEMENT_WARP:
				$message .= 'warping into';
			break;
			case MOVEMENT_WALK:
			default:
				$message .= 'entering';
		}
		$message .= ' sector ' . Globals::getSectorBBLink($this->getSectorID());

		$forces = $this->getForces();
		foreach($forces as $force) {
			$force->ping($message,$player);
		}
	}

	function leavingSector(AbstractSmrPlayer $player, $movementType) {
		// send scout messages to user
		$message = 'Your forces have spotted ' . $player->getBBLink() . ' ';
		switch($movementType) {
			case MOVEMENT_JUMP:
				$message .= 'jumping from';
			break;
			case MOVEMENT_WARP:
				$message .= 'warping from';
			break;
			case MOVEMENT_WALK:
			default:
				$message .= 'leaving';
		}
		$message .= ' sector ' . Globals::getSectorBBLink($this->getSectorID());

		// iterate over all scout drones in sector
		$forces = $this->getForces();
		foreach($forces as $force) {
			$force->ping($message,$player);
		}
		$this->db->query('UPDATE sector_has_forces SET refresher = 0 WHERE ' . $this->SQL . '
								AND refresher = ' . $this->db->escapeNumber($player->getAccountID()));
	}

	function diedHere(AbstractSmrPlayer $player) {
		// iterate over all scout drones in sector
		$forces = $this->getForces();
		foreach($forces as $force) {
			// send scout messages to user
			$message = 'Your forces have spotted that ' . $player->getBBLink() . ' has been <span class="red">DESTROYED</span> in sector ' . Globals::getSectorBBLink($this->sectorID);
			$force->ping($message,$player);
		}
	}

	public function getGameID() {
		return $this->gameID;
	}

	public function getSectorID() {
		return $this->sectorID;
	}

	public function getGalaxyID() {
		return $this->galaxyID;
	}

	public function setGalaxyID($galaxyID) {
		if($this->galaxyID==$galaxyID)
			return;
		$this->galaxyID=$galaxyID;
		$this->hasChanged=true;
	}

	public function getGalaxyName() {
		return $this->getGalaxy()->getName();
	}

	public function getNumberOfLinks() {
		$num=0;
		if(!is_array($this->getLinks()))
			return $num;
		foreach($this->getLinks() as $link)
			if($link!==0)
				$num++;
		return $num;
	}

	public function getNumberOfConnections() {
		$links = $this->getNumberOfLinks();
		if($this->hasWarp())
			$links++;
		return $links;
	}

	public function &getGalaxy() {
		return SmrGalaxy::getGalaxy($this->getGameID(),$this->getGalaxyID());
	}

	public function getNeighbourID($dir) {
		if($this->hasLink($dir))
			return $this->getLink($dir);
		$galaxy = $this->getGalaxy();
		$neighbour = $this->getSectorID();
		switch($dir) {
			case 'Up':
				$neighbour -= $galaxy->getWidth();
				if($neighbour<$galaxy->getStartSector())
					$neighbour+=$galaxy->getSize();
			break;
			case 'Down':
				$neighbour += $galaxy->getWidth();
				if($neighbour>$galaxy->getEndSector())
					$neighbour-=$galaxy->getSize();
			break;
			case 'Left':
				$neighbour -= 1;
				if((1+$neighbour-$galaxy->getStartSector())%$galaxy->getWidth()==0)
					$neighbour+=$galaxy->getWidth();
			break;
			case 'Right':
				$neighbour += 1;
				if(($neighbour-$galaxy->getStartSector())%$galaxy->getWidth()==0)
					$neighbour-=$galaxy->getWidth();
			break;
			default:
				throw new Exception($dir.': is not a valid direction');
		}
		return $neighbour;
	}

	public function getSectorDirection($sectorID) {
		if($sectorID == $this->getSectorID())
			return 'Current';
		$dir = array_search($sectorID, $this->getLinks());
		if($dir !== false)
			return $dir;
		if($sectorID == $this->getWarp())
			return 'Warp';
		return 'None';
	}

	public function &getNeighbourSector($dir) {
		return SmrSector::getSector($this->getGameID(),$this->getNeighbourID($dir));
	}

	public function getLinks() {
		return $this->links;
	}

	public function isLinked($sectorID) {
		return in_array($sectorID,$this->links)||$sectorID==$this->getWarp();
	}

	public function getLink($name) {
		return isset($this->links[$name]) ? $this->links[$name] : 0;
	}

	public function hasLink($name) {
		return $this->getLink($name)!=0;
	}

	public function &getLinkSector($name) {
		if($this->hasLink($name))
			return SmrSector::getSector($this->getGameID(),$this->getLink($name));
		return false;
	}

	/**
	 * Cannot be used for Warps
	 */
	public function setLink($name,$linkID) {
		if($this->getLink($name)==$linkID)
			return;
		if($linkID==0)
			unset($this->links[$name]);
		else
			$this->links[$name] = $linkID;
		$this->hasChanged=true;
	}

	/**
	 * Cannot be used for Warps
	 */
	public function setLinkSector($dir,SmrSector $linkSector) {
		if($this->getLink($dir)==$linkSector->getSectorID()||$linkSector->equals($this))
			return;
		$this->setLink($dir,$linkSector->getSectorID());
		$linkSector->setLink(self::oppositeDir($dir),$this->getSectorID());
		$this->hasChanged=true;
	}
	/**
	 * Cannot be used for Warps
	 */
	public function enableLink($dir) {
		$this->setLinkSector($dir,$this->getNeighbourSector($dir));
	}
	/**
	 * Cannot be used for Warps
	 */
	public function disableLink($dir) {
		$this->setLink($dir,0);
		$this->getNeighbourSector($dir)->setLink(self::oppositeDir($dir),0);
	}
	/**
	 * Cannot be used for Warps
	 */
	public function toggleLink($dir) {
		if($this->hasLink($dir)) {
			$this->disableLink($dir);
		}
		else {
			$this->enableLink($dir);
		}
	}

	protected static function oppositeDir($dir) {
		switch($dir) {
			case 'Up': return 'Down';
			case 'Down': return 'Up';
			case 'Left': return 'Right';
			case 'Right': return 'Left';
			case 'Warp': return 'Warp';
		}
	}

	public function getLinkUp() {
		return $this->getLink('Up');
	}

	public function setLinkUp($linkID) {
		$this->setLink('Up',$linkID);
	}

	public function hasLinkUp() {
		return $this->hasLink('Up');
	}

	public function getLinkDown() {
		return $this->getLink('Down');
	}

	public function setLinkDown($linkID) {
		$this->setLink('Down',$linkID);
	}

	public function hasLinkDown() {
		return $this->hasLink('Down');
	}

	public function getLinkLeft() {
		return $this->getLink('Left');
	}

	public function hasLinkLeft() {
		return $this->hasLink('Left');
	}

	public function setLinkLeft($linkID) {
		$this->setLink('Left',$linkID);
	}

	public function getLinkRight() {
		return $this->getLink('Right');
	}

	public function hasLinkRight() {
		return $this->hasLink('Right');
	}

	public function setLinkRight($linkID) {
		$this->setLink('Right',$linkID);
	}

	/**
	 * Returns the warp sector if the sector has a warp; returns 0 otherwise.
	 */
	public function getWarp() {
		return $this->warp;
	}

	public function &getWarpSector() {
		return SmrSector::getSector($this->getGameID(),$this->getWarp());
	}

	public function hasWarp() {
		return $this->getWarp()!=0;
	}

	/**
	 * Set the warp sector for both $this and $warp to ensure
	 * a consistent 2-way warp.
	 */
	public function setWarp(SmrSector $warp) {
		if ($this->getWarp() == $warp->getSectorID() &&
		    $warp->getWarp() == $this->getSectorID()) {
			// Warps are already set correctly!
			return;
		}

		if ($this->equals($warp)) {
			throw new Exception('Sector must not warp to itself!');
		}

		if (($warp->hasWarp() && $warp->getWarp() != $this->getSectorID()) ||
		    ($this->hasWarp() && $this->getWarp() != $warp->getSectorID())) {
			throw new Exception('One of the sectors already has a warp!');
		}

		$this->warp = $warp->getSectorID();
		$this->hasChanged = true;

		if ($warp->getWarp() != $this->getSectorID()) {
			// Set the other side if needed
			$warp->setWarp($this);
		}
	}

	/**
	 * Remove the warp sector for both sides of the warp.
	 */
	public function removeWarp() {
		if (!$this->hasWarp()) {
			return;
		}

		$warp = $this->getWarpSector();
		if ($warp->hasWarp() && $warp->getWarp() != $this->getSectorID()) {
			throw new Exception('Warp sectors do not match');
		}

		$this->warp = 0;
		$this->hasChanged = true;

		if ($warp->hasWarp()) {
			$warp->removeWarp();
		}
	}

	public function hasPort() {
		return $this->getPort()->exists();
	}

	public function &getPort() {
		return SmrPort::getPort($this->getGameID(),$this->getSectorID());
	}

	public function &createPort() {
		return SmrPort::createPort($this->getGameID(),$this->getSectorID());
	}

	public function removePort() {
		SmrPort::removePort($this->getGameID(),$this->getSectorID());
	}

	public function hasCachedPort(AbstractSmrPlayer $player=null) {
		return $this->getCachedPort($player)!==false;
	}

	public function &getCachedPort(AbstractSmrPlayer $player=null) {
		if($player == null) {
			$return = false;
			return $return;
		}
		return SmrPort::getCachedPort($this->getGameID(),$this->getSectorID(),$player->getAccountID());
	}

	public function hasAnyLocationsWithAction() {
		$locations = SmrLocation::getSectorLocations($this->getGameID(),$this->getSectorID());
		$hasAction = false;
		foreach($locations as $location) {
			if($location->hasAction())
				$hasAction = true;
		}
		return $hasAction;
	}

	public function hasLocation($locationTypeID=false) {
		$locations = $this->getLocations();
		if(count($locations) == 0)
			return false;
		if($locationTypeID==false)
			return true;
		foreach($locations as $location) {
			if($location->getTypeID()==$locationTypeID)
				return true;
		}
		return false;
	}

	public function &getLocations() {
		return SmrLocation::getSectorLocations($this->getGameID(),$this->getSectorID());
	}

	public function addLocation(SmrLocation $location) {
		$this->db->query('INSERT INTO location (game_id,sector_id,location_type_id)
						values(' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeNumber($this->getSectorID()) . ',' . $this->db->escapeNumber($location->getTypeID()) . ')');
		SmrLocation::getSectorLocations($this->getGameID(),$this->getSectorID(),true);
	}

	public function removeAllLocations() {
		$this->db->query('DELETE FROM location WHERE ' . $this->SQL);
		SmrLocation::getSectorLocations($this->getGameID(),$this->getSectorID(),true);
	}

	public function hasPlanet() {
		return $this->getPlanet()->exists();
	}

	public function &getPlanet() {
		return SmrPlanet::getPlanet($this->getGameID(),$this->getSectorID());
	}

	public function &createPlanet($type=1) {
		return SmrPlanet::createPlanet($this->getGameID(),$this->getSectorID(), $type);
	}

	public function removePlanet() {
		SmrPlanet::removePlanet($this->getGameID(),$this->getSectorID());
	}

	public function hasForces() {
		return count($this->getForces()) > 0;
	}

	public function hasEnemyForces(AbstractSmrPlayer $player=null) {
		if($player == null || !$this->hasForces())
			return false;
		foreach ($this->getForces() as $force) {
			if(!$player->forceNAPAlliance($force->getOwner()))
				return true;
		}
		return false;
	}

	public function &getEnemyForces(AbstractSmrPlayer $player) {
		$enemyForces = array();
		foreach ($this->getForces() as $force) {
			if(!$player->forceNAPAlliance($force->getOwner()))
				$enemyForces[] = $force;
		}
		return $enemyForces;
	}

	/**
	 * Returns true if any forces in this sector belong to $player.
	 */
	public function hasPlayerForces(AbstractSmrPlayer $player) {
		foreach ($this->getForces() as $force) {
			if ($player->getAccountID() == $force->getOwnerID()) {
				return true;
			}
		}
		return false;
	}

	public function hasFriendlyForces(AbstractSmrPlayer $player=null) {
		if($player == null || !$this->hasForces())
			return false;
		foreach ($this->getForces() as $force) {
			if($player->forceNAPAlliance($force->getOwner()))
				return true;
		}
		return false;
	}

	public function &getFriendlyForces(AbstractSmrPlayer $player) {
		$friendlyForces = array();
		foreach ($this->getForces() as $force) {
			if($player->forceNAPAlliance($force->getOwner()))
				$friendlyForces[] = $force;
		}
		return $friendlyForces;
	}

	public function &getForces() {
		return SmrForce::getSectorForces($this->getGameID(),$this->getSectorID());
	}

	public function &getPlayers() {
		return SmrPlayer::getSectorPlayers($this->getGameID(),$this->getSectorID());
	}

	public function hasPlayers() {
		return count($this->getPlayers()) > 0;
	}

	public function &getOtherTraders(AbstractSmrPlayer $player) {
		$players = SmrPlayer::getSectorPlayers($this->getGameID(),$this->getSectorID()); //Do not use & because we unset something and only want that in what we return
		unset($players[$player->getAccountID()]);
		return $players;
	}

	public function hasOtherTraders(AbstractSmrPlayer $player) {
		return count($this->getOtherTraders($player)) > 0;
	}

	public function hasEnemyTraders(AbstractSmrPlayer $player=null) {
		if($player == null || !$this->hasOtherTraders($player))
			return false;
		$otherPlayers = $this->getOtherTraders($player);
		foreach($otherPlayers as $otherPlayer) {
			if(!$player->traderNAPAlliance($otherPlayer) 
				&& !$otherPlayer->hasNewbieTurns()
				&& !$otherPlayer->hasFederalProtection()) {
				return true;
			}
		}
		return false;
	}

	public function hasFriendlyTraders(AbstractSmrPlayer $player=null) {
		if($player == null || !$this->hasOtherTraders($player))
			return false;
		$otherPlayers = $this->getOtherTraders($player);
		foreach($otherPlayers as $otherPlayer) {
			if($player->traderNAPAlliance($otherPlayer))
				return true;
		}
		return false;
	}

	/**
	 * Is the $player's alliance flagship in this sector?
	 */
	public function hasAllianceFlagship(AbstractSmrPlayer $player=null) {
		if (is_null($player) || !$player->hasAlliance() || !$player->getAlliance()->hasFlagship()) {
			return false;
		}
		$flagshipID = $player->getAlliance()->getFlagshipID();
		foreach ($this->getPlayers() as $sectorPlayer) {
			if ($sectorPlayer->getAccountID() == $flagshipID) {
				return true;
			}
		}
		return false;
	}

	public function hasProtectedTraders(AbstractSmrPlayer $player=null) {
		if($player == null || !$this->hasOtherTraders($player))
			return false;
		$otherPlayers = $this->getOtherTraders($player);
		foreach($otherPlayers as $otherPlayer) {
			if(!$player->traderNAPAlliance($otherPlayer) 
				&& ($otherPlayer->hasNewbieTurns() || $otherPlayer->hasFederalProtection())) {
				return true;
			}
		}
		return false;
	}

	public function getFightingTradersAgainstForces(AbstractSmrPlayer $attackingPlayer, $bump) {
		// Whether bumping or attacking, only the current player fires at forces
		return array($attackingPlayer);
	}

	public function &getFightingTradersAgainstPort(AbstractSmrPlayer $attackingPlayer, SmrPort $defendingPort) {
		$fightingPlayers = array();
		$alliancePlayers = SmrPlayer::getSectorPlayersByAlliances($this->getGameID(),$this->getSectorID(), array($attackingPlayer->getAllianceID()));
		if(count($alliancePlayers) > 0)
			foreach($alliancePlayers as $accountID => $player) {
				if($player->canFight()) {
					if($attackingPlayer->traderAttackPortAlliance($player))
						$fightingPlayers[$accountID] = $alliancePlayers[$accountID];
				}
			}
		return self::limitFightingTraders($fightingPlayers, $attackingPlayer, MAXIMUM_PORT_FLEET_SIZE);
	}

	public function &getFightingTradersAgainstPlanet(AbstractSmrPlayer $attackingPlayer, SmrPlanet $defendingPlanet) {
		$fightingPlayers = array();
		$alliancePlayers = SmrPlayer::getSectorPlayersByAlliances($this->getGameID(),$this->getSectorID(), array($attackingPlayer->getAllianceID()));
		if(count($alliancePlayers) > 0) {
			$planetOwner = $defendingPlanet->getOwner();
			foreach($alliancePlayers as $accountID => $player) {
				if($player->canFight()) {
					if($attackingPlayer->traderAttackPlanetAlliance($player) && !$planetOwner->planetNAPAlliance($player))
						$fightingPlayers[$accountID] = $alliancePlayers[$accountID];
				}
			}
		}
		return self::limitFightingTraders($fightingPlayers, $attackingPlayer, min($defendingPlanet->getMaxAttackers(),MAXIMUM_PLANET_FLEET_SIZE));
	}

	public function &getFightingTraders(AbstractSmrPlayer $attackingPlayer, AbstractSmrPlayer $defendingPlayer, $checkForCloak=false) {
		if($attackingPlayer->traderNAPAlliance($defendingPlayer))
			throw new Exception('These traders are NAPed.');
		$fightingPlayers = array('Attackers' => array(), 'Defenders' => array());
		$alliancePlayers = SmrPlayer::getSectorPlayersByAlliances($this->getGameID(),$this->getSectorID(), array($attackingPlayer->getAllianceID(), $defendingPlayer->getAllianceID()));
		$attackers = array();
		$defenders = array();
		if(count($alliancePlayers) > 0)
			foreach($alliancePlayers as $accountID => $player) {
				if($player->canFight()) {
					if($attackingPlayer->traderAttackTraderAlliance($player) && !$defendingPlayer->traderDefendTraderAlliance($player) && !$defendingPlayer->traderNAPAlliance($player))
						$attackers[] = $alliancePlayers[$accountID];
					else if($defendingPlayer->traderDefendTraderAlliance($player) && !$attackingPlayer->traderAttackTraderAlliance($player) && !$attackingPlayer->traderNAPAlliance($player) && ($checkForCloak===false || $attackingPlayer->canSee($player)))
						$defenders[] = $alliancePlayers[$accountID];
				}
			}
		$attackers = self::limitFightingTraders($attackers, $attackingPlayer, MAXIMUM_PVP_FLEET_SIZE);
		shuffle($attackers);
		foreach($attackers as $attacker) {
			$fightingPlayers['Attackers'][$attacker->getAccountID()] = $attacker;
		}
		$defenders = self::limitFightingTraders($defenders,$defendingPlayer, MAXIMUM_PVP_FLEET_SIZE);
		shuffle($defenders);
		foreach($defenders as $defender) {
			$fightingPlayers['Defenders'][$defender->getAccountID()] = $defender;
		}
		return $fightingPlayers;
	}

	public static function &limitFightingTraders(array &$fightingPlayers, AbstractSmrPlayer $keepPlayer, $maximumFleetSize) {
		// Cap fleets to the required size
		$fleet_size = count($fightingPlayers);
		if($fleet_size > $maximumFleetSize) {
			// We use random key to stop the same people being capped all the time
			for($j=0;$j<$fleet_size-$maximumFleetSize;++$j) {
				do {
					$key = array_rand($fightingPlayers);
				} while($keepPlayer->equals($fightingPlayers[$key]));
				unset($fightingPlayers[$key]);
			}
		}
		return $fightingPlayers;
	}

	public function &getPotentialFightingTraders(AbstractSmrPlayer $attackingPlayer) {
		$fightingPlayers = array();
		$alliancePlayers = SmrPlayer::getSectorPlayersByAlliances($this->getGameID(),$this->getSectorID(), array($attackingPlayer->getAllianceID()));
		if(count($alliancePlayers) > 0)
			foreach($alliancePlayers as $accountID => $player) {
				if($player->canFight()) {
					if($attackingPlayer->traderAttackTraderAlliance($player))
						$fightingPlayers['Attackers'][$accountID] = $player;
				}
			}
		return $fightingPlayers;
	}

	public function getBattles() {
		return $this->battles;
	}

	public function setBattles($amount) {
		if($this->battles == $amount)
			return;
		$this->battles = $amount;
		$this->hasChanged = true;
	}

	public function decreaseBattles($amount) {
		$this->setBattles($this->battles-$amount);
	}

	public function increaseBattles($amount) {
		$this->setBattles($this->battles+$amount);
	}

	function equals(SmrSector $otherSector) {
		return $otherSector->getSectorID()==$this->getSectorID() && $otherSector->getGameID()==$this->getGameID();
	}

	function isLinkedSector(SmrSector $otherSector) {
		return $otherSector->getGameID()==$this->getGameID() && $this->isLinked($otherSector->getSectorID());
	}

	public function isVisited(AbstractSmrPlayer $player=null) {
		if($player===null)
			return true;
		if(!isset($this->visited[$player->getAccountID()])) {
			$this->db->query('SELECT sector_id FROM player_visited_sector WHERE ' . $this->SQL . ' AND account_id=' . $this->db->escapeNumber($player->getAccountID()) . ' LIMIT 1');
			$this->visited[$player->getAccountID()] = !$this->db->nextRecord();
		}
		return $this->visited[$player->getAccountID()];
	}

	public function getLocalMapMoveHREF() {
		return Globals::getSectorMoveHREF($this->getSectorID(), 'map_local.php');
	}

	public function getCurrentSectorMoveHREF() {
		return Globals::getCurrentSectorMoveHREF($this->getSectorID());
	}

	public function getGalaxyMapHREF() {
		return '?sector_id='.$this->getSectorID();
	}

	public function getScanSectorHREF() {
		return Globals::getSectorScanHREF($this->getSectorID());
	}

	public function hasX(/*Object*/ $x, AbstractSmrPlayer $player=null) {
		if($x instanceof SmrSector) {
			return $this->equals($x);
		}
		if ($x == 'Port') {
			return $this->hasPort();
		}
		if ($x == 'Location') {
			return $this->hasLocation();
		}
		if ($x instanceof SmrLocation) {
			return $this->hasLocation($x->getTypeID());
		}
		if ($x instanceof SmrGalaxy) {
			return $x->contains($this);
		}

		if(is_array($x) && $x['Type']=='Good') //Check if it's possible for port to have X, hacky but nice performance gains
			if ($this->hasPort())
				if ($this->getPort()->hasX($x))
					return true;

		if($x instanceof SmrWeapon || (is_array($x) && ($x['Type']=='Ship'||$x['Type']=='Hardware')) || (is_string($x) && ($x=='Bank'||$x=='Bar'||$x=='Fed'||$x=='SafeFed'||$x=='HQ'||$x=='UG'||$x=='Hardware'||$x=='Ship'||$x=='Weapon'))) //Check if it's possible for location to have X, hacky but nice performance gains
			if ($this->hasLocation()) {
				$locations = $this->getLocations();
				foreach ($locations as $l) {
					if ($l->hasX($x,$player))
						return true;
				}
			}
		return false;
	}
}
