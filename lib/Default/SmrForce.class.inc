<?php

class SmrForce {
	protected static $CACHE_FORCES = array();
	protected static $CACHE_SECTOR_FORCES = array();
	protected static $TIDIED_UP = array();

	const LOWEST_MAX_EXPIRE_SCOUTS_ONLY = 432000; // 5 days
	const TIME_PER_SCOUT_ONLY = 86400; // 1 = 1 day
	const TIME_PERCENT_PER_SCOUT = 0.02; // 1/50th
	const TIME_PERCENT_PER_COMBAT = 0.02; // 1/50th
	const TIME_PERCENT_PER_MINE = 0.02; // 1/50th
	const REFRESH_ALL_TIME_PER_STACK = 1; // 1 second

	protected static $refreshAllHREF;

	protected $db;
	protected $SQL;

	protected $ownerID;
	protected $sectorID;
	protected $gameID;
	protected $combatDrones=0;
	protected $scoutDrones=0;
	protected $mines=0;
	protected $expire=0;

	protected $isNew;
	protected $hasChanged = false;

	public static function refreshCache() {
		foreach(self::$CACHE_FORCES as $gameID => &$gameForces) {
			foreach($gameForces as $sectorID => &$gameSectorForces) {
				foreach($gameSectorForces as $ownerID => &$forces) {
					$forces = self::getForce($gameID,$sectorID,$ownerID,true);
				}
			}
		}
	}

	public static function clearCache() {
		self::$CACHE_FORCES = array();
		self::$CACHE_SECTOR_FORCES = array();
	}

	public static function saveForces() {
		foreach(self::$CACHE_FORCES as $gameForces) {
			foreach($gameForces as $gameSectorForces) {
				foreach($gameSectorForces as $forces) {
					$forces->update();
				}
			}
		}
	}

	public static function &getSectorForces($gameID,$sectorID,$forceUpdate = false) {
		if($forceUpdate || !isset(self::$CACHE_SECTOR_FORCES[$gameID][$sectorID])) {
			self::tidyUpForces(SmrGalaxy::getGalaxyContaining($gameID,$sectorID));
			$db = new SmrMySqlDatabase();
			$db->query('SELECT * FROM sector_has_forces WHERE sector_id = ' . $db->escapeNumber($sectorID) . ' AND game_id=' . $db->escapeNumber($gameID) . ' ORDER BY expire_time ASC');
			$forces = array();
			while($db->nextRecord()) {
				$ownerID = $db->getInt('owner_id');
				$forces[$ownerID] = self::getForce($gameID, $sectorID, $ownerID, $forceUpdate, $db);
			}
			self::$CACHE_SECTOR_FORCES[$gameID][$sectorID] = $forces;
		}
		return self::$CACHE_SECTOR_FORCES[$gameID][$sectorID];
	}

	public static function &getForce($gameID, $sectorID, $ownerID, $forceUpdate=false, $db=null) {
		if($forceUpdate || !isset(self::$CACHE_FORCES[$gameID][$sectorID][$ownerID])) {
			self::tidyUpForces(SmrGalaxy::getGalaxyContaining($gameID,$sectorID));
			$p = new SmrForce($gameID, $sectorID, $ownerID, $db);
			self::$CACHE_FORCES[$gameID][$sectorID][$ownerID] = $p;
		}
		return self::$CACHE_FORCES[$gameID][$sectorID][$ownerID];
	}

