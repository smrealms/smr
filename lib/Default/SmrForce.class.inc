<?php
require_once('SmrMySqlDatabase.class.inc');

require_once(get_file_loc('SmrPlayer.class.inc'));
class SmrForce
{
	protected static $CACHE_FORCES = array();
	protected static $CACHE_SECTOR_FORCES = array();
	protected static $TIDIED_UP = false;
	
	const MAX_EXPIRE_TIME = 432000; // 5 days
	const TIME_PER_SCOUT_ONLY = 8640; // 1 = 1 day
	const TIME_PER_SCOUT = 8640; // 10 = 1 day
	const TIME_PER_COMBAT = 8640; // 10 = 1 day
	const TIME_PER_MINE = 8640; // 10 = 1 day
	
	protected static $refreshAllHREF;
	
	protected $db;
	
	protected $ownerID;
	protected $sectorID;
	protected $gameID;
	protected $combatDrones=0;
	protected $scoutDrones=0;
	protected $mines=0;
	protected $expire=0;
	
	protected $isNew;
	protected $hasChanged = false;
	
	protected $examineDropForcesHREF;
	protected $examineAttackForcesHREF;
	protected $attackForcesHREF;
	protected $refreshHREF;
	protected $dropSDHREF;
	protected $takeSDHREF;
	protected $dropCDHREF;
	protected $takeCDHREF;
	protected $dropMineHREF;
	protected $takeMineHREF;
	
	public static function saveForces()
	{
		foreach(self::$CACHE_FORCES as &$forces)
		{
			$forces->update();
		}
	}
	
