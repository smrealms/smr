<?php
require_once(get_file_loc('AbstractSmrPlayer.class.inc'));
require_once(get_file_loc('council.inc'));
class SmrPlayer extends AbstractSmrPlayer
{
	const TIME_FOR_ALLIANCE_SWITCH = 86400;
	protected static $CACHE_SECTOR_PLAYERS = array();
	protected static $CACHE_PLANET_PLAYERS = array();
	protected static $CACHE_ALLIANCE_PLAYERS = array();
	protected static $CACHE_PLAYERS = array();
	
	protected $db;
	
	protected $newbieWarning;


	protected $tickers;

	protected $originalMaintenance;
	protected $lastTurnUpdate;
	protected $lastShipMod;
	protected $lastNewsUpdate;
	protected $stunned;
	protected $fleed;
	protected $attackColor;
	protected $kicked;
//	protected $pastKnowledge;
	protected $governmentHelp;
	protected $allianceJoinable;
	protected $lastPort;
	protected $bank;
	protected $zoom;
//	protected $zoomOn;
//	protected $chat;
//	protected $chatPosition;
	protected $displayMissions;
	protected $displayWeapons;
	protected $useAJAX;
	protected $ignoreGlobals;
//	protected $changedStats;
	protected $mining;
	protected $plottedCourse;
	protected $nameChanged;
	protected $combatDronesKamikazeOnMines;

	protected $customShipName;

	protected $SQL;
	
//	public static function getPlayers($gameAccountIDArray,$forceUpdate = false)
//	{
//		global $CACHE_PLAYERS;
//		$playerArray = array();
//		if(!$forceUpdate)
//		{
//			foreach($gameAccountIDArray as $gameAccountID)
//			{
//				$playerArray[$gameAccountID['Account ID']] = $CACHE_PLAYERS[$gameAccountID['Game ID'].$gameAccountID['Account ID']];
//			}
//		}
//	}
	
	public static function refreshCache()
	{
		foreach(self::$CACHE_PLAYERS as $gameID => &$gamePlayers)
		{
			foreach($gamePlayers as $accountID => &$player)
			{
				$player = self::getPlayer($accountID,$gameID,true);
			}
		}
	}
	
	public static function clearCache()
	{
		self::$CACHE_PLAYERS = array();
		self::$CACHE_SECTOR_PLAYERS = array();
	}
	
	public static function savePlayers()
	{
		foreach(self::$CACHE_PLAYERS as &$gamePlayers)
		{
			foreach($gamePlayers as &$player)
			{
				$player->save();
			}
		}
	}

	public static function &getSectorPlayersByAlliances($gameID,$sectorID, array $allianceIDs, $forceUpdate = false)
	{
		$players = self::getSectorPlayers($gameID,$sectorID,$forceUpdate); // Don't use & as we do an unset
		foreach($players as $accountID => &$player)
		{
			if(!in_array($player->getAllianceID(),$allianceIDs))
				unset($players[$accountID]);
		}
		return $players;
	}

	public static function &getSectorPlayers($gameID,$sectorID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID]))
		{
			$db = new SmrMySqlDatabase();
			$db->query('SELECT account_id FROM player WHERE sector_id = ' . $sectorID . ' AND game_id=' . $gameID . ' AND land_on_planet = ' . $db->escapeBoolean(false) . ' AND (last_cpl_action > '.INACTIVE_TIME.' OR newbie_turns = 0) AND account_id NOT IN (' . implode(',', Globals::getHiddenPlayers()) . ') ORDER BY last_cpl_action DESC');
			$players = array();
			while($db->nextRecord())
			{
				$accountID = $db->getField('account_id');
				$players[$accountID] =& self::getPlayer($accountID,$gameID,$forceUpdate);
			}
			return self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID] =& $players;
		}
		return self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID];
	}

	public static function &getPlanetPlayers($gameID,$sectorID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_PLANET_PLAYERS[$gameID][$sectorID]))
		{
			$db = new SmrMySqlDatabase();
			$db->query('SELECT account_id FROM player WHERE sector_id = ' . $sectorID . ' AND game_id=' . $gameID . ' AND land_on_planet = ' . $db->escapeBoolean(true) . ' AND account_id NOT IN (' . implode(',', Globals::getHiddenPlayers()) . ') ORDER BY last_cpl_action DESC');
			$players = array();
			while($db->nextRecord())
			{
				$accountID = $db->getField('account_id');
				$players[$accountID] =& self::getPlayer($accountID,$gameID,$forceUpdate);
			}
			return self::$CACHE_PLANET_PLAYERS[$gameID][$sectorID] =& $players;
		}
		return self::$CACHE_PLANET_PLAYERS[$gameID][$sectorID];
	}

	public static function &getAlliancePlayers($gameID,$allianceID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_ALLIANCE_PLAYERS[$gameID][$allianceID]))
		{
			$db = new SmrMySqlDatabase();
			$db->query('SELECT account_id FROM player WHERE alliance_id = ' . $allianceID . ' AND game_id=' . $gameID .' ORDER BY experience DESC');
			$players = array();
			while($db->nextRecord())
			{
				$accountID = $db->getField('account_id');
				$players[$accountID] =& self::getPlayer($accountID,$gameID);
			}
			return self::$CACHE_ALLIANCE_PLAYERS[$gameID][$allianceID] =& $players;
		}
		return self::$CACHE_ALLIANCE_PLAYERS[$gameID][$allianceID];
	}

	public static function &getPlayer($accountIDOrResultArray,$gameID,$forceUpdate = false)
	{
		if($forceUpdate || is_array($accountIDOrResultArray) || !isset(self::$CACHE_PLAYERS[$gameID][$accountIDOrResultArray]))
		{
			$p = new SmrPlayer($gameID,$accountIDOrResultArray);
			return self::$CACHE_PLAYERS[$gameID][$p->getAccountID()] =& $p;
		}
		return self::$CACHE_PLAYERS[$gameID][$accountIDOrResultArray];
	}

	public static function &getPlayerByPlayerID($playerID,$gameID,$forceUpdate = false)
	{
		$db = new SmrMySqlDatabase();
		$db->query('SELECT account_id FROM player WHERE game_id = '.$db->escapeNumber($gameID).' AND player_id = '.$db->escapeNumber($playerID).' LIMIT 1');
		if($db->nextRecord())
			return self::getPlayer($db->getField('account_id'),$gameID,$forceUpdate);
		throw new Exception('Player ID not found.');
	}
	
	protected function __construct($gameID,&$accountIDOrResultArray)
	{
		parent::__construct();
		$this->db = new SmrMySqlDatabase();
		$result=false;
		if (is_array($accountIDOrResultArray))
			$result =& $accountIDOrResultArray;
		else
		{
			$this->db->query('SELECT * FROM player WHERE account_id = '.$accountIDOrResultArray.' AND game_id = '.$gameID.' LIMIT 1');
			$this->db->nextRecord();
			$result = $this->db->getRow();
		}
		if($result)
		{
			$this->accountID					= (int) $result['account_id'];
			$this->gameID						= (int) $result['game_id'];
			$this->playerName					= (string) $result['player_name'];
			$this->playerID						= (int) $result['player_id'];
			$this->sectorID						= (int) $result['sector_id'];
			$this->lastSectorID					= (int) $result['last_sector_id'];
			$this->turns						= (int) $result['turns'];
			$this->originalMaintenance			= $this->turns;
			$this->lastTurnUpdate				= (int) $result['last_turn_update'];
			$this->newbieTurns					= (int) $result['newbie_turns'];
			$this->lastShipMod					= (int) $result['last_ship_mod'];
			$this->lastNewsUpdate				= (int) $result['last_news_update'];
			$this->stunned						= (int) $result['stunned'];
			$this->fleed						= $result['fleed']=='TRUE';
			$this->attackColor					= (string) $result['attack_warning'];
			$this->dead							= $result['dead']=='TRUE';
			$this->startDead					= $this->dead;
			$this->landedOnPlanet				= $result['land_on_planet']=='TRUE';
			$this->kicked						= $result['kicked']=='TRUE';
			$this->lastShieldUpdate				= (int) $result['last_shield_update'];
			$this->lastActive					= (int) $result['last_active'];
			$this->lastCPLAction				= (int) $result['last_cpl_action'];
			$this->raceID						= (int) $result['race_id'];
			$this->credits						= (int) $result['credits'];
			$this->experience					= (int) $result['experience'];
			$this->alignment					= (int) $result['alignment'];
			$this->militaryPayment				= (int) $result['military_payment'];
//			$this->pastKnowledge = $result['past_knowledge'];
			$this->governmentHelp				= (int) $result['government_help'];
			$this->allianceID					= (int) $result['alliance_id'];
			$this->allianceJoinable				= (int) $result['alliance_join'];
			$this->shipID						= (int) $result['ship_type_id'];
			$this->kills						= (int) $result['kills'];
			$this->deaths						= (int) $result['deaths'];
			$this->lastPort						= (int) $result['last_port'];
			$this->bank							= (int) $result['bank'];
			$this->zoom							= (int) $result['zoom'];
//			$this->zoomOn = $result['zoom_on']=='TRUE';
//			$this->chat = $result['chat'];
//			$this->chatPosition = $result['chat_position'];
			$this->displayMissions				= $result['display_missions']=='TRUE';
			$this->displayWeapons				= $result['display_weapons']=='TRUE';
			$this->forceDropMessages			= $result['force_drop_messages']=='TRUE';
			$this->ignoreGlobals				= $result['ignore_globals']=='TRUE';
			$this->newbieWarning				= $result['newbie_warning']=='TRUE';
			$this->nameChanged					= $result['name_changed']=='TRUE';
			$this->combatDronesKamikazeOnMines	= $result['combat_drones_kamikaze_on_mines']=='TRUE';
//			$this->changedStats = $result['changed_stats'];
			
			$this->SQL = 'account_id = '.$this->accountID.' AND game_id = '.$this->gameID;
		}
		else
		{
			throw new Exception('Invalid accountID: '.$accountIDOrResultArray . ' OR gameID:'.$gameID);
		}
	}
	
	public function getSQL()
	{
		return $this->SQL;
	}
	
	function get($bit)
	{
		switch($bit)
		{
			case 'Sector ID':
				return $this->sectorID;
			case 'Last Sector ID':
				return $this->lastSectorID;
			case 'Player Name':
				return $this->playerName;
			case 'Player ID':
				return $this->playerID;
			case 'Land On Planet':
				return $this->landedOnPlanet;
			case 'Kicked':
				return $this->kicked;
			case 'Newbie Turns':
				return $this->newbieTurns;
			case 'Race ID':
				return $this->raceID;
			case 'Credits':
				return $this->credits;
			case 'Experience':
				return $this->experience;
			case 'Kills':
				return $this->kills;
			case 'Deaths':
				return $this->deaths;
			case 'Alignment':
				return $this->alignment;
//			case 'Past Knowledge':
//				return $this->pastKnowledge;
			case 'Government Help':
				return $this->governmentHelp;
			case 'Alliance ID':
				return $this->allianceID;
			case 'Alliance Joinable':
				return $this->allianceJoinable;
			case 'Ship ID':
				return $this->shipID;
			case 'Dead':
				return $this->dead;
			case 'Last Port':
				return $this->lastPort;
			case 'Last News':
				return $this->lastNewsUpdate;
			case 'Last Ship Mod':
				return $this->lastShipMod;
			case 'Default Zoom':
				return $this->zoom;
//			case 'Default Zoom Toggle':
//				return $this->zoomOn;
			case 'Bank':
				return $this->bank;
			case 'Maintenance':
			case 'Turns':
				return $this->turns;
			case 'Last Maintenance Update':
			case 'Last Turn Update':
				return $this->lastTurnUpdate;
			case 'Last Active':
				return $this->lastActive;
			case 'Last CPL Action':
				return $this->lastCPLAction;
			case 'Last Shield Regen':
				return $this->lastShieldUpdate;
			case 'Stunned':
				return $this->stunned;
			case 'Fleed':
				return $this->fleed;
			case 'Attack Color':
				return $this->attackWarning;
//			case 'Chat':
//				return $this->chat;
//			case 'Chat Position':
//				return $this->chatPosition;
			case 'Display Missions':
				return $this->displayMissions;
			case 'Ignore Global':
				return $this->ignoreGlobals;
			case 'Level ID':
				return $this->getLevelID();
			case 'Display Name':
				return $this->getDisplayName();
			case 'Span Color':
				return $this->getSpanColour();
//			case 'Ship Size':
//				return $this->getShipSize();
				
			case 'Stats':
				return $this->getStats();
			case 'Erebus Knowledge':
				return $this->getKnowledge('Erebus');
			case 'Nyx Knowledge':
				return $this->getKnowledge('Nyx');
			case 'Tartarus Knowledge':
				return $this->getKnowledge('Tartarus');
			case 'Aether Knowledge':
				return $this->getKnowledge('Aether');
			default:
				throw new Exception('Player->get('.$bit.') not found, report to Page.');
		}
	}
	
	public function &getShip()
	{
		return SmrShip::getShip($this);
	}
	
	public function &getAccount()
	{
		require_once(get_file_loc('SmrAccount.class.inc'));
		return SmrAccount::getAccount($this->getAccountID());
	}
	
	public function getKicked()
	{
		return $this->kicked;
	}
	
	public function setKicked($bool)
	{
		if($this->kicked == $bool)
			return;
		$this->kicked=$bool;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET kicked = '.$this->db->escapeBoolean($bool).' WHERE ' . $this->SQL . ' LIMIT 1');
	}
	
	function getStunned()
	{
		return $this->stunned;
	}
	
	function setStunned($time)
	{
		if($this->stunned == $time)
			return;
		$this->stunned=$time;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET stunned = ' . $this->stunned . ' WHERE ' . $this->SQL . ' LIMIT 1');
	}
	
	function getFleed()
	{
		return $this->fleed;
	}
	
	function setFleed($bool)
	{
		if($this->fleed == $bool)
			return;
		$this->fleed=$bool;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET fleed='.$this->db->escapeBoolean($bool).' WHERE ' . $this->SQL . ' LIMIT 1');
	}
	
	function getZoom()
	{
		return $this->zoom;
	}
	
	function setZoom($zoom)
	{
		if ($zoom < 0) $zoom = 0;
		if ($zoom > 12) $zoom = 12;
		if($this->zoom == $zoom)
			return;
		$this->zoom=$zoom;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET zoom = ' . $zoom . ' WHERE ' . $this->SQL . ' LIMIT 1');
	}
	
	function increaseZoom($zoom)
	{
		if ($zoom < 0)
			throw new Exception('Trying to increase negative zoom.');
		$this->setZoom($this->getZoom()+$zoom);
	}
	
	function decreaseZoom($zoom)
	{
		if ($zoom < 0)
			throw new Exception('Trying to decrease negative zoom.');
		$this->setZoom($this->getZoom()-$zoom);
	}
	