	public static function tidyUpForces(SmrGalaxy $galaxyToTidy) {
		if(!isset(self::$TIDIED_UP[$galaxyToTidy->getGameID()][$galaxyToTidy->getGalaxyID()])) {
			self::$TIDIED_UP[$galaxyToTidy->getGameID()][$galaxyToTidy->getGalaxyID()] = true;
			$db = new SmrMySqlDatabase();
			$db->query('UPDATE sector_has_forces
						SET refresher=0,
							expire_time = (refresh_at + if(combat_drones+mines=0,
															LEAST('.$db->escapeNumber(self::LOWEST_MAX_EXPIRE_SCOUTS_ONLY).', scout_drones*'.$db->escapeNumber(self::TIME_PER_SCOUT_ONLY).'),
															LEAST('.$db->escapeNumber($galaxyToTidy->getMaxForceTime()).', (combat_drones*'.$db->escapeNumber(self::TIME_PERCENT_PER_COMBAT).'+scout_drones*'.$db->escapeNumber(self::TIME_PERCENT_PER_SCOUT).'+mines*'.$db->escapeNumber(self::TIME_PERCENT_PER_MINE).')*'.$db->escapeNumber($galaxyToTidy->getMaxForceTime()).')
														))
						WHERE game_id = '.$db->escapeNumber($galaxyToTidy->getGameID()).' AND sector_id >= '.$db->escapeNumber($galaxyToTidy->getStartSector()).' AND sector_id <= '.$db->escapeNumber($galaxyToTidy->getEndSector()).' AND refresher != 0 AND refresh_at <= '.$db->escapeNumber(TIME));
			$db->query('DELETE FROM sector_has_forces WHERE expire_time < ' . $db->escapeNumber(TIME));
		}
	}

	protected function __construct($gameID, $sectorID, $ownerID, $db=null) {
		$this->db = new SmrMySqlDatabase();
		$this->SQL = 'game_id = '.$this->db->escapeNumber($gameID).'
		              AND sector_id = '.$this->db->escapeNumber($sectorID).'
		              AND owner_id = '.$this->db->escapeNumber($ownerID);

		if (isset($db)) {
			$this->isNew = false;
		} else {
			$db = $this->db;
			$this->db->query('SELECT * FROM sector_has_forces WHERE ' . $this->SQL);
			$this->isNew = !$db->nextRecord();
		}

		$this->gameID = $gameID;
		$this->ownerID = $ownerID;
		$this->sectorID = $sectorID;
		if (!$this->isNew) {
			$this->combatDrones = $db->getInt('combat_drones');
			$this->scoutDrones = $db->getInt('scout_drones');
			$this->mines = $db->getInt('mines');
			$this->expire = $db->getInt('expire_time');
		}
	}

	public function exists() {
		return ($this->hasCDs()||$this->hasSDs()||$this->hasMines())&&!$this->hasExpired();
	}

	public function canAcceptCDs() {
		return $this->getCDs()<50;
	}

	public function canAcceptSDs() {
		return $this->getSDs()<5;
	}

	public function canAcceptMines() {
		return $this->getMines()<50;
	}

	public function hasCDs() {
		return $this->getCDs()>0;
	}

	public function hasSDs() {
		return $this->getSDs()>0;
	}

	public function hasMines() {
		return $this->getMines()>0;
	}

	public function getCDs() {
		return $this->combatDrones;
	}

	public function getSDs() {
		return $this->scoutDrones;
	}

	public function getMines() {
		return $this->mines;
	}

	public function addMines($amount) {
		if($amount<0) {
			throw new Exception('Cannot add negative mines.');
		}
		$this->setMines($this->getMines() + $amount);
	}

	public function addCDs($amount) {
		if($amount<0) {
			throw new Exception('Cannot add negative CDs.');
		}
		$this->setCDs($this->getCDs() + $amount);
	}

	public function addSDs($amount) {
		if($amount<0) {
			throw new Exception('Cannot add negative SDs.');
		}
		$this->setSDs($this->getSDs() + $amount);
	}

	public function takeMines($amount) {
		if($amount<0) {
			throw new Exception('Cannot take negative mines.');
		}
		$this->setMines($this->getMines() - $amount);
	}

	public function takeCDs($amount) {
		if($amount<0) {
			throw new Exception('Cannot take negative CDs.');
		}
		$this->setCDs($this->getCDs() - $amount);
	}

	public function takeSDs($amount) {
		if($amount<0) {
			throw new Exception('Cannot take negative SDs.');
		}
		$this->setSDs($this->getSDs() - $amount);
	}

	public function setMines($amount) {
		if($amount<0) {
			throw new Exception('Cannot set negative mines.');
		}
		if($amount==$this->getMines()) {
			return;
		}
		$this->hasChanged = true;
		$this->mines = $amount;
	}

	public function setCDs($amount) {
		if($amount<0) {
			throw new Exception('Cannot set negative CDs.');
		}
		if($amount==$this->getCDs()) {
			return;
		}
		$this->hasChanged = true;
		$this->combatDrones = $amount;
	}

	public function setSDs($amount) {
		if($amount<0) {
			throw new Exception('Cannot set negative SDs.');
		}
		if($amount==$this->getSDs()) {
			return;
		}
		$this->hasChanged = true;
		$this->scoutDrones = $amount;
	}

	public function hasExpired() {
		return $this->expire<TIME;
	}

	public function getExpire() {
		return $this->expire;
	}

	public function setExpire($time) {
		if($time<0) {
			throw new Exception('Cannot set negative expiry.');
		}
		if($time==$this->getExpire()) {
			return;
		}
		if($time > TIME + $this->getMaxExpireTime()) {
			$time = TIME + $this->getMaxExpireTime();
		}
		$this->hasChanged = true;
		$this->expire = $time;
		if(!$this->isNew)
			$this->update();
	}

	public function updateExpire() {
		// Changed (26/10/05) - scout drones count * 2
		if($this->getCDs() == 0 && $this->getMines() == 0 && $this->getSDs() > 0) {
			$time = self::TIME_PER_SCOUT_ONLY*$this->getSDs();
		}
		else {
			$time = ($this->getCDs()*self::TIME_PERCENT_PER_COMBAT + $this->getSDs()*self::TIME_PERCENT_PER_SCOUT + $this->getMines()*self::TIME_PERCENT_PER_MINE) * $this->getMaxGalaxyExpireTime();
		}
		$this->setExpire(TIME + $time);
	}

	public function getMaxExpireTime() {
		if($this->hasCDs()||$this->hasMines()) {
			return $this->getMaxGalaxyExpireTime();
		}
		if(!$this->hasCDs() && !$this->hasMines() && $this->hasSDs()) {
			return max(self::LOWEST_MAX_EXPIRE_SCOUTS_ONLY,$this->getMaxGalaxyExpireTime());
		}
		return 0;
	}

	public function getMaxGalaxyExpireTime() {
		return $this->getGalaxy()->getMaxForceTime();
	}

	public function getBumpTurnCost(AbstractSmrShip $ship) {
		$mines = $this->getMines();
		if($mines <= 1) {
			return 0;
		}
		if($mines < 10) {
			$turns = 1;
		}
		else if($mines < 25) {
			$turns = 2;
		}
		else {
			$turns = 3;
		}
		if($ship->isFederal() || $ship->hasDCS()) {
			$turns -= 1;
		}
		return $turns;
	}

	public function getAttackTurnCost(AbstractSmrShip $ship) {
		if($ship->isFederal() || $ship->hasDCS()) {
			return 2;
		}
		return 3;
	}

	public function getOwnerID() {
		return $this->ownerID;
	}

	public function getGameID() {
		return $this->gameID;
	}

	public function &getSector() {
		return SmrSector::getSector($this->getGameID(),$this->getSectorID());
	}

	public function getSectorID() {
		return $this->sectorID;
	}

	public function ping($pingMessage, AbstractSmrPlayer $playerPinging, $skipCheck=false) {
		if(!$this->hasSDs() && !$skipCheck) {
			return;
		}
		$owner = $this->getOwner();
		if(!$playerPinging->sameAlliance($owner)) {
			$playerPinging->sendMessage($owner->getAccountID(), MSG_SCOUT, $pingMessage,false);
		}
	}

	function &getGalaxy() {
		return SmrGalaxy::getGalaxyContaining($this->getGameID(),$this->getSectorID());
	}

	function &getOwner() {
		return SmrPlayer::getPlayer($this->getOwnerID(),$this->getGameID());
	}

	public function update() {
		if(!$this->isNew) {
			if (!$this->exists()) {
				$this->db->query('DELETE FROM sector_has_forces WHERE ' . $this->SQL);
				$this->isNew=true;
			}
			else if ($this->hasChanged) {
				$this->db->query('UPDATE sector_has_forces SET combat_drones = '.$this->db->escapeNumber($this->combatDrones).', scout_drones = ' . $this->db->escapeNumber($this->scoutDrones) . ', mines = ' . $this->db->escapeNumber($this->mines) . ', expire_time = ' . $this->db->escapeNumber($this->expire) . ' WHERE ' . $this->SQL);
			}
		}
		else if($this->exists()) {
			$this->db->query('INSERT INTO sector_has_forces (game_id, sector_id, owner_id, combat_drones, scout_drones, mines, expire_time)
								VALUES('.$this->db->escapeNumber($this->gameID).', '.$this->db->escapeNumber($this->sectorID).', '.$this->db->escapeNumber($this->ownerID).', '.$this->db->escapeNumber($this->combatDrones).', '.$this->db->escapeNumber($this->scoutDrones).', '.$this->db->escapeNumber($this->mines).', '.$this->db->escapeNumber($this->expire).')');
			$this->isNew=false;
		}
		// This instance is now in sync with the database
		$this->hasChanged = false;
	}

	/**
	 * Update the table fields associated with using Refresh All
	 */
	public function updateRefreshAll(SmrPlayer $player, $refreshTime) {
		$this->db->query('UPDATE sector_has_forces SET refresh_at=' . $this->db->escapeNumber($refreshTime) . ', refresher=' . $this->db->escapeNumber($player->getAccountID()) . ' WHERE ' . $this->SQL);
	}

	public function getExamineDropForcesHREF() {
		$container = create_container('skeleton.php', 'forces_drop.php');
		$container['owner_id']	= $this->getOwnerID();
		return SmrSession::getNewHREF($container);
	}

	public function getAttackForcesHREF() {
		$container = create_container('forces_attack_processing.php');
		$container['action'] = 'attack';
		$container['owner_id']	= $this->getOwnerID();
		return SmrSession::getNewHREF($container);
	}

	public function getRefreshHREF() {
		$container = create_container('forces_refresh_processing.php');
		$container['owner_id']	= $this->getOwnerID();
		return SmrSession::getNewHREF($container);
	}

	protected function getDropContainer() {
		return array('url' => 'forces_drop_processing.php', 'owner_id' => $this->getOwnerID());
	}

	public function getDropSDHREF() {
		$container = $this->getDropContainer();
		$container['drop_scout_drones']	= 1;
		return SmrSession::getNewHREF($container);
	}

	public function getTakeSDHREF() {
		$container = $this->getDropContainer();
		$container['take_scout_drones']	= 1;
		return SmrSession::getNewHREF($container);
	}

	public function getDropCDHREF() {
		$container = $this->getDropContainer();
		$container['drop_combat_drones']	= 1;
		return SmrSession::getNewHREF($container);
	}

	public function getTakeCDHREF() {
		$container = $this->getDropContainer();
		$container['take_combat_drones']	= 1;
		return SmrSession::getNewHREF($container);
	}

	public function getDropMineHREF() {
		$container = $this->getDropContainer();
		$container['drop_mines']	= 1;
		return SmrSession::getNewHREF($container);
	}

	public function getTakeMineHREF() {
		$container = $this->getDropContainer();
		$container['take_mines']	= 1;
		return SmrSession::getNewHREF($container);
	}

	public static function getRefreshAllHREF() {
		$container = create_container('forces_mass_refresh.php');
		return SmrSession::getNewHREF($container);
	}

	public function &shootPlayer(AbstractSmrPlayer $targetPlayer,$minesAreAttacker) {
		return $this->shootPlayers(array($targetPlayer),$minesAreAttacker);
	}

	public function &shootPlayers(array $targetPlayers,$minesAreAttacker) {
		$results = array('TotalDamage' => 0);
		if(!$this->exists()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;

		if($this->hasMines()) {
			$thisMines = new SmrMines($this->getGameID(),$this->getMines());
			$results['Results']['Mines'] =& $thisMines->shootPlayerAsForce($this, $targetPlayers[array_rand($targetPlayers)],$minesAreAttacker);
			$results['TotalDamage'] += $results['Results']['Mines']['ActualDamage']['TotalDamage'];
		}

		if($this->hasCDs()) {
			$thisCDs = new SmrCombatDrones($this->getGameID(),$this->getCDs());
			$results['Results']['Drones'] =& $thisCDs->shootPlayerAsForce($this, $targetPlayers[array_rand($targetPlayers)]);
			$results['TotalDamage'] += $results['Results']['Drones']['ActualDamage']['TotalDamage'];
		}

		if(!$minesAreAttacker) {
			if($this->hasSDs()) {
				$thisSDs = new SmrScoutDrones($this->getGameID(),$this->getSDs());
				$results['Results']['Scouts'] =& $thisSDs->shootPlayerAsForce($this, $targetPlayers[array_rand($targetPlayers)]);
				$results['TotalDamage'] += $results['Results']['Scouts']['ActualDamage']['TotalDamage'];
			}
		}

		$results['ForcesDestroyed'] = !$this->exists();
		return $results;
	}

	public function &doWeaponDamage(array $damage) {
		$alreadyDead = !$this->exists();
		$minesDamage = 0;
		$cdDamage = 0;
		$sdDamage = 0;
		if(!$alreadyDead) {
			$minesDamage = $this->doMinesDamage(min($damage['MaxDamage'],$damage['Armour']));
			$damage['Armour'] -= $minesDamage;
			$damage['MaxDamage'] -= $minesDamage;
			if(!$this->hasMines() && ($minesDamage == 0 || $damage['Rollover'])) {
				$cdDamage = $this->doCDDamage(min($damage['MaxDamage'],$damage['Armour']));
				$damage['Armour'] -= $cdDamage;
				$damage['MaxDamage'] -= $cdDamage;
				if(!$this->hasCDs() && ($cdDamage == 0 || $damage['Rollover'])) {
					$sdDamage = $this->doSDDamage(min($damage['MaxDamage'],$damage['Armour']));
				}
			}
		}
		$return = array(
						'KillingShot' => !$alreadyDead && !$this->exists(),
						'TargetAlreadyDead' => $alreadyDead,
						'Mines' => $minesDamage,
						'NumMines' => $minesDamage/MINE_ARMOUR,
						'HasMines' => $this->hasMines(),
						'CDs' => $cdDamage,
						'NumCDs' => $cdDamage/CD_ARMOUR,
						'HasCDs' => $this->hasCDs(),
						'SDs' => $sdDamage,
						'NumSDs' => $sdDamage/SD_ARMOUR,
						'HasSDs' => $this->hasSDs(),
						'TotalDamage' => $minesDamage+$cdDamage+$sdDamage
		);
		return $return;
	}

	protected function doMinesDamage($damage) {
		$actualDamage = min($this->getMines(),floor($damage/MINE_ARMOUR));
		$this->takeMines($actualDamage);
		return $actualDamage*MINE_ARMOUR;
	}

	protected function doCDDamage($damage) {
		$actualDamage = min($this->getCDs(),floor($damage/CD_ARMOUR));
		$this->takeCDs($actualDamage);
		return $actualDamage*CD_ARMOUR;
	}

	protected function doSDDamage($damage) {
		$actualDamage = min($this->getSDs(),floor($damage/SD_ARMOUR));
		$this->takeSDs($actualDamage);
		return $actualDamage*SD_ARMOUR;
	}

	public function &killForcesByPlayer(AbstractSmrPlayer $killer) {
		$return = array();
		return $return;
	}
}