	public static function &getSectorForces($gameID,$sectorID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_SECTOR_FORCES[$gameID . ':' . $sectorID]))
		{
			$db = new SmrMySqlDatabase();
			$db->query('DELETE FROM sector_has_forces WHERE expire_time < ' . TIME);
			$db->query('SELECT owner_id FROM sector_has_forces WHERE sector_id = ' . $sectorID . ' AND game_id=' . $gameID);
			$forces = array();
			while($db->nextRecord())
			{
				$forces[$db->getField('owner_id')] =& self::getForce($gameID,$sectorID,$db->getField('owner_id'));
			}
			return self::$CACHE_SECTOR_FORCES[$gameID . ':' . $sectorID] =& $forces;
		}
		return self::$CACHE_SECTOR_FORCES[$gameID . ':' . $sectorID];
	}
	
	public static function &getForce($gameID,$sectorID,$ownerID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_FORCES[$gameID . ':' . $sectorID . ':' . $ownerID]))
		{
			self::tidyUpForces();
			$p = new SmrForce($gameID,$sectorID,$ownerID);
			return self::$CACHE_FORCES[$gameID . ':' . $sectorID . ':' . $ownerID] =& $p;
		}
		return self::$CACHE_FORCES[$gameID . ':' . $sectorID . ':' . $ownerID];
	}
	
	public static function tidyUpForces()
	{
		if(!self::$TIDIED_UP)
		{
			self::$TIDIED_UP = true;
			$db = new SmrMySqlDatabase();
			$db->query('UPDATE sector_has_forces SET refresher=0, expire_time = (refresh_at + if(combat_drones+mines=0,scout_drones*'.self::TIME_PER_SCOUT_ONLY.',combat_drones*'.self::TIME_PER_COMBAT.'+scout_drones*'.self::TIME_PER_SCOUT.'+mines*'.self::TIME_PER_MINE.')) WHERE refresh_at <= '.TIME.' AND refresher != 0');
			$db->query('DELETE FROM sector_has_forces WHERE expire_time < ' . TIME);
			$db->query('UPDATE sector_has_forces SET expire_time = '.(TIME+self::MAX_EXPIRE_TIME).' WHERE expire_time > '.(TIME+432000));
		}
	}
	
	protected function __construct($gameID,$sectorID, $ownerID)
	{
		$this->db = new SmrMySqlDatabase();
		$this->db->query('DELETE FROM sector_has_forces WHERE expire_time < ' . TIME);
		$this->db->query('SELECT * FROM sector_has_forces WHERE game_id = '.$gameID.' AND ' .
														   'sector_id = '.$sectorID.' AND ' .
														   'owner_id = '.$ownerID.'  LIMIT 1');
		$this->ownerID			= $ownerID;
		$this->sectorID			= $sectorID;
		$this->gameID			= $gameID;
		if($this->db->nextRecord())
		{
			$this->combatDrones		= $this->db->getField('combat_drones');
			$this->scoutDrones		= $this->db->getField('scout_drones');
			$this->mines			= $this->db->getField('mines');
			$this->expire			= $this->db->getField('expire_time');
			$this->isNew = false;
		}
		else
			$this->isNew = true;
	}
	public function exists()
	{
		return ($this->hasCDs()>0||$this->hasSDs()||$this->hasMines())&&!$this->hasExpired();
	}
	public function getTypeID()
	{
		return $this->typeID;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getProcessor()
	{
		return $this->processor;
	}
	
	public function getImage()
	{
		return $this->image;
	}
	
	public function getHREF()
	{
		if(!isset($this->HREF))
		{
			$container = array();
			$container['url'] = 'skeleton.php';
			$container['body'] = $this->getProcessor();
			$this->HREF = SmrSession::get_new_href($container);
		}
		return $this->HREF;
	}
	
	public function canAcceptCDs()
	{
		return $this->getCDs()<50;
	}
	
	public function canAcceptSDs()
	{
		return $this->getSDs()<5;
	}
	
	public function canAcceptMines()
	{
		return $this->getMines()<50;
	}
	
	public function hasCDs()
	{
		return $this->getCDs()>0;
	}
	
	public function hasSDs()
	{
		return $this->getSDs()>0;
	}
	
	public function hasMines()
	{
		return $this->getMines()>0;
	}
	
	public function getCDs()
	{
		return $this->combatDrones;
	}
	
	public function getSDs()
	{
		return $this->scoutDrones;
	}
	
	public function getMines()
	{
		return $this->mines;
	}
	
	public function addMines($amount)
	{
		if($amount<0)
			throw new Exception('Cannot add negative mines.');
		$this->setMines($this->getMines() + $amount);
	}
	
	public function addCDs($amount)
	{
		if($amount<0)
			throw new Exception('Cannot add negative CDs.');
		$this->setCDs($this->getCDs() + $amount);
	}
	
	public function addSDs($amount)
	{
		if($amount<0)
			throw new Exception('Cannot add negative SDs.');
		$this->setSDs($this->getSDs() + $amount);
	}
	
	public function takeMines($amount)
	{
		if($amount<0)
			throw new Exception('Cannot take negative mines.');
		$this->setMines($this->getMines() - $amount);
	}
	
	public function takeCDs($amount)
	{
		if($amount<0)
			throw new Exception('Cannot take negative CDs.');
		$this->setCDs($this->getCDs() - $amount);
	}
	
	public function takeSDs($amount)
	{
		if($amount<0)
			throw new Exception('Cannot take negative SDs.');
		$this->setSDs($this->getSDs() - $amount);
	}
	
	public function setMines($amount)
	{
		if($amount<0)
			throw new Exception('Cannot set negative mines.');
		if($amount==$this->getMines())
			return;
		$this->hasChanged = true;
		$this->mines = $amount;
	}
	
	public function setCDs($amount)
	{
		if($amount<0)
			throw new Exception('Cannot set negative CDs.');
		if($amount==$this->getCDs())
			return;
		$this->hasChanged = true;
		$this->combatDrones = $amount;
	}
	
	public function setSDs($amount)
	{
		if($amount<0)
			throw new Exception('Cannot set negative SDs.');
		if($amount==$this->getSDs())
			return;
		$this->hasChanged = true;
		$this->scoutDrones = $amount;
	}
	
	public function hasExpired()
	{
		return $this->expire<TIME;
	}
	
	public function getExpire()
	{
		return $this->expire;
	}
	
	public function setExpire($time)
	{
		if($time<0)
			throw new Exception('Cannot set negative expiry.');
		if($time==$this->getExpire())
			return;
		if($time > TIME + self::MAX_EXPIRE_TIME)
			$time = self::MAX_EXPIRE_TIME + TIME;
		$this->hasChanged = true;
		$this->expire = $time;
	}
	
	public function updateExpire()
	{
		// Changed (26/10/05) - scout drones count * 2
		if($this->getCDs() == 0 && $this->getMines() == 0 && $this->getSDs() >= 1)
		{
			$time = self::TIME_PER_SCOUT_ONLY*$this->getSDs();
		}
		else
		{
			$time = $this->getCDs()*self::TIME_PER_COMBAT  + $this->getSDs()*self::TIME_PER_SCOUT + $this->getMines()*self::TIME_PER_MINE;
		}
		$this->setExpire(TIME + $time);
	}
	
	public function getOwnerID()
	{
		return $this->ownerID;
	}
	
	public function getGameID()
	{
		return $this->gameID;
	}
	
	public function getSectorID()
	{
		return $this->sectorID;
	}
	
	public function ping($pingMessage, AbstractSmrPlayer &$playerPinging)
	{
		$owner =& $this->getOwner();
		if(!$playerPinging->sameAlliance($owner))
		{
			$playerPinging->sendMessage($owner->getAccountID(), MSG_SCOUT, $pingMessage);
		}
	}

	function &getOwner()
	{
		return SmrPlayer::getPlayer($this->getOwnerID(),$this->getGameID());
	}

	public function update()
	{
		if(!$this->isNew)
		{
			if (!$this->exists())
			{
				$this->db->query('DELETE FROM sector_has_forces ' .
							 'WHERE game_id = '.$this->gameID.' AND ' .
								   'sector_id = '.$this->sectorID.' AND ' .
								   'owner_id = '.$this->ownerID);
			}
			else
			{
				$this->db->query('UPDATE sector_has_forces SET combat_drones = '.$this->combatDrones.', scout_drones = '.$this->scoutDrones.', mines = '.$this->mines.', expire_time = '.$this->expire .
						' WHERE game_id = '.$this->gameID.' AND sector_id = '.$this->sectorID.' AND owner_id = '.$this->ownerID.' LIMIT 1 ');
			}
		}
		else if($this->exists())
		{
			$this->db->query('INSERT INTO sector_has_forces (game_id, sector_id, owner_id, combat_drones, scout_drones, mines, expire_time) ' .
												  'VALUES('.$this->gameID.', '.$this->sectorID.', '.$this->ownerID.', '.$this->combatDrones.', '.$this->scoutDrones.', '.$this->mines.', '.$this->expire.')');
		}
	}
	
	public function getExamineDropForcesHREF()
	{
		if(!isset($this->examineDropForcesHREF))
		{
			$container = array();
			$container['url']		= 'skeleton.php';
			$container['body']		= 'forces_drop.php';
			$container['owner_id']	= $this->getOwnerID();
			$this->examineDropForcesHREF = SmrSession::get_new_href($container);
		}
		return $this->examineDropForcesHREF;
	}
	
	public function getExamineAttackForcesHREF()
	{
		if(!isset($this->examineAttackForcesHREF))
		{
			$container = array();
			$container['url']		= 'skeleton.php';
			$container['body']		= 'forces_examine.php';
			$container['owner_id']	= $this->getOwnerID();
			$this->examineAttackForcesHREF = SmrSession::get_new_href($container);
		}
		return $this->examineAttackForcesHREF;
	}
	
	public function getAttackForcesHREF()
	{
		if(!isset($this->attackForcesHREF))
		{
			$container = array();
			$container['url']		= 'skeleton.php';
			$container['body']		= 'forces_attack_processing.php';
			$container['owner_id']	= $this->getOwnerID();
			$this->attackForcesHREF = SmrSession::get_new_href($container);
		}
		return $this->attackForcesHREF;
	}
	
	public function getRefreshHREF()
	{
		if(!isset($this->refreshHREF))
		{
			$container = array();
			$container['url']		= 'forces_refresh_processing.php';
			$container['owner_id']	= $this->getOwnerID();
			$this->refreshHREF = SmrSession::get_new_href($container);
		}
		return $this->refreshHREF;
	}
	
	public function getDropSDHREF()
	{
		if(!isset($this->dropSDHREF))
		{
			$container = array();
			$container['url']		= 'forces_drop_processing.php';
			$container['owner_id']	= $this->getOwnerID();
			$container['drop_scout_drones']	= 1;
			$this->dropSDHREF = SmrSession::get_new_href($container);
		}
		return $this->dropSDHREF;
	}
	
	public function getTakeSDHREF()
	{
		if(!isset($this->takeSDHREF))
		{
			$container = array();
			$container['url']		= 'forces_drop_processing.php';
			$container['owner_id']	= $this->getOwnerID();
			$container['take_scout_drones']	= 1;
			$this->takeSDHREF = SmrSession::get_new_href($container);
		}
		return $this->takeSDHREF;
	}
	
	public function getDropCDHREF()
	{
		if(!isset($this->dropCDHREF))
		{
			$container = array();
			$container['url']		= 'forces_drop_processing.php';
			$container['owner_id']	= $this->getOwnerID();
			$container['drop_combat_drones']	= 1;
			$this->dropCDHREF = SmrSession::get_new_href($container);
		}
		return $this->dropCDHREF;
	}
	
	public function getTakeCDHREF()
	{
		if(!isset($this->takeCDHREF))
		{
			$container = array();
			$container['url']		= 'forces_drop_processing.php';
			$container['owner_id']	= $this->getOwnerID();
			$container['take_combat_drones']	= 1;
			$this->takeCDHREF = SmrSession::get_new_href($container);
		}
		return $this->takeCDHREF;
	}
	
	public function getDropMineHREF()
	{
		if(!isset($this->dropMineHREF))
		{
			$container = array();
			$container['url']		= 'forces_drop_processing.php';
			$container['owner_id']	= $this->getOwnerID();
			$container['drop_mines']	= 1;
			$this->dropMineHREF = SmrSession::get_new_href($container);
		}
		return $this->dropMineHREF;
	}
	
	public function getTakeMineHREF()
	{
		if(!isset($this->takeMineHREF))
		{
			$container = array();
			$container['url']		= 'forces_drop_processing.php';
			$container['owner_id']	= $this->getOwnerID();
			$container['take_mines']	= 1;
			$this->takeMineHREF = SmrSession::get_new_href($container);
		}
		return $this->takeMineHREF;
	}
	
	public static function getRefreshAllHREF()
	{
		if(!isset(self::$refreshAllHREF))
		{
			$container = array();
			$container['url']		= 'forces_mass_refresh.php';
			self::$refreshAllHREF	= SmrSession::get_new_href($container);
		}
		return self::$refreshAllHREF;
	}
	
	public function &shootPlayer(AbstractSmrPlayer &$targetPlayer,$justMines)
	{
		return $this->shootPlayers(array($targetPlayer),$justMines);
	}
	
	public function &shootPlayers(array &$targetPlayers,$minesAreAttacker)
	{
		$results = array('TotalDamage' => 0);
		if(!$this->exists())
		{
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		
		
		if($this->hasMines())
		{
			require_once(get_file_loc('SmrMines.class.inc'));
			$thisMines = new SmrMines($this->getGameID(),$this->getMines());
			$results['Results']['Mines'] =& $thisMines->shootPlayerAsForce($this, $targetPlayers[array_rand($targetPlayers)],$minesAreAttacker);
			$results['TotalDamage'] += $results['Results']['Mines']['ActualDamage']['TotalDamage'];
		}
		
		if(!$minesAreAttacker)
		{
			if($this->hasCDs())
			{
				require_once(get_file_loc('SmrCombatDrones.class.inc'));
				$thisCDs = new SmrCombatDrones($this->getGameID(),$this->getCDs());
				$results['Results']['Drones'] =& $thisCDs->shootPlayerAsForce($this, $targetPlayers[array_rand($targetPlayers)]);
				$results['TotalDamage'] += $results['Results']['Drones']['ActualDamage']['TotalDamage'];
			}
	
			if($this->hasSDs())
			{
				require_once(get_file_loc('SmrScoutDrones.class.inc'));
				$thisSDs = new SmrScoutDrones($this->getGameID(),$this->getSDs());
				$results['Results']['Scouts'] =& $thisSDs->shootPlayerAsForce($this, $targetPlayers[array_rand($targetPlayers)]);
				$results['TotalDamage'] += $results['Results']['Scouts']['ActualDamage']['TotalDamage'];
			}
		}
		
		$results['ForcesDestroyed'] = !$this->exists();
		return $results;
	}
	
	public function &doWeaponDamage(array $damage)
	{
		$alreadyDead = !$this->exists();
		$minesDamage = 0;
		$cdDamage = 0;
		$sdDamage = 0;
		if(!$alreadyDead)
		{
			$minesDamage = $this->doMinesDamage(min($damage['MaxDamage'],$damage['Armour']));
			$damage['Armour'] -= $minesDamage;
			$damage['MaxDamage'] -= $minesDamage;
			if(!$this->hasMines() && ($minesDamage == 0 || $damage['Rollover']))
			{
				$cdDamage = $this->doCDDamage(min($damage['MaxDamage'],$damage['Armour']));
				$damage['Armour'] -= $cdDamage;
				$damage['MaxDamage'] -= $cdDamage;
				if(!$this->hasCDs() && ($cdDamage == 0 || $damage['Rollover']))
					$sdDamage = $this->doSDDamage(min($damage['MaxDamage'],$damage['Armour']));
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
	
	protected function doMinesDamage($damage)
	{
		$actualDamage = min($this->getMines(),floor($damage/MINE_ARMOUR));
		$this->takeMines($actualDamage);
		return $actualDamage*MINE_ARMOUR;
	}
	
	protected function doCDDamage($damage)
	{
		$actualDamage = min($this->getCDs(),floor($damage/CD_ARMOUR));
		$this->takeCDs($actualDamage);
		return $actualDamage*CD_ARMOUR;
	}
	
	protected function doSDDamage($damage)
	{
		$actualDamage = min($this->getSDs(),floor($damage/SD_ARMOUR));
		$this->takeSDs($actualDamage);
		return $actualDamage*SD_ARMOUR;
	}
	
	public function &killForcesByPlayer(AbstractSmrPlayer &$killer)
	{
		$return = array();
		return $return;
	}
}
?>