//	function isZoomOn()
//	{
//		return $this->zoomOn;
//	}

	public function setSectorID($sectorID)
	{
		require_once('SmrPort.class.inc');
		$port =& SmrPort::getPort($this->getGameID(),$this->getSectorID());
		$port->addCachePort($this->getAccountID()); //Add port of sector we were just in, to make sure it is left totally up to date.
		
		parent::setSectorID($sectorID);
		
		$port =& SmrPort::getPort($this->getGameID(),$sectorID);
		$port->addCachePort($this->getAccountID()); //Add the port of sector we are now in.
	}
	
	function setLastSectorID($lastSectorID)
	{
		if($this->lastSectorID == $lastSectorID)
			return;
		$this->lastSectorID=$lastSectorID;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET last_sector_id = '.$this->lastSectorID.' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	function leaveAlliance(AbstractSmrPlayer $kickedBy = null)
	{
		$allianceID = $this->getAllianceID();
		$alliance = $this->getAlliance();
		if($kickedBy != null)
		{
			$kickedBy->sendMessage($this->getAccountID(), 2, 'You were kicked out of the alliance!', false);
			$this->actionTaken('PlayerKicked', array('Alliance' => &$alliance, 'Player' => &$kickedBy));
			$kickedBy->actionTaken('KickPlayer', array('Alliance' => &$alliance, 'Player' => &$this));
		}
		else if($this->isAllianceLeader())
		{
			$this->actionTaken('DisbandAlliance', array('Alliance' => &$alliance));
		}
		else
		{
			$this->actionTaken('LeaveAlliance', array('Alliance' => &$alliance));
			if ($alliance->getLeaderID() != 0 && $alliance->getLeaderID() != ACCOUNT_ID_NHL)
			{
				$this->sendMessage($alliance->getLeaderID(), 2, 'I left your alliance!', false);
			}
		}
			
		$this->setAllianceID(0);
		$this->db->query('DELETE
					FROM player_has_alliance_role
					WHERE account_id = '.$this->getAccountID().' AND
						  game_id = '.$this->getGameID());
	
		if(!$this->isAllianceLeader() && $allianceID!=NHA_ID) // Don't have a delay for switching alliance after leaving NHA, or for disbanding an alliance.
		{
			$this->setAllianceJoinable(TIME + self::TIME_FOR_ALLIANCE_SWITCH);
			$alliance->getLeader()->setAllianceJoinable(TIME + self::TIME_FOR_ALLIANCE_SWITCH); //We set the joinable time for leader here, that way a single player alliance won't cause a player to wait before switching.
		}
	}
	
	function joinAlliance($allianceID)
	{
		$this->setAllianceID($allianceID);
		$alliance = $this->getAlliance();
		if (!$this->isAllianceLeader())
			$this->sendMessage($alliance->getLeaderID(), 2, 'I joined your alliance!', false);
		$this->db->query('INSERT INTO player_has_alliance_role (game_id, account_id, role_id, alliance_id) VALUES ('.$this->getGameID().', '.$this->getAccountID().', 2,'.$alliance->getAllianceID().')');
		$this->actionTaken('JoinAlliance', array('Alliance' => &$alliance));
	}
	
	function getAllianceJoinable()
	{
		return $this->allianceJoinable;
	}
	
	function setAllianceJoinable($time)
	{
		if($this->allianceJoinable == $time)
			return;
		$this->allianceJoinable=$time;
		$this->hasChanged=true;
	}
	
	function getAttackColor()
	{
		return $this->attackColor;
	}
	
	function setAttackColor($colour)
	{
		if($this->attackColor == $colour)
			return;
		$this->attackColor=$colour;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET attack_warning = ' . $this->db->escapeString($this->attackColor) . ' WHERE ' . $this->SQL . ' LIMIT 1');
	}
	
	function getBank()
	{
		return $this->bank;
	}

	function increaseBank($credits)
	{
		if($credits < 0)
			throw new Exception('Trying to increase negative credits.');
		if($credits == 0)
			return;
		$credits += $this->bank;
		$this->setBank($credits);
	}
	function decreaseBank($credits)
	{
		if($credits < 0)
			throw new Exception('Trying to decrease negative credits.');
		if($credits == 0)
			return;
		$credits = $this->bank - $credits;
		$this->setBank($credits);
	}
	function setBank($credits)
	{
		if($this->bank == $credits)
			return;
		if (has_privilege('Money Doesn\'t Matter',$this->accountID)) return;
		if($credits < 0)
			throw new Exception('Trying to set negative credits.');
		if($credits > MAX_MONEY)
			throw new Exception('Trying to set more than max credits.');
		$this->bank = $credits;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET bank = '.$this->bank.' WHERE '.$this->SQL.' LIMIT 1');
	}

	function getLastNewsUpdate()
	{
		return $this->lastNewsUpdate;
	}

	function setLastNewsUpdate($time)
	{
		if($this->lastNewsUpdate == $time)
			return;
		$this->lastNewsUpdate=$time;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET last_news_update = ' . $time . ' WHERE ' . $this->SQL . ' LIMIT 1');
	}

	function updateLastNewsUpdate()
	{
		$this->setLastNewsUpdate(TIME);
	}

	function getGovernmentHelp()
	{
		return $this->governmentHelp;
	}

//	function getPastKnowledge()
//	{
//		return $this->pastKnowledge;
//	}
	
//	function getChangedStats()
//	{
//		return $this->changedStats;
//	}
//
//	function setChangedStats($boolStr)
//	{
//		if($this->changedStats == $boolStr)
//			return;
//		$this->changedStats=$boolStr;
//		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET changed_stats = '.$this->db->escapeString($boolStr).' WHERE '.$this->SQL.' LIMIT 1');
//	}
	
//	function getChat()
//	{
//		return $this->chat;
//	}
//
//	function setChat($chat)
//	{
//		if($this->chat == $chat)
//			return;
//		$this->chat=$chat;
//		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET chat = ' . $this->db->escapeString($chat) . ' WHERE ' . $this->SQL . ' LIMIT 1');
//	}
//
//	function getChatPosition()
//	{
//		return $this->chatPosition;
//	}
//
//	function setChatPosition($chatPosition)
//	{
//		if($this->chatPosition == $chatPosition)
//			return;
//		$this->chatPosition=$chatPosition;
//		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET chat_position = ' . $this->db->escapeString($chatPosition) . ' WHERE ' . $this->SQL . ' LIMIT 1');
//	}
	
	function getOriginalMaintenance()
	{
		return $this->originalMaintenance;
	}
	
	public function updateTurns()
	{
		// is account validated?
		if (!$this->getAccount()->isValidated()) return;
		$ship =& $this->getShip();
		
		// update turns?
		$time_diff = TIME - $this->getLastTurnUpdate();
		
		// how many turns would he get right now?
		$extraTurns = floor($time_diff * $ship->getRealSpeed() / 3600);
		
		// do we have at least one turn to give?
		if ($extraTurns > 0)
		{
			// recalc the time to avoid errors
			$newLastTurnUpdate = $this->getLastTurnUpdate() + ceil($extraTurns * 3600 / $ship->getRealSpeed());
		
			$startTurnsDate = $this->getGame()->getStartTurnsDate();
			if($startTurnsDate < $newLastTurnUpdate && $startTurnsDate >= $this->getLastTurnUpdate())
			{
				$startingTurns = min($ship->getRealSpeed() * $this->getGame()->getStartTurnHours(), $this->getMaxTurns() - $this->getTurns());
				$extraUpdateTime = (ceil($startingTurns) - $startingTurns) * 3600 / $ship->getRealSpeed();
				$extraTurns += $startingTurns;
				$newLastTurnUpdate += $extraUpdateTime;
			}
			
			$this->setLastTurnUpdate($newLastTurnUpdate);
			
			
			$this->giveTurns($extraTurns);
		}
	}
		
	
	function isIgnoreGlobals()
	{
		return $this->ignoreGlobals;
	}
	
	function setIgnoreGlobals($bool)
	{
		if($this->ignoreGlobals == $bool)
			return;
		$this->ignoreGlobals=$bool;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET ignore_globals = '.$this->db->escapeBoolean($bool).' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	
	function getLastPort()
	{
		return $this->lastPort;
	}
	
	function setLastPort($lastPort)
	{
		if($this->lastPort == $lastPort)
			return;
		$this->lastPort=$lastPort;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET last_port = ' . $this->lastPort . ' WHERE ' . $this->SQL . ' LIMIT 1');
	}
	
	function isDisplayMissions()
	{
		return $this->displayMissions;
	}
	
	function setDisplayMissions($bool)
	{
		if($this->displayMissions == $bool)
			return;
		$this->displayMissions=$bool;
		$this->hasChanged=true;
	}
	
	function isDisplayWeapons()
	{
		return $this->displayWeapons;
	}
	
	function setDisplayWeapons($bool)
	{
		if($this->displayWeapons == $bool)
			return;
		$this->displayWeapons=$bool;
		$this->hasChanged=true;
	}
	
	function isForceDropMessages()
	{
		return $this->forceDropMessages;
	}
	
	function setForceDropMessages($bool)
	{
		if($this->forceDropMessages == $bool)
			return;
		$this->forceDropMessages=$bool;
		$this->hasChanged=true;
	}
	
	function getLastTurnUpdate()
	{
		return $this->lastTurnUpdate;
	}
	
	function setLastTurnUpdate($time)
	{
		if($this->lastTurnUpdate == $time)
			return;
		$this->lastTurnUpdate=$time;
		$this->hasChanged=true;
//		$sql = $this->db->query('UPDATE player SET last_turn_update = ' . $this->lastTurnUpdate . ' WHERE '. $this->SQL . ' LIMIT 1');
	}
	
	function getLastShipMod()
	{
		return $this->lastShipMod;
	}
	
	function setLastShipMod($time)
	{
		if($this->lastShipMod == $time)
			return;
		$this->lastShipMod = $time;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET last_ship_mod = ' . $time . ' WHERE ' . $this->SQL . ' LIMIT 1');
	}
	
	function getPastMaintBought()
	{
		if(!isset($this->pastMaintBought))
		{
			//get past maint
			$this->db->query('DELETE FROM player_repaired WHERE time < '.(TIME - 3600 * 20));
			$this->db->query('SELECT sum(amount) as sum FROM player_repaired WHERE ' . $this->SQL . ' AND amount > 0 LIMIT 1');
			if ($this->db->nextRecord()) $this->pastMaintBought = $this->db->getField('sum');
			else $this->pastMaintBought = 0;
		}
		return $this->pastMaintBought;
	}
	
	function getPastMaintCredit()
	{
		if(!isset($this->pastMaintCredit))
		{
			//get past maint
			$this->db->query('DELETE FROM player_repaired WHERE time < '.(TIME - 3600 * 20));
			$this->db->query('SELECT sum(amount) as sum FROM player_repaired WHERE '.$this->SQL.' AND amount < 0 LIMIT 1');
			if ($this->db->nextRecord()) $this->pastMaintCredit = $this->db->getField('sum');
			else $this->pastMaintCredit = 0;
		}
		return $this->pastMaintCredit;
	}
		
	function removePastMaint()
	{
		$this->pastMaint=0;
		$this->db->query('DELETE FROM player_repaired WHERE '.$this->SQL);
	}
	
	function addPastMaintBought($amount,$type='Normal')
	{
		$this->getPastMaintBought();
		$this->db->query('REPLACE INTO player_repaired (account_id, game_id, time, amount, source) VALUES (' . $this->accountID . ',' . $this->gameID . ',' . TIME . ',' . $amount . ','.$this->db->escapeString($type).')');
		$this->giveTurns($amount*10);
		$this->pastMaintBought+=$amount;
	}
	
	protected function getStatsData()
	{
		if(!isset($this->stats))
		{
//			//initialize stats
//			global $STATS;
//			foreach ($STATS as $stat_name => $statArray)
//				$temp_stats[$statArray['ID']] = 0;
//			$this->db->query('SELECT stat_type_id, amount FROM player_has_special WHERE ' . $this->SQL . ' LIMIT ' . sizeof($STATS));
//			while ($this->db->nextRecord())
//				$temp_stats[$this->db->getField('stat_type_id')] = $this->db->getField('amount');
			//put stats in a more user friendly form
			$this->stats = array();
//			foreach ($STATS as $stat_name => $statArray)
//				$this->stats[$stat_name] = $temp_stats[$statArray['ID']];
		}
	}
	
	function deleteStats()
	{
		$this->getStats();
		$this->db->query('DELETE FROM player_has_special WHERE '.$this->SQL.' LIMIT '.count($this->stats));
		unset($this->stats);
	}
	
	protected function getPureRelationsData()
	{
		if(!isset($this->pureRelations))
		{
			//get relations
			$RACES = Globals::getRaces();
			$this->pureRelations = array();
			foreach ($RACES as $race_id => $race_name)
			{
				$this->pureRelations[$race_id] = 0;
			}
			$this->db->query('SELECT race_id,relation FROM player_has_relation WHERE '.$this->SQL.' LIMIT '.sizeof($RACES));
			while ($this->db->nextRecord())
			{
				$this->pureRelations[$this->db->getInt('race_id')] = $this->db->getInt('relation');
			}
		}
	}

	function increaseRelationsByTrade($numGoods,$raceID)
	{
		$relations = ceil(min($numGoods,300)/30);
		//Cap relations to a max of 1 after 500 have been reached
		if($this->getPureRelation($raceID) + $relations >= 500)
			$relations = max(1, min($relations, 500 - $this->getPureRelation($raceID)));
		$this->increaseRelations($relations,$raceID);
	}

	function decreaseRelationsByTrade($numGoods,$raceID)
	{
		$relations = ceil(min($numGoods,300)/30);
		$this->decreaseRelations($relations,$raceID);
	}

	function increaseRelations($relations,$raceID)
	{
		if($relations < 0)
			throw new Exception('Trying to increase negative relations.');
		if($relations == 0)
			return;
		$relations += $this->getPureRelation($raceID);
		$this->setRelations($relations,$raceID);
	}
	function decreaseRelations($relations,$raceID)
	{
		if($relations < 0)
			throw new Exception('Trying to decrease negative relations.');
		if($relations == 0)
			return;
		$relations = $this->getPureRelation($raceID) - $relations;
		$this->setRelations($relations,$raceID);
	}
	function setRelations($relations,$raceID)
	{
		$this->getPureRelations();
		if($this->pureRelations[$raceID] == $relations)
			return;
		if($relations < MIN_RELATIONS)
			$relations = MIN_RELATIONS;
		$relationsDiff = $relations - $this->pureRelations[$raceID];
		$this->pureRelations[$raceID] = $relations;
		$this->relations[$raceID] += round($relationsDiff);
		$this->db->query('REPLACE INTO player_has_relation (account_id,game_id,race_id,relation) values ('.$this->getAccountID().','.$this->getGameID().','.$raceID.','.$this->pureRelations[$raceID].')');
	}
	
	public function setPlayerName($playerName)
	{
		$this->playerName=$playerName;
		$this->setNameChanged(true);
		$this->hasChanged=true;
	}
	
	public function isNameChanged()
	{
		return $this->nameChanged;
	}
	
	public function setNameChanged($bool)
	{
		$this->nameChanged = $bool;
		$this->hasChanged=true;
	}
	
	public function hasCustomShipName()
	{
		return $this->getCustomShipName()!==false;
	}
	
	public function getCustomShipName()
	{
		if(!isset($this->customShipName))
		{
			$this->db->query('SELECT * FROM ship_has_name WHERE game_id = '.$this->getGameID().' AND account_id = '.$this->getAccountID().' LIMIT 1');
			if ($this->db->nextRecord())
				$this->customShipName = $this->db->getField('ship_name');
			else
				$this->customShipName = false;
		}
		return $this->customShipName;
	}
	
//	protected function getGoodsInfo()
//	{
//
//		if(!isset($this->goods) || !isset($this->goodsExpGained))
//		{
//			//Get Player Goods
//			global $GOODS;
//			$this->goods = array();
//			$this->goodsExpGained = array();
//			foreach ($GOODS as $good_id => $good_name)
//			{
//				$this->goods[$good_id] = 0;
//				$this->goodsExpGained[$good_id] = 0;
//			}
//			$this->db->query('SELECT good_id,amount,exp_gained FROM player_has_goods WHERE ' . $this->SQL);
//			while ($this->db->nextRecord())
//			{
//				$this->goods[$this->db->getField('good_id')] = $this->db->getField('amount');
//				$this->goodsExpGained[$this->db->getField('good_id')] = $this->db->getField('exp_gained');
//			}
//		}
//	}
//
//	function getGoods()
//	{
//		if(!isset($this->goods))
//		{
//			$this->getGoodsInfo();
//		}
//		return $this->goods;
//	}
//	function getGood($goodID)
//	{
//		$goods = $this->getGoods();
//		return $goods[$goodID];
//	}
//	function getGoodsExpGained()
//	{
//		if(!isset($this->goodsExpGained))
//		{
//			$this->getGoodsInfo();
//		}
//		return $this->goodsExpGained;
//	}
//	function getGoodExpGained($goodID)
//	{
//		$goodsExp = $this->getGoodsExpGained();
//		return $goodsExp[$goodID];
//	}
	
	protected function getGadgetsData()
	{
		if(!isset($this->gadgets))
		{
			//Get Player Gadgets
			$this->db->query('SELECT gadget_id,equipped,cooldown,lasts_until FROM player_has_gadget WHERE ' . $this->SQL);
			$this->gadgets = array();
			while ($this->db->nextRecord())
				$this->gadgets[$this->db->getField('gadget_id')] = array('Equipped' => $this->db->getField('equipped'), 'Cooldown' => $this->db->getField('cooldown'), 'Expires' => $this->db->getField('lasts_until'));
		}
	}
	
	public function setGadgets($keyOrUpdateArray,$value)
	{
		$this->getGadgets();

		if(is_array($keyOrUpdateArray))
		{
			$set = '';
			foreach($keyOrUpdateArray as $key => $newInfo)
			{
				$field = $this->gadgetKeyToField($key);
				if($field!==false)
				{
					foreach($this->gadgets as $gadgetID => $gadget)
					{
						$this->gadgets[$gadgetID][$key] = $newInfo;
					}
					$set .= $field.' = '.$this->db->escapeString($newInfo).',';
				}
			}
			if($set!=='')
			{
				$set=substr($set,0,-1);
				$this->db->query('UPDATE player_has_gadget SET '.$set.' WHERE '.$this->SQL.' LIMIT '.count($this->gadgets));
			}
		}
		else
		{
			$field = $this->gadgetKeyToField($keyOrUpdateArray);
			if($field!==false)
			{
				foreach($this->gadgets as $gadgetID => $gadget)
				{
					$this->gadgets[$gadgetID][$keyOrUpdateArray] = $value;
				}
				$this->db->query('UPDATE player_has_gadget SET '.$field.' = '.$value.' WHERE '.$this->SQL.' LIMIT '.count($this->gadgets));
			}
		}
	}
	
	function setGadget($gadgetID,$keyOrUpdateArray,$value)
	{
		if(!is_numeric($gadgetID))
		{
			global $GADGETS;
			$gadgetID = $GADGETS[$gadgetID]['ID'];
		}
		$this->getGadgets();
		if(isset($this->gadgets[$gadgetID]))
		{
			if(is_array($keyOrUpdateArray))
			{
				$set = '';
				foreach($keyOrUpdateArray as $key => $newInfo)
				{
					$field = $this->gadgetKeyToField($key);
					if($field!==false)
					{
						if($this->gadgets[$gadgetID][$key] != $newInfo)
						{
							$this->gadgets[$gadgetID][$key]=$newInfo;
							$set .= $field.' = '.$this->db->escapeString($newInfo).',';
						}
					}
				}
				if($set!=='')
				{
					$set=substr($set,0,-1);
					$this->db->query('UPDATE player_has_gadget SET '.$set.' WHERE '.$this->SQL.' AND gadget_id = '.$gadgetID.' LIMIT 1');
					return true;
				}
			}
			else
			{
				$field = $this->gadgetKeyToField($keyOrUpdateArray);
				if($field!==false)
				{
					if($this->gadgets[$gadgetID][$keyOrUpdateArray] == $value)
						return;
					$this->gadgets[$gadgetID][$keyOrUpdateArray]=$value;
					$this->db->query('UPDATE player_has_gadget SET '.$field.' = '.$value.' WHERE '.$this->SQL.' AND gadget_id = '.$gadgetID.' LIMIT 1');
					return true;
				}
			}
		}
		return false;
	}
	
	public function addGadget($gadgetID)
	{
		if(isset($this->gadgets[$gadgetID]))
			return false;
		$this->gadgets[$gadgetID] = array('Equipped' => 0, 'Cooldown' => 0, 'Expires' => 0);
		$this->db->query('REPLACE INTO player_has_gadget (game_id, account_id, gadget_id, cooldown, equipped,lasts_until) VALUES ('.$this->gameID.','.$this->accountID.','.$gadgetID.',0,0,0)');
		return true;
	}
	
	protected function gadgetKeyToField($key)
	{
		switch($key)
		{
			case 'Equipped':
				return 'equipped';
			break;
			case 'Cooldown':
				return 'cooldown';
			break;
			case 'Expires':
				return 'lasts_until';
			break;
			default:
				throw new Exception('Invalid gadget attribute: '.$key);
		}
	}
	
	function getMining($mineID=false)
	{
		if(!isset($this->mining))
		{
			//Get Player Mining Info
			$this->mining = array();
			$this->db->query('SELECT sector_has_mining.mine_id as mine_id, level, type, started, amount, starting_amount' .
							' FROM player_is_mining, sector_has_mining WHERE sector_id = ' . $this->sectorID .
							' AND sector_has_mining.game_id = player_is_mining.game_id' .
							' AND sector_has_mining.mine_id = player_is_mining.mine_id' .
							' AND account_id = ' . $this->accountID . ' AND sector_has_mining.game_id = ' . $this->gameID .' LIMIT 1');
			while ($this->db->nextRecord())
			{
				$this->mining[$this->db->getField('mine_id')] = array('Level' => $this->db->getField('level'),
																	'Started' => $this->db->getField('started'),
																	'Amount' => $this->db->getField('amount'),
																	'Type' => $this->db->getField('type'),
																	'Starting Amount' => $this->db->getField('starting_amount'));
			}
		}
		if($mineID===false)
			return $this->mining;
		if(isset($this->mining[$mineID]))
			return $this->mining[$mineID];
		return false;
	}
	
	function updateMiningStarted($mineID,$time)
	{
		$this->getMining();
		$this->mining[$mineID]['Started'] = $time;
		$this->db->query('UPDATE player_is_mining SET started = ' . $time . ' WHERE ' . $this->SQL . ' AND mine_id = ' . $mineID . ' LIMIT 1');
	}
	
	function deleteMining($mineID=false)
	{
		$this->getMining();
		if($mineID===false)
		{
			$this->db->query('DELETE FROM player_is_mining WHERE '.$this->SQL . ' LIMIT ' . count($this->getMining()));
			$this->mining=array();
		}
		else if(isset($this->mining[$mineID]))
		{
	
			unset($this->mining[$mineID]);
			$this->db->query('DELETE FROM player_is_mining WHERE '.$this->SQL.' AND mine_id='.$mineID.' LIMIT 1');
		}
	}
	
	function getKnowledge($knowledgeType=false)
	{
		if(!isset($this->knowledge))
		{
			//get players faction knowledge
			$this->db->query('SELECT * FROM player_knows_faction WHERE ' . $this->SQL . ' LIMIT 1');
			if ($this->db->nextRecord())
			{
				$this->knowledge['Erebus'] = $this->db->getField('erebus');
				$this->knowledge['Aether'] = $this->db->getField('aether');
				$this->knowledge['Tartarus'] = $this->db->getField('tartarus');
				$this->knowledge['Nyx'] = $this->db->getField('nyx');
				$this->knowledge['Federation'] = 0;
				$this->knowledge['Underground'] = 0;
			}
			else
			{
				$this->knowledge['Erebus'] = 0;
				$this->knowledge['Aether'] = 0;
				$this->knowledge['Tartarus'] = 0;
				$this->knowledge['Nyx'] = 0;
				$this->knowledge['Federation'] = 0;
				$this->knowledge['Underground'] = 0;
			}
		}
		if($knowledgeType===false)
			return $this->knowledge;
		if(isset($this->knowledge[$knowledgeType]))
			return $this->knowledge[$knowledgeType];
		return false;
	}
	
	public function killPlayer($sectorID)
	{
		$sector =& SmrSector::getSector($this->getGameID(),$sectorID);
		//msg taken care of in trader_att_proc.php
		// forget plotted course
		$this->deletePlottedCourse();

		$sector->diedHere($this);
		
		// if we are in an alliance we increase their deaths
		if ($this->hasAlliance())
			$this->db->query('UPDATE alliance SET alliance_deaths = alliance_deaths + 1 ' .
						 'WHERE game_id = '.$this->getGameID().' AND alliance_id = '.$this->getAllianceID().' LIMIT 1');

		// record death stat
		$this->increaseHOF(1,array('Dying','Deaths'), HOF_PUBLIC);
		//record cost of ship lost
		$this->increaseHOF($this->getShip()->getCost(),array('Dying','Money','Cost Of Ships Lost'), HOF_PUBLIC);
		// reset turns since last death
		$this->setHOF(0,array('Movement','Turns Used','Since Last Death'), HOF_ALLIANCE);

		// 1/4 of ship value -> insurance
		$newCredits = round($this->getShip()->getCost() / 4);
		$old_speed = $this->getShip()->getSpeed();
			
		if ($newCredits < 100000)
			$newCredits = 100000;
		$this->setCredits($newCredits);

		// speed for pod
		$new_speed = 7;

		// adapt turns
		$this->setTurns(round($this->turns / $old_speed * $new_speed),100);

		$this->setSectorID($this->getHome());
		$this->increaseDeaths(1);
		$this->setLandedOnPlanet(false);
		$this->setDead(true);
		$this->setNewbieWarning(true);
		$this->getShip()->getPod($this->getAccount()->isNewbie());
	}
	
	public function getHome()
	{
		// get his home sector
		$hq_id = GOVERNMENT + $this->getRaceID();
		$this->db->query('SELECT sector_id FROM location JOIN sector USING(game_id, sector_id) ' .
					 'WHERE game_id = '.$this->getGameID().' AND ' .
						   'location_type_id = '.$hq_id.' LIMIT 1');
		if ($this->db->nextRecord())
			return $this->db->getField('sector_id');
		else
			return 1;
	}
	
	public function &killPlayerByPlayer(AbstractSmrPlayer &$killer)
	{
		$return = array();
		$msg = $this->getBBLink();
	
		if ($this->hasCustomShipName())
		{
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$msg .= ' flying <span class="yellow">'.$named_ship.'</span>';
		}
		$msg .= ' was destroyed by ' . $killer->getBBLink();
		if ($killer->hasCustomShipName())
		{
			$named_ship = strip_tags($killer->getCustomShipName(), '<font><span><img>');
			$msg .= ' flying <span class="yellow">'.$named_ship.'</span>';
		}
		$msg .= ' in Sector&nbsp;#' . $this->getSectorID();
		$this->getSector()->increaseBattles(1);
		$this->db->query('INSERT INTO news (game_id,time,news_message,type,killer_id,killer_alliance,dead_id,dead_alliance) VALUES (' . $this->getGameID() . ',' . TIME . ',' . $this->db->escapeString($msg,true) . ',\'regular\','.$killer->getAccountID().','.$killer->getAllianceID().','.$this->getAccountID().','.$this->getAllianceID().')');
		
		$killer->sendMessage($this->getAccountID(),MSG_PLAYER,'You were <span class="red">DESTROYED</span> by ' . $killer->getBBLink() . ' in sector <span class="blue">#' . $this->getSectorID() . '</span>',false);
		$this->sendMessage($killer->getAccountID(),MSG_PLAYER,'You <span class="red">DESTROYED</span>&nbsp;' . $this->getBBLink() . ' in sector <span class="blue">#' . $this->getSectorID() . '</span>',false);
		
		// The person who got the kill gains experience
		$expGainPercent = 0.04875 + 0.03 * ($this->getLevelID() - $killer->getLevelID()) / max($this->getLevelID(), $killer->getLevelID());
		$gainedExp = ceil($expGainPercent * $this->getExperience());
		
		$return['KillerExp'] = $gainedExp;
		$killer->increaseExperience($gainedExp);
		
		if($this->isNPC())
		{
			$killer->increaseHOF($gainedExp,array('Killing','NPC','Experience','Gained'), HOF_PUBLIC);
			$killer->increaseHOF($this->getExperience(),array('Killing','NPC','Experience','Of Traders Killed'), HOF_PUBLIC);
		}
		else
		{
			$killer->increaseHOF($gainedExp,array('Killing','Experience','Gained'), HOF_PUBLIC);
			$killer->increaseHOF($this->getExperience(),array('Killing','Experience','Of Traders Killed'), HOF_PUBLIC);
		}
		$this->increaseHOF($gainedExp,array('Dying','Experience','Gained By Killer'), HOF_PUBLIC);
		// Dead player loses experience
		$expLossPercentage = 0.12 + (($this->getLevelID() - $killer->getLevelID()) * 0.0025);
		$return['DeadExp'] = 0;
		if($expLossPercentage > 0)
		{
			$return['DeadExp'] = floor($this->getExperience() * $expLossPercentage);
			$this->decreaseExperience($return['DeadExp']);
			$this->increaseHOF($return['DeadExp'],array('Dying','Experience','Lost'), HOF_PUBLIC);
			if($this->isNPC())
				$killer->increaseHOF($return['DeadExp'],array('Killing','Experience','Lost By NPCs Killed'), HOF_PUBLIC);
			else
				$killer->increaseHOF($return['DeadExp'],array('Killing','Experience','Lost By Traders Killed'), HOF_PUBLIC);
		}
		
		$creditsGained = $this->getCredits();
		$return['KillerCredits'] = $creditsGained;
		$killer->increaseCredits($creditsGained);
		if($this->isNPC())
		{
			$killer->increaseHOF($creditsGained,array('Killing','NPC','Money','Gain'), HOF_PUBLIC);
			$killer->increaseHOF($this->getShip()->getCost(),array('Killing','NPC','Money','Cost Of Ships Killed'), HOF_PUBLIC);
		}
		else
		{
			$killer->increaseHOF($creditsGained,array('Killing','Money','Gain'), HOF_PUBLIC);
			$killer->increaseHOF($this->getShip()->getCost(),array('Killing','Money','Cost Of Ships Killed'), HOF_PUBLIC);
		}

		// The killer may change alignment
		$relations = Globals::getRaceRelations($this->getGameID(),$killer->getRaceID());
		$relation = $relations[$this->getRaceID()];

		$alignChangePerRelation = 0.1;
		if($relation >= 300 || $relation <= -300)
			$alignChangePerRelation = 0.04;

		$alignChange = -$relation * $alignChangePerRelation; //Lose relations when killing a peaceful race
		$return['KillerAlign'] = $alignChange;
		if($alignChange>0)
		{
			$killer->increaseAlignment($alignChange);
			if($this->isNPC())
				$killer->increaseHOF($alignChange,array('Killing','NPC','Alignment','Gain'), HOF_PUBLIC);
			else
				$killer->increaseHOF($alignChange,array('Killing','Alignment','Gain'), HOF_PUBLIC);
		}
		else
		{
			$killer->decreaseAlignment(-$alignChange);
			if($this->isNPC())
				$killer->increaseHOF(-$alignChange,array('Killing','NPC','Alignment','Loss'), HOF_PUBLIC);
			else
				$killer->increaseHOF(-$alignChange,array('Killing','Alignment','Loss'), HOF_PUBLIC);
		}
		// War setting gives them military pay
		if($relation <= -300)
		{
			$killer->increaseMilitaryPayment(-floor($relation * 100 * (pow($gainedExp/2,0.25))));
		}
		
		//check for federal bounty being offered for current port raiders
		$allowed = TIME - 3600 * 3;
		$this->db->query('DELETE FROM player_attacks_port WHERE time < '.$allowed);
		$query = 'SELECT count(*) as numAttacks
					FROM player_attacks_port, player, port
					WHERE player_attacks_port.game_id = port.game_id
					AND port.game_id = player.game_id
					AND armour > 0
					AND player_attacks_port.sector_id = port.sector_id
					AND player.account_id = player_attacks_port.account_id
					AND player.account_id = ' . $this->getAccountID() . '
					AND player.game_id = ' . $this->getGameID();
		$this->db->query($query);
		if ($this->db->nextRecord())
		{
			if ($this->db->getField('numAttacks'))
			{
				$numAttacks = $this->db->getField('numAttacks');
				$multiplier = round(DEFEND_PORT_BOUNTY_PER_LEVEL * $this->getLevelID());
				$this->increaseCurrentBountyAmount('HQ',$numAttacks * $multiplier);
			}
		}
	
		// Killer get marked as claimer of podded player's bounties even if they don't exist
		$this->setBountiesClaimable($killer);

		// If the alignment difference is greater than 200 then a bounty may be set
		$alignment_diff = abs($this->getAlignment() - $killer->getAlignment());
		if($alignment_diff >= 200)
		{
			// If the podded players alignment makes them deputy or member then set bounty
			if($this->getAlignment() >= 100)
			{
				$bounty_type = 'HQ';
			}
			else if ($this->getAlignment() <= 100)
			{
				$bounty_type = 'UG';
			}

			if(isset($bounty_type))
			{
				$bountyGained = floor(pow($alignment_diff, 2.56));
				$killer->increaseCurrentBountyAmount($bounty_type,$bountyGained);
				if($this->isNPC())
					$killer->increaseHOF($bountyGained,array('Killing','NPC','Money','Bounty Gained'), HOF_PUBLIC);
				else
					$killer->increaseHOF($bountyGained,array('Killing','Money','Bounty Gained'), HOF_PUBLIC);
				$killer->increaseHOF($bountyGained,array('Killing','Money','Bounty Gained'), HOF_PUBLIC);
				$this->increaseHOF($bountyGained,array('Dying','Money','Bounty Gained By Killer'), HOF_PUBLIC);
			}
		}
		
		if($this->isNPC())
			$killer->increaseHOF(1,array('Killing','NPC Kills'), HOF_PUBLIC);
		else if($this->getShip()->getAttackRatingWithMaxCDs()<=MAX_ATTACK_RATING_NEWBIE&&$this->getAccount()->isNewbie()&&$killer->getAccount()->isVeteran()) //Newbie kill
			$killer->increaseHOF(1,array('Killing','Newbie Kills'), HOF_PUBLIC);
		else
		{
			$killer->increaseKills(1);
			$killer->increaseHOF(1,array('Killing','Kills'), HOF_PUBLIC);

			if($killer->hasAlliance())
			{
				$this->db->query('UPDATE alliance SET alliance_kills=alliance_kills+1 WHERE alliance_id=' . $killer->getAllianceID() . ' AND game_id=' . $killer->getGameID() . ' LIMIT 1');
			}
		}

		if($this->hasAlliance() && $killer->hasAlliance())
		{
			$this->db->query('SELECT kills FROM alliance_vs_alliance WHERE alliance_id_1=' . $killer->getAllianceID() . ' AND alliance_id_2=' . $this->getAllianceID() . ' AND game_id=' . $this->getGameID() . ' LIMIT 1');
			if($this->db->nextRecord())
			{
				$this->db->query('UPDATE alliance_vs_alliance SET kills=kills+1 WHERE alliance_id_1=' . $killer->getAllianceID() . ' AND alliance_id_2=' . $this->getAllianceID() . ' AND game_id=' . $this->getGameID() . ' LIMIT 1');
			}
			else
			{
				$this->db->query('INSERT INTO alliance_vs_alliance VALUES (' . $this->getGameID() . ',' . $killer->getAllianceID() . ',' . $this->getAllianceID() . ',1)');
			}
		}
		
		
		$this->killPlayer($this->getSectorID());
		return $return;
	}
	
	public function &killPlayerByForces(SmrForce &$forces)
	{
		$return = array();
		$owner =& $forces->getOwner();
		// send a message to the person who died
		$this->sendMessage($owner->getAccountID(), MSG_PLAYER, 'Your forces <span class="red">DESTROYED </span>'.$this->getBBLink().' in sector '.$forces->getSectorID(),false);
		$owner->sendMessage($this->getAccountID(), MSG_PLAYER, 'You were <span class="red">DESTROYED</span> by forces in sector '.$forces->getSectorID(),false);

		$news_message = $this->getBBLink();
		if ($this->hasCustomShipName())
		{
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$news_message .= ' flying <span class="yellow">'.$named_ship.'</span>';
		}
		$news_message .= ' was destroyed by forces in sector #'.$forces->getSectorID();
		// insert the news entry
		$this->db->query('INSERT INTO news (game_id, time, news_message,killer_id,killer_alliance,dead_id,dead_alliance) ' .
					 'VALUES('.$this->getGameID().', ' . TIME . ', ' . $this->db->escapeString($news_message) . ','.$owner->getAccountID().','.$owner->getAllianceID().','.$this->getAccountID().','.$this->getAllianceID().')');
		
		$expLossPercentage = .30;
		$return['DeadExp'] = floor($this->getExperience() * $expLossPercentage);
		$this->decreaseExperience($return['DeadExp']);
		
		if ($percentage > 0 )
			$this->decreaseExperience($this->getExperience() * $percentage / 100);
		$this->db->query('SELECT * FROM alliance_vs_alliance WHERE alliance_id_1 = -1 AND ' .
						'alliance_id_2 = '.$this->getAllianceID().' AND game_id = '.$this->getGameID());
		if ($this->db->nextRecord()) $this->db->query('UPDATE alliance_vs_alliance SET kills = kills + 1 ' .
											'WHERE alliance_id_1 = -1 AND alliance_id_2 = '.$this->getAllianceID().' AND ' .
											'game_id = '.$this->getGameID());
		else $this->db->query('REPLACE INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) ' .
						'VALUES ('.$this->getGameID().', -1, '.$this->getAllianceID().', 1)');
		
		
		$this->killPlayer($forces->getSectorID());
		return $return;
	}
	
	public function &killPlayerByPort(SmrPort &$port)
	{
		$return = array();
		// send a message to the person who died
		self::sendMessageFromPort($this->getGameID(), $this->getAccountID(), 'You were <span class="red">DESTROYED</span> by '.$port->getDisplayName().'\'s defenses.');
		
		$news_message = $this->getBBLink();
		if ($this->hasCustomShipName())
		{
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$news_message .= ' flying <span class="yellow">'.$named_ship.'</span>';
		}
		$news_message .= ' was destroyed while invading '.$port->getDisplayName().'.';
		// insert the news entry
		$this->db->query('INSERT INTO news (game_id, time, news_message,killer_id,dead_id,dead_alliance) ' .
					 'VALUES('.$this->getGameID().', ' . TIME . ', ' . $this->db->escapeString($news_message).','.ACCOUNT_ID_PORT.','.$this->getAccountID().','.$this->getAllianceID().')');
		
		$expLossPercentage = .31 - $port->getLevel()/100;
		$return['DeadExp'] = floor($this->getExperience() * $expLossPercentage);
		$this->decreaseExperience($return['DeadExp']);
		
		$this->killPlayer($port->getSectorID());
		return $return;
	}
	
	public function &killPlayerByPlanet(SmrPlanet &$planet)
	{
		$return = array();
		// send a message to the person who died
		$planetOwner =& $planet->getOwner();
		$this->sendMessage($planetOwner->getAccountID(), MSG_PLAYER, 'Your planet <span class="red">DESTROYED</span> '.$this->getBBLink().' in sector '.$planet->getSectorID(),false);
		$planetOwner->sendMessage($this->getAccountID(), MSG_PLAYER, 'You were <span class="red">DESTROYED</span> by '.$planet->getDisplayName().'\'s defenses.',false);
		
		$news_message = $this->getBBLink();
		if ($this->hasCustomShipName())
		{
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$news_message .= ' flying <span class="yellow">'.$named_ship.'</span>';
		}
		$news_message .= ' was destroyed by '.$planet->getDisplayName().'\'s planetary defenses in Sector #'.$planet->getSectorID().'.';
		// insert the news entry
		$this->db->query('INSERT INTO news (game_id, time, news_message,killer_id,killer_alliance,dead_id,dead_alliance) ' .
					 'VALUES('.$this->getGameID().', ' . TIME . ', ' . $this->db->escapeString($news_message) . ','.$planetOwner->getAccountID().','.$planetOwner->getAllianceID().','.$this->getAccountID().','.$this->getAllianceID().')');
		
		$expLossPercentage = .27 - $planet->getLevel()/1000;
		$return['DeadExp'] = floor($this->getExperience() * $expLossPercentage);
		$this->decreaseExperience($return['DeadExp']);
		
		$this->killPlayer($planet->getSectorID());
		return $return;
	}
	
	function changeShip(SmrShip &$ship,$shipID)
	{
		$this->db->query('DELETE FROM player_has_goods WHERE '.$this->SQL.' LIMIT 1');
		decloak($ship);
		$this->db->query('DELETE FROM player_has_illusion WHERE '.$this->SQL.' LIMIT 1');
		$this->setShipID($shipID);
		$ship = get_ship($this);
		checkShipForExcess($ship,$this);
	}
	
	public function save()
	{
		if($this->hasChanged === true)
		{
			$this->db->query('UPDATE player SET player_name='.$this->db->escapeString($this->playerName).
				', player_id='.$this->db->escapeNumber($this->playerID).
				', sector_id='.$this->db->escapeNumber($this->sectorID).
				', last_sector_id='.$this->db->escapeNumber($this->lastSectorID).
				', turns='.$this->db->escapeNumber($this->turns).
				', last_turn_update='.$this->db->escapeNumber($this->lastTurnUpdate).
				', newbie_turns='.$this->db->escapeNumber($this->newbieTurns).
				', last_ship_mod='.$this->db->escapeNumber($this->lastShipMod).
				', last_news_update='.$this->db->escapeNumber($this->lastNewsUpdate).
				', stunned='.$this->db->escapeString($this->stunned).
				', fleed='.$this->db->escapeBoolean($this->fleed).
				', attack_warning='.$this->db->escapeString($this->attackColor).
				', dead='.$this->db->escapeBoolean($this->dead).
				', land_on_planet='.$this->db->escapeBoolean($this->landedOnPlanet).
				', kicked='.$this->db->escapeBoolean($this->kicked).
				', last_shield_update='.$this->db->escapeNumber($this->lastShieldUpdate).
				', last_active='.$this->db->escapeNumber($this->lastActive).
				', last_cpl_action='.$this->db->escapeNumber($this->lastCPLAction).
				', race_id='.$this->db->escapeNumber($this->raceID).
				', credits='.$this->db->escapeNumber($this->credits).
				', experience='.$this->db->escapeNumber($this->experience).
				', alignment='.$this->db->escapeNumber($this->alignment).
				', military_payment='.$this->db->escapeString($this->militaryPayment).
//				', past_knowledge='.$this->db->escapeString($this->pastKnowledge).
				', government_help='.$this->db->escapeString($this->governmentHelp).
				', alliance_id='.$this->db->escapeNumber($this->allianceID).
				', alliance_join='.$this->db->escapeNumber($this->allianceJoinable).
				', ship_type_id='.$this->db->escapeNumber($this->shipID).
				', kills='.$this->db->escapeNumber($this->kills).
				', deaths='.$this->db->escapeNumber($this->deaths).
				', last_port='.$this->db->escapeNumber($this->lastPort).
				', bank='.$this->db->escapeNumber($this->bank).
				', zoom='.$this->db->escapeString($this->zoom).
//				', chat='.$this->db->escapeString($this->chat).
//				', chat_position='.$this->db->escapeString($this->chatPosition).
				', display_missions='.$this->db->escapeBoolean($this->displayMissions).
				', display_weapons='.$this->db->escapeBoolean($this->displayWeapons).
				', force_drop_messages='.$this->db->escapeBoolean($this->forceDropMessages).
				', ignore_globals='.$this->db->escapeBoolean($this->ignoreGlobals).
//				', changed_stats='.$this->db->escapeString($this->changedStats).
				', newbie_warning = ' . $this->db->escapeBoolean($this->newbieWarning).
				', name_changed = ' . $this->db->escapeBoolean($this->nameChanged).
				', combat_drones_kamikaze_on_mines = ' . $this->db->escapeBoolean($this->combatDronesKamikazeOnMines).
				' WHERE '.$this->SQL.' LIMIT 1');
			$this->hasChanged=false;
		}
		foreach($this->hasBountyChanged as $key => &$bountyChanged)
		{
			if($bountyChanged===true)
			{
				$bountyChanged = false;
				$bounty = $this->getBounty($key);
				if($bounty['New'] === true)
				{
					if($bounty['Amount'] > 0 || $bounty['SmrCredits'] > 0)
						$this->db->query('INSERT INTO bounty (account_id,game_id,type,amount,smr_credits,claimer_id,time) VALUES (' . $this->getAccountID() . ',' . $this->getGameID() . ',' . $this->db->escapeString($bounty['Type']) . ',' . $bounty['Amount'] .','.$bounty['SmrCredits'].',' . $bounty['Claimer'] . ',' . $bounty['Time'] .')');
				}
				else
				{
					if($bounty['Amount'] > 0 || $bounty['SmrCredits'] > 0)
						$this->db->query('UPDATE bounty
							SET amount=' . $bounty['Amount'] . ',
							smr_credits=' . $this->db->escapeString($bounty['SmrCredits']) . ',
							type=' . $this->db->escapeString($bounty['Type']) . ',
							claimer_id=' . $bounty['Claimer'] . ',
							time=' . $bounty['Time'] . '
							WHERE bounty_id=' . $bounty['ID'] . ' AND account_id = ' . $this->getAccountID() . ' AND game_id = ' . $this->getGameID() . ' LIMIT 1');
					else
						$this->db->query('DELETE FROM bounty WHERE bounty_id=' . $bounty['ID'] . ' AND account_id = ' . $this->getAccountID() . ' AND game_id = ' . $this->getGameID() . ' LIMIT 1');
				}
			}
		}
		$this->saveHOF();
	}
	
	public function saveHOF()
	{
		if($this->hasHOFChanged!==false)
			$this->doHOFSave($this->hasHOFChanged);
		if(!empty(self::$hasHOFVisChanged))
		{
			foreach(self::$hasHOFVisChanged as $hofType => $changeType)
			{
				if($changeType==self::HOF_NEW)
					$this->db->query('INSERT INTO hof_visibility (type, visibility) VALUES ('.$this->db->escapeString($hofType).','.$this->db->escapeString(self::$HOFVis[$hofType]).')');
				else
					$this->db->query('UPDATE hof_visibility SET visibility = '.$this->db->escapeString(self::$HOFVis[$hofType]).' WHERE type = '.$this->db->escapeString($hofType).' LIMIT 1');
				unset(self::$hasHOFVisChanged[$hofType]);
			}
		}
	}
	protected function doHOFSave(array &$hasChangedList,array $typeList = array())
	{
		foreach($hasChangedList as $type => &$hofChanged)
		{
			$tempTypeList = $typeList;
			$tempTypeList[] = $type;
			if(is_array($hofChanged))
			{
				$this->doHOFSave($hofChanged,$tempTypeList);
			}
			else
			{
				$amount = $this->getHOF($tempTypeList);
				if($hofChanged==self::HOF_NEW)
				{
					if($amount > 0)
						$this->db->query('INSERT INTO player_hof (account_id,game_id,type,amount) VALUES (' . $this->getAccountID() . ',' . $this->getGameID() . ',' . $this->db->escapeArray($tempTypeList,false,true,':',false) . ',' . $amount.')');
				}
				else if($hofChanged==self::HOF_CHANGED)
				{
	//				if($amount > 0)
						$this->db->query('UPDATE player_hof
							SET amount=' . $amount . '
							WHERE account_id = ' . $this->getAccountID() . ' AND game_id = ' . $this->getGameID() . ' AND type = '.$this->db->escapeArray($tempTypeList,false,true,':',false).' LIMIT 1');
	//				else
	//					$this->db->query('DELETE FROM player_hof WHERE account_id=' . $this->getAccountID() . ' AND game_id = ' . $this->getGameID() . ' AND type = ' . $this->db->escapeArray($tempTypeList,false,true,':',false) . ' LIMIT 1');
	//				}
				}
				$hofChanged = false;
			}
		}
	}
	
	protected function getHOFData()
	{
		if(!isset($this->HOF))
		{
			//Get Player HOF
			$this->db->query('SELECT type,amount FROM player_hof WHERE ' . $this->SQL);
			$this->HOF = array();
			while($this->db->nextRecord())
			{
				$hof =& $this->HOF;
				$typeList = explode(':',$this->db->getField('type'));
				foreach($typeList as $type)
				{
					if(!isset($hof[$type]))
					{
						$hof[$type] = array();
					}
					$hof =& $hof[$type];
				}
				$hof = $this->db->getField('amount');
			}
			self::getHOFVis();
		}
	}
	
	public static function getHOFVis()
	{
		if(!isset(self::$HOFVis))
		{
			//Get Player HOF Vis
			$db = new SmrMySqlDatabase();
			$db->query('SELECT type,visibility FROM hof_visibility');
			self::$HOFVis = array();
			while($db->nextRecord())
			{
				self::$HOFVis[$db->getField('type')] = $db->getField('visibility');
			}
		}
	}
	
	protected function getBountiesData()
	{
		if(!isset($this->bounties))
		{
			$this->bounties = array();
			$this->db->query('SELECT * FROM bounty WHERE account_id = ' . $this->getAccountID() . ' AND game_id=' . $this->getGameID());
			while($this->db->nextRecord())
			{
				$this->bounties[$this->db->getField('bounty_id')] = array(
							'Amount' => $this->db->getField('amount'),
							'SmrCredits' => $this->db->getField('smr_credits'),
							'Type' => $this->db->getField('type'),
							'Claimer' => $this->db->getField('claimer_id'),
							'Time' => $this->db->getField('time'),
							'ID' => $this->db->getField('bounty_id'),
							'New' => false);
			}
		}
	}
	
	public function isPresident()
	{
		$president =& Council::getPresident($this->getGameID(),$this->getRaceID());
		return is_object($president)&&$this->equals($president);
	}
	
	public function isOnCouncil()
	{
		return Council::isOnCouncil($this->getGameID(),$this->getRaceID(),$this->getAccountID());
	}
	
	public function setNewbieWarning($bool)
	{
		if($this->newbieWarning==$bool)
		{
			return;
		}
		$this->newbieWarning=$bool;
		$this->hasChanged=true;
	}
	
	public function getNewbieWarning()
	{
		return $this->newbieWarning;
	}
	
	public function getTickers()
	{
		if(!isset($this->tickers))
		{
			$this->tickers = array();
			//get ticker info
			$this->db->query('SELECT type,time,expires,recent FROM player_has_ticker WHERE ' . $this->SQL .' AND expires > ' . TIME);
			while($this->db->nextRecord())
				$this->tickers[$this->db->getField('type')] = array( 'Type' => $this->db->getField('type'),
																				'Time' => $this->db->getField('time'),
																				'Expires' => $this->db->getField('expires'),
																				'Recent' => $this->db->getField('recent'));
		}
		return $this->tickers;
	}
	
	public function hasTickers()
	{
		return count($this->getTickers()) > 0;
	}
	
	public function getTicker($tickerType)
	{
		$tickers = $this->getTickers();
		if(isset($tickers[$tickerType]))
			return $tickers[$tickerType];
		return false;
	}
	
	public function hasTicker($tickerType)
	{
		return $this->getTicker($tickerType) !== false;
	}
	
	public function getTurnsLevel()
	{
		if (!$this->hasTurns()) return 'NONE';
		if ($this->getTurns() <= 25 ) return 'LOW';
		if ($this->getTurns() <= 75 ) return 'MEDIUM';
		return 'HIGH';
	}
	
	public function update()
	{
		$this->save();
	}
	
	protected static function doMessageSending($senderID,$receiverID, $gameID, $messageTypeID, $message, $expires, $senderDelete = false)
	{
		$message = trim($message);
		$db = new SmrMySqlDatabase();
		// send him the message
		$db->query('INSERT INTO message
			(account_id,game_id,message_type_id,message_text,
			sender_id,send_time,expire_time,sender_delete) VALUES(' .
			$receiverID . ',' .
			$gameID . ',' .
			$messageTypeID . ',' .
			$db->escapeString($message) . ',' .
			$senderID . ',' .
			TIME . ',' .
			$expires . ',' .
			$db->escapeBoolean($senderDelete) . ')'
		);

		// give him the message icon
		$db->query('REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES ' .
						'('.$gameID.', '.$receiverID.', '.$messageTypeID.')');
		
		switch ($messageTypeID)
		{
			case MSG_PLAYER:
				$receiverAccount =& SmrAccount::getAccount($receiverID);
				if($receiverAccount->isValidated() && $receiverAccount->isReceivingMessageNotifications($messageTypeID) && !$receiverAccount->isLoggedIn())
				{
					require_once(LIB.'External/phpMailer/class.phpmailer.php');
					$senderPlayer =& SmrPlayer::getPlayer($senderID, $gameID);
					$mail = new PHPMailer();
					$mail->Subject	= 'Message Notification';
					$mail->From		= 'notifications@smrealms.de';
					$mail->FromName	= 'SMR Notifications';
					$mail->Mailer	= 'smtp';
					$bbifiedMessage = 'From: ' . $senderPlayer->getDisplayName() . ' Date: ' . date($receiverAccount->getShortDateFormat().' '.$receiverAccount->getShortTimeFormat(),TIME) . "<br/>\r\n<br/>\r\n" . bbifyMessage($message,true);
					$mail->MsgHTML($bbifiedMessage);
					$mail->AltBody = strip_tags($bbifiedMessage);
					$mail->AddAddress($receiverAccount->getEmail(), $receiverAccount->getHofName());
					$mail->Send();
					$mail->SmtpClose();
					$receiverAccount->decreaseMessageNotifications($messageTypeID,1);
				}
			break;
		}
	}
	
	public function sendMessageToBox($boxTypeID, $message)
	{
		// send him the message
		SmrAccount::doMessageSendingToBox($this->getAccountID(), $boxTypeID, $message, $this->getGameID());
	}
	
	public function sendGlobalMessage($message, $canBeIgnored = true)
	{
		if($canBeIgnored)
		{
			if($this->getAccount()->isMailBanned())
				create_error('You are currently banned from sending messages');
		}
		$this->sendMessageToBox(BOX_GLOBALS,$message);
		
		// send to all online player
		$db = new SmrMySqlDatabase();
		$db->query('SELECT account_id
					FROM active_session
					JOIN player USING (game_id, account_id)
					WHERE active_session.last_accessed >= ' . (TIME - SmrSession::TIME_BEFORE_EXPIRY) . '
						AND game_id = ' . $this->getGameID() . '
						AND player.ignore_globals = \'FALSE\'
						AND account_id != ' . $this->getAccountID());
	//	$db->query('SELECT * FROM player WHERE game_id = '.$player->getGameID().' AND last_cpl_action >= '.$allowed.' AND ignore_globals = \'FALSE\'');
	
		while ($db->nextRecord())
		{
			$this->sendMessage($db->getInt('account_id'), MSG_GLOBAL, $message, $canBeIgnored);
		}
	}
	
	public function sendMessage($receiverID, $messageTypeID, $message, $canBeIgnored = true)
	{
		//get expire time
		if($canBeIgnored)
		{
			if($this->getAccount()->isMailBanned())
				create_error('You are currently banned from sending messages');
			// Don't send messages to players ignoring us
			$this->db->query('SELECT account_id FROM message_blacklist WHERE account_id=' . $receiverID . ' AND blacklisted_id=' . $this->getAccountID() . ' LIMIT 1');
			if($this->db->nextRecord())
				return;
		}
				
		$message = word_filter($message);
		
		switch ($messageTypeID)
		{
			case MSG_GLOBAL: //We don't send globals to the box here or it gets done loads of times.
				$expires = 3600; // 1h
			break;
			case MSG_PLAYER:
				$expires = 86400 * 31;
			break;
			case MSG_PLANET:
				$expires = 86400 * 3;
			break;
			case MSG_SCOUT:
				$expires = 86400 * 3;
			break;
			case MSG_POLITICAL:
				$expires = 86400 * 31;
			break;
			case MSG_ALLIANCE:
				$expires = 86400 * 31;
			break;
			case MSG_ADMIN:
				$expires = 86400 * 365;
			break;
			default:
				$expires = 86400 * 3;
		}
		$expires += TIME;
		// send him the message
		self::doMessageSending($this->getAccountID(),$receiverID, $this->getGameID(), $messageTypeID, $message, $expires, $messageTypeID == MSG_SCOUT);
	}
	
	public static function sendMessageFromPlanet($gameID, $receiverID, $message)
	{
		//get expire time
		$expires = TIME + 86400 * 31;
		// send him the message
		self::doMessageSending(ACCOUNT_ID_PLANET,$receiverID, $gameID, MSG_PLANET, $message, $expires);
	}
	
	public static function sendMessageFromPort($gameID, $receiverID, $message)
	{
		//get expire time
		$expires = TIME + 86400 * 31;
		// send him the message
		self::doMessageSending(ACCOUNT_ID_PORT,$receiverID, $gameID, MSG_PLAYER, $message, $expires);
	}
	public static function sendMessageFromAdmin($gameID, $receiverID, $message, $expires = false)
	{
		//get expire time
		if($expires===false)
			$expires = TIME + 86400 * 365;
		// send him the message
		self::doMessageSending(ACCOUNT_ID_ADMIN,$receiverID, $gameID, MSG_ADMIN, $message, $expires);
	}
	
	public static function sendMessageFromAllianceAmbassador($gameID, $receiverID, $message, $expires = false)
	{
		//get expire time
		if($expires===false)
			$expires = TIME + 86400 * 31;
		// send him the message
		self::doMessageSending(ACCOUNT_ID_ALLIANCE_AMBASSADOR, $receiverID, $gameID, MSG_ALLIANCE, $message, $expires);
	}
	
	public static function sendMessageFromRace($raceID, $gameID, $receiverID, $message, $expires = false)
	{
		//get expire time
		if($expires===false)
			$expires = TIME + 86400 * 5;
		// send him the message
		self::doMessageSending(ACCOUNT_ID_GROUP_RACES+$raceID,$receiverID, $gameID, MSG_POLITICAL, $message, $expires);
	}
	
	public function setMessagesRead($messageTypeID)
	{
		$this->db->query('DELETE FROM player_has_unread_messages WHERE '.$this->SQL.' AND ' .
					'message_type_id = '.$messageTypeID);
	}
	
	public function &getPlottedCourse()
	{
		if(!isset($this->plottedCourse))
		{
			require_once(get_file_loc('Plotter.class.inc'));
			// check if we have a course plotted
			$this->db->query('SELECT course FROM player_plotted_course
			WHERE account_id=' . $this->getAccountID() . '
			AND game_id=' . $this->getGameID() . '
			LIMIT 1'
			);
			
			if ($this->db->nextRecord())
			{
				// get the course back
				$this->plottedCourse = unserialize($this->db->getField('course'));
				$endSectorID = $this->plottedCourse->getEndSectorID();
				if($endSectorID != $this->getSectorID() && $this->plottedCourse->isInPath($this->getSectorID())) //Current sector is in the path, we have jumped along it somehow so lets replot from our cur
				{
					$this->plottedCourse =& Plotter::findDistanceToX(SmrSector::getSector($this->getGameID(),$endSectorID), $this->getSector(), true);
					if($this->plottedCourse === false)
					{
						throw new Exception('For some reason we could not replot from "'.$this->getSectorID().'" to "'.$endSectorID);
					}
					$this->plottedCourse->removeStart();
					$this->setPlottedCourse($this->plottedCourse);
				}
			}
			else
				$this->plottedCourse = false;
		}
		return $this->plottedCourse;
	}
	
	public function setPlottedCourse(Distance &$plottedCourse)
	{
		$hadPlottedCourse = $this->hasPlottedCourse();
		$this->plottedCourse =& $plottedCourse;
		if ($this->plottedCourse->getTotalSectors()>0)
			$this->db->query('REPLACE INTO player_plotted_course
				(account_id, game_id, course)
				VALUES('.$this->getAccountID().', '.$this->getGameID().', ' . $this->db->escapeBinary(serialize($this->plottedCourse)) . ')');
		else if($hadPlottedCourse)
		{
			$this->plottedCourse = false;
			$this->db->query('DELETE FROM player_plotted_course WHERE account_id = '.$this->getAccountID().' AND game_id = '.$this->getGameID());
		}
	}
	
	public function hasPlottedCourse()
	{
		return $this->getPlottedCourse()!==false;
	}
	
	public function isPartOfCourse(&$sectorOrSectorID)
	{
		if(!$this->hasPlottedCourse())
			return false;
		if($sectorOrSectorID instanceof SmrSector )
			$sectorID = $sectorOrSectorID->getSectorID();
		else
			$sectorID = $sectorOrSectorID;
		return $this->getPlottedCourse()->isInPath($sectorID);
	}
	
	public function deletePlottedCourse()
	{
		$this->plottedCourse = false;
		$this->db->query('DELETE FROM player_plotted_course ' .
					 'WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public function getExamineTraderHREF()
	{
		$container = create_container('skeleton.php','trader_examine.php');
		$container['target']	= $this->getAccountID();
		return SmrSession::get_new_href($container);
	}
	
	public function getAttackTraderHREF()
	{
		global $var;
		$container = create_container('skeleton.php','trader_attack_processing.php');
		$container['target']	= $this->getAccountID();
		if(isset($var['time']))
			$container['time']	= $var['time'];
		else
			$container['time']	= 0;
		return SmrSession::get_new_href($container);
	}
	
	public function getPlanetKickHREF()
	{
		$container = create_container('planet_kick_processing.php','trader_attack_processing.php');
		$container['account_id']	= $this->getAccountID();
		return SmrSession::get_new_href($container);
	}
	
	public function getTraderSearchHREF()
	{
		$container = create_container('skeleton.php','trader_search_result.php');
		$container['player_id'] = $this->getPlayerID();
		return SmrSession::get_new_href($container);
	}
	
	public function getAllianceRosterHREF()
	{
		$container = create_container('skeleton.php','alliance_roster.php');
		$container['alliance_id'] = $this->getAllianceID();
		return SmrSession::get_new_href($container);
	}
	
	public function getToggleWeaponHidingHREF()
	{
		$container = create_container('skeleton.php','toggle_processing.php');
		$container['toggle'] = 'WeaponHiding';
		return SmrSession::get_new_href($container);
	}
	
	public function __sleep()
	{
        return array('accountID', 'gameID', 'sectorID', 'alignment', 'playerID', 'playerName');
	}
}
?>