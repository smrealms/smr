<?
require_once('AbstractSmrPlayer.class.inc');
require_once('SmrMySqlDatabase.class.inc');

class SmrPlayer extends AbstractSmrPlayer
{
	protected static $CACHE_SECTOR_PLAYERS = array();
	protected static $CACHE_PLAYERS = array();
	
	protected $db;
	
//	var $race_name;
//	var $level_id;
//	var $level_name;
//	var $alliance_description;
//	var $military_payment;
//	var $controlled;
//	var $old_dead;
	protected $newbieWarning;
//	var $relations_global;
//	var $relations_global_rev;
//	var $display_experience;
//	var $display_level_id;
//	var $display_level_name;
//	var $sector_change;
//	var $game_type;
//	var $game_speed;
	protected $validated;
//	var $out_of_game;
//	var $last_ticker_update;
	protected $ticker;
//    var $safe_exit;
//    var $detected;
	protected $originalMaintenance;
	protected $lastMaintenanceUpdate;
	protected $lastShipMod;
	protected $lastNewsUpdate;
	protected $stunned;
	protected $fleed;
	protected $attackColor;
	protected $kicked;
//	protected $pastKnowledge;
	protected $governmentHelp;
//	protected $allianceJoinable;
	protected $lastPort;
	protected $bank;
	protected $zoom;
//	protected $zoomOn;
//	protected $chat;
//	protected $chatPosition;
	protected $displayMissions;
	protected $ignoreGlobals;
//	protected $changedStats;
	protected $completedMissions;
	protected $missions;
	protected $mining;
	protected $plottedCourse;
	
	
	protected $examineTraderHREF;
	protected $attackTraderHREF;
	protected $traderSearchHREF;
	protected $allianceRosterHREF;
	
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
	
	public static function savePlayers()
	{
		foreach(self::$CACHE_PLAYERS as &$player)
		{
			$player->save();
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
		if($forceUpdate || !isset(self::$CACHE_SECTOR_PLAYERS[$gameID . ':' . $sectorID]))
		{
			$db = new SmrMySqlDatabase();
			$db->query('SELECT account_id FROM player WHERE sector_id = ' . $sectorID . ' AND game_id=' . $gameID);
			$players = array();
			while($db->nextRecord())
			{
				$accountID = $db->getField('account_id');
				$players[$db->getField('account_id')] =& self::getPlayer($accountID,$gameID);
			}
			return self::$CACHE_SECTOR_PLAYERS[$gameID . ':' . $sectorID] =& $players;
		}
		return self::$CACHE_SECTOR_PLAYERS[$gameID . ':' . $sectorID];
	}

	public static function &getPlayer($accountIDOrResultArray,$gameID,$forceUpdate = false)
	{
		if($forceUpdate || is_array($accountIDOrResultArray) || !isset(self::$CACHE_PLAYERS[$gameID . ':' . $accountIDOrResultArray]))
		{
			$p = new SmrPlayer($gameID,$accountIDOrResultArray);
			return self::$CACHE_PLAYERS[$gameID . ':' . $p->getAccountID()] =& $p;
		}
		return self::$CACHE_PLAYERS[$gameID. ':' .$accountIDOrResultArray];
		
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
			$sql = $this->db->query('SELECT * FROM player WHERE account_id = '.$accountIDOrResultArray.' AND game_id = '.$gameID.' LIMIT 1');
			$this->db->nextRecord($sql);
			$result = $this->db->getRow();
		}
		if($result)
		{
			$this->accountID				= (int) $result['account_id'];
			$this->gameID					= (int) $result['game_id'];
			$this->playerName				= (string) $result['player_name'];
			$this->playerID					= (int) $result['player_id'];
			$this->sectorID					= (int) $result['sector_id'];
			$this->lastSectorID				= (int) $result['last_sector_id'];
			$this->turns					= (int) $result['turns'];
			$this->originalMaintenance		= $this->turns;
			$this->lastMaintenanceUpdate	= (int) $result['last_turn_update'];
			$this->newbieTurns				= (int) $result['newbie_turns'];
			$this->lastShipMod				= (int) $result['last_ship_mod'];
			$this->lastNewsUpdate			= (int) $result['last_news_update'];
			$this->stunned					= (int) $result['stunned'];
			$this->fleed					= $result['fleed']=='TRUE';
			$this->attackColor				= (string) $result['attack_warning'];
			$this->dead						= $result['dead']=='TRUE';
			$this->startDead				= $this->dead;
			$this->landedOnPlanet			= $result['land_on_planet']=='TRUE';
			$this->kicked					= $result['kicked']=='TRUE';
			$this->lastShieldUpdate			= (int) $result['last_shield_update'];
			$this->lastActive				= (int) $result['last_active'];
			$this->lastCPLAction			= (int) $result['last_cpl_action'];
			$this->raceID					= (int) $result['race_id'];
			$this->credits					= (int) $result['credits'];
			$this->experience				= (int) $result['experience'];
			$this->alignment				= (int) $result['alignment'];
			$this->militaryPayment			= (int) $result['military_payment'];
//			$this->pastKnowledge = $result['past_knowledge'];
			$this->governmentHelp			= (int) $result['government_help'];
			$this->allianceID				= (int) $result['alliance_id'];
//			$this->allianceJoinable = $result['alliance_join'];
			$this->shipID					= (int) $result['ship_type_id'];
			$this->kills					= (int) $result['kills'];
			$this->deaths					= (int) $result['deaths'];
			$this->lastPort					= (int) $result['last_port'];
			$this->bank						= (int) $result['bank'];
			$this->zoom						= (int) $result['zoom'];
//			$this->zoomOn = $result['zoom_on']=='TRUE';
//			$this->chat = $result['chat'];
//			$this->chatPosition = $result['chat_position'];
			$this->displayMissions			= $result['display_missions']=='TRUE';
			$this->ignoreGlobals			= $result['ignore_globals']=='TRUE';
			$this->newbieWarning			= $result['newbie_warning']=='TRUE';
//			$this->changedStats = $result['changed_stats'];


			// is account validated?
			$this->db->query('SELECT * FROM account WHERE account_id = '.$this->accountID);
			if ($this->db->nextRecord())
				$this->validated = $this->db->getField('validated')=='TRUE';
			
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
//			case 'Alliance Joinable':
//				return $this->allianceJoinable;
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
				return $this->lastMaintenanceUpdate;
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
			case 'Max Maintenance':
				return $this->getMaxTurns();
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
		require_once('SmrAccount.class.inc');
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
	
	function leaveAlliance()
	{
		$this->setAllianceID(0);
//		$this->setAllianceJoinable(TIME + 1 * 3600);
//		$this->db->query('DELETE FROM player_has_alliance_role WHERE '.$this->SQL);
	}
	
//	function getAllianceJoinable()
//	{
//		return $this->allianceJoinable;
//	}
	
//	function setAllianceJoinable($time)
//	{
//		if($this->allianceJoinable == $time)
//			return;
//		$this->allianceJoinable=$time;
//		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET alliance_join = '.$time.' WHERE '.$this->SQL.' LIMIT 1');
//	}
	
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
		if (!$this->isValidated()) return;
		$ship =& $this->getShip();
		
		// update turns?
		$time_diff = TIME - $this->getLastMaintenanceUpdate();
		
		// how many turns would he get right now?
		$extraMaint = floor($time_diff * $ship->getSpeed() * Globals::getGameSpeed($this->gameID) / 3600);
		
		// do we have at least one turn to give?
		if ($extraMaint > 0)
		{
			// recalc the time to avoid errors
			$this->setLastMaintenanceUpdate($this->getLastMaintenanceUpdate() + ceil($extraMaint * 3600 / ($ship->getSpeed() * Globals::getGameSpeed($this->gameID))));
			
			$this->giveTurns($extraMaint);	
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
	
	function getDisplayMissions()
	{
		return $this->displayMissions;
	}
	
	function setDisplayMissions($bool)
	{
		if($this->displayMissions == $bool)
			return;
		$this->displayMissions=$bool;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET display_missions = '.$this->db->escapeBoolean($bool).' WHERE '.$this->SQL.' LIMIT 1');
	
	}
	
	function getLastMaintenanceUpdate()
	{
		return $this->lastMaintenanceUpdate;
	}
	
	function setLastMaintenanceUpdate($time)
	{
		if($this->lastMaintenanceUpdate == $time)
			return;
		$this->lastMaintenanceUpdate=$time;
		$this->hasChanged=true;
//		$sql = $this->db->query('UPDATE player SET last_turn_update = ' . $this->lastMaintenanceUpdate . ' WHERE '. $this->SQL . ' LIMIT 1');
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

	function getDisplayName()
	{
//NPC: get_colored_name($dead_guy->getPlayerName(), $dead_guy->getAlignment()) . ' <span class='npcColor'>[NPC]</span>';
			return get_colored_text($this->alignment,$this->playerName.' ('.$this->playerID.')');
	}
	function getLevelName()
	{
		$LEVELS_REQUIREMENTS = Globals::getLevelRequirements();
		return $LEVELS_REQUIREMENTS[$this->getLevelID()]['Name'];
	}
	
	function getPastMaintBought()
	{
		if(!isset($this->pastMaintBought))
		{
			//get past maint
			global $TIME;
			$this->db->query('DELETE FROM player_repaired WHERE time < '.($TIME - 3600 * 20));
			$sql = $this->db->query('SELECT sum(amount) as sum FROM player_repaired WHERE ' . $this->SQL . ' AND amount > 0 LIMIT 1');
			if ($this->db->nextRecord($sql)) $this->pastMaintBought = $this->db->getField('sum');
			else $this->pastMaintBought = 0;
		}
		return $this->pastMaintBought;
	}
	
	function getPastMaintCredit()
	{
		if(!isset($this->pastMaintCredit))
		{
			//get past maint
			global $TIME;
			$this->db->query('DELETE FROM player_repaired WHERE time < '.($TIME - 3600 * 20));
			$m_sql = $this->db->query('SELECT sum(amount) as sum FROM player_repaired WHERE '.$this->SQL.' AND amount < 0 LIMIT 1');
			if ($this->db->nextRecord($m_sql)) $this->pastMaintCredit = $this->db->getField('sum');
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
		global $TIME;
		$this->getPastMaintBought();
		$this->db->query('REPLACE INTO player_repaired (account_id, game_id, time, amount, source) VALUES (' . $this->accountID . ',' . $this->gameID . ',' . $TIME . ',' . $amount . ','.$this->db->escapeString($type).')');
		$this->giveTurns($amount*10);
		$this->pastMaintBought+=$amount;
	}
	
	protected function getStatsData()
	{
		if(!isset($this->stats))
		{
			//initialize stats
			global $STATS;
			foreach ($STATS as $stat_name => $statArray)
				$temp_stats[$statArray['ID']] = 0;
			$sql = $this->db->query('SELECT stat_type_id, amount FROM player_has_special WHERE ' . $this->SQL . ' LIMIT ' . sizeof($STATS));
			while ($this->db->nextRecord($sql))
				$temp_stats[$this->db->getField('stat_type_id')] = $this->db->getField('amount');
			//put stats in a more user friendly form
			$this->stats = array();
			foreach ($STATS as $stat_name => $statArray)
				$this->stats[$stat_name] = $temp_stats[$statArray['ID']];
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
			$r_sql = $this->db->query('SELECT race_id,relation FROM player_has_relation WHERE '.$this->SQL.' LIMIT '.sizeof($RACES));
			while ($this->db->nextRecord($r_sql))
			{
				$this->pureRelations[$this->db->getField('race_id')] = $this->db->getField('relation');
			}
		}
	}

	function increaseRelations($relations,$raceID)
	{
		if($relations < 0)
			throw new Exception('Trying to increase negative relations.');
		if($relations == 0)
			return;
		if($relations > 10)
			$relations=10;
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
		$this->getRelations();
		if($this->pureRelations[$raceID] == $relations)
			return;
		global $MIN_RELATIONS;
		if($relations < $MIN_RELATIONS)
			$relations = $MIN_RELATIONS;
		$this->pureRelations[$raceID] = $relations;
		$this->relations[$raceID] = round($relations + $this->getStat('Relations') / 2);
		$this->db->query('REPLACE INTO player_has_relation (account_id,game_id,race_id,relation) values ('.$this->getAccountID().','.$this->getGameID().','.$raceID.','.$this->pureRelations[$raceID].')');
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
//			$result = $this->db->query('SELECT good_id,amount,exp_gained FROM player_has_goods WHERE ' . $this->SQL);
//			while ($this->db->nextRecord($result))
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
			$sql = $this->db->query('SELECT gadget_id,equipped,cooldown,lasts_until FROM player_has_gadget WHERE ' . $this->SQL);
			$this->gadgets = array();
			while ($this->db->nextRecord($sql))
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
	
	
	function getCompletedMissions()
	{
		if(!isset($this->completedMissions))
		{
			//Get Player missions
			$sql = $this->db->query('SELECT mission_id FROM player_completed_mission WHERE ' . $this->SQL);
			$this->completedMissions = array();
			while ($this->db->nextRecord($sql))
				$this->completedMissions[$this->db->getField('mission_id')] = $this->db->getField('mission_id');
		}
		return $this->completedMissions;
	}
	
	function getMissions()
	{
		if(!isset($this->missions))
		{
			//Get Player missions
			global $MISSIONS;
			$sql = $this->db->query('SELECT * FROM player_has_mission WHERE ' . $this->SQL);
			$this->missions = array();
			while ($this->db->nextRecord($sql))
			{
				$this->missions[$this->db->getField('mission_id')] = array(
								'On Step' => $this->db->getField('on_step'),
								'Total' => $this->db->getField('total_steps'),
								'Progress' => $this->db->getField('progress'),
								'Next Step' => $this->db->getField('next_step'),
								'Expires' => $this->db->getField('step_fails'),
								'Mission ID' => $this->db->getField('mission_id'),
								'Sector' => $this->db->getField('mission_sector'),
								'Starting Sector' => $this->db->getField('starting_sector')
								);
				$this->rebuildMission($this->db->getField('mission_id'));
			}
		}
		return $this->missions;
	}
	
	function getMission($missionID)
	{
		$missions = $this->getMissions();
		if(isset($missions[$missionID]))
			return $missions[$missionID];
		return false;
	}
	
	function updateMission($missionID,$keyOrUpdateArray,$newInfo=false)
	{
		$this->getMissions();
		if(isset($this->missions[$missionID]))
		{
			if(is_array($keyOrUpdateArray))
			{
				$set = '';
				foreach($keyOrUpdateArray as $bit => $newInfo)
				{
					$field = $this->missionKeyToField($bit);
					if($field!==false)
					{
						$this->missions[$missionID][$bit]=$newInfo;
						$set .= $field.' = '.$this->db->escapeString($newInfo).',';
					}
				}
				if($set!=='')
				{
					$set=substr($set,0,-1);
					$this->db->query('UPDATE player_has_mission SET '.$set.' WHERE '.$this->SQL.' AND mission_id = '.$missionID.' LIMIT 1');
					$this->rebuildMission($missionID);
					return true;
				}
			}
			else
			{
				$field = $this->missionKeyToField($keyOrUpdateArray);
				if($field!==false)
				{
					$this->missions[$missionID][$keyOrUpdateArray]=$newInfo;
					$this->db->query('UPDATE player_has_mission SET '.$field.' = '.$newInfo.' WHERE '.$this->SQL.' AND mission_id = '.$missionID.' LIMIT 1');
					$this->rebuildMission($missionID);
					return true;
				}
			}
		}
		return false;
	}
	
	function addMission($missionID,$randomSector)
	{
		global $MISSIONS,$TIME;
		$this->getMissions();

		if(isset($this->missions[$missionID]))
			return;
			
		$mission = $MISSIONS[$missionID];
		$playerMission = array(
						'On Step' => 1,
						'Total' => sizeof($mission['Steps']),
						'Progress' => 0,
						'Next Step' => 'TRUE',
						'Expires' => ($TIME + 24 * 3600),
						'Mission ID' => $missionID,
						'Sector' => $randomSector,
						'Starting Sector' => $this->getSectorID()
						);
						
		$this->db->query('REPLACE INTO player_has_mission (game_id,account_id,mission_id,on_step,progress,next_step,total_steps,starting_sector,mission_sector,step_fails) ' . 
				'VALUES ('.$this->gameID.','.$this->accountID.','.$missionID.','.$playerMission['On Step'].','.$playerMission['Progress'].','.$this->db->escapeString($playerMission['Next Step']).','.$playerMission['Total'].','.$playerMission['Starting Sector'].','.$playerMission['Sector'].','.$playerMission['Expires'].')');
		$this->missions[$missionID] = $playerMission;
		$this->rebuildMission($missionID);
	}
	
	function rebuildMission($missionID)
	{
		global $MISSIONS;
		$RACES = Globals::getRaces();
		$missionInfo = $this->missions[$missionID];
		$currentStep = $MISSIONS[$missionID]['Steps'][$missionInfo['On Step']];
		$detail = str_replace(array('<Race>','<Sector>','<Starting Sector>','<trader>'),array($RACES[$this->getRaceID()],$missionInfo['Sector'],$missionInfo['Starting Sector'],$this->playerName),$currentStep['Detail']);
		$text = str_replace(array('<Race>','<Sector>','<Starting Sector>','<trader>'),array($RACES[$this->getRaceID()],$missionInfo['Sector'],$missionInfo['Starting Sector'],$this->playerName),$currentStep['Text']);
		$task = str_replace(array('<Race>','<Sector>','<Starting Sector>','<trader>'),array($RACES[$this->getRaceID()],$missionInfo['Sector'],$missionInfo['Starting Sector'],$this->playerName),$currentStep['Task']);
		if (isset($currentStep['Level']))
			$step_level = str_replace('<Player Level>',$this->levelID,$currentStep['Level']);
		else $step_level = 0;
		$this->missions[$missionID]['Name'] = $MISSIONS[$missionID]['Name'];
		$this->missions[$missionID]['Location'] = $MISSIONS[$missionID]['Location'];
		$this->missions[$missionID]['Task']['Step'] = $currentStep['Step'];
		$this->missions[$missionID]['Task']['Level'] = $step_level;
		$this->missions[$missionID]['Task']['Detail'] = $detail;
		$this->missions[$missionID]['Task']['Text'] = $text;
		$this->missions[$missionID]['Task']['Task'] = $task;
	}
	
	function deleteMission($missionID)
	{
		$this->getMissions();
		if(isset($this->missions[$missionID]))
		{
			unset($this->missions[$missionID]);
			$this->db->query('DELETE FROM player_has_mission WHERE '.$this->SQL.' AND mission_id = '.$missionID.' LIMIT 1');
			return true;
		}
		return false;
	}
	
	protected function missionKeyToField($bit)
	{
		$field = false;
		switch($bit)
		{
			case 'On Step':
				$field='on_step';
			break;
			case 'Total':
				$field='total_steps';
			break;
			case 'Progress':
				$field='progress';
			break;
			case 'Next Step':
				$field='next_step';
			break;
			case 'Expires':
				$field='step_fails';
			break;
			case 'Mission ID':
				$field='mission_id';
			break;
		}
		return $field;
	}
	
	function getMining($mineID=false)
	{
		if(!isset($this->mining))
		{
			//Get Player Mining Info
			$this->mining = array();
			$sql = $this->db->query('SELECT sector_has_mining.mine_id as mine_id, level, type, started, amount, starting_amount' . 
							' FROM player_is_mining, sector_has_mining WHERE sector_id = ' . $this->sectorID . 
							' AND sector_has_mining.game_id = player_is_mining.game_id' . 
							' AND sector_has_mining.mine_id = player_is_mining.mine_id' . 
							' AND account_id = ' . $this->accountID . ' AND sector_has_mining.game_id = ' . $this->gameID .' LIMIT 1');
			while ($this->db->nextRecord($sql))
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
			$sql = $this->db->query('SELECT * FROM player_knows_faction WHERE ' . $this->SQL . ' LIMIT 1');
			if ($this->db->nextRecord($sql))
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
		$sector =& SmrSector::getSector($this->getGameID(),$sectorID,$this->getAccountID());
		//msg taken care of in trader_att_proc.php
		// forget plotted course
		$this->deletePlottedCourse();

		$sector->died_here($this);
		
		// if we are in an alliance we increase their deaths
		if ($this->hasAlliance())
			$this->db->query('UPDATE alliance SET alliance_deaths = alliance_deaths + 1 ' .
						 'WHERE game_id = '.$this->getGameID().' AND alliance_id = '.$this->getAllianceID().' LIMIT 1');

		// record death stat
		$this->increaseHOF(1,'deaths');
		//record cost of ship lost
		$this->increaseHOF($this->getShip()->getCost(),'ship_cost_lost');
		// reset turns since last death
		$this->setHOF(0,'turns_used');

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
		$this->getShip()->get_pod();
	}
	
	public function getHome()
	{
		global $GOVERNMENT;

		// get his home sector
		$hq_id = $GOVERNMENT + $this->getRaceID();
		$this->db->query('SELECT location.sector_id FROM location NATURAL JOIN sector ' .
					 'WHERE location.game_id = '.$this->getGameID().' AND ' .
						   'location_type_id = '.$hq_id.' LIMIT 1');
		if ($this->db->nextRecord())
			return $this->db->getField('sector_id');
		else
			return 1;
	}
	
	public function &killPlayerByPlayer(AbstractSmrPlayer &$killer)
	{
		$return = array();
		$msg = $this->getDisplayName();
	
//		if(isset($ship_names[$killed_id])) {
//			$msg .= ' flying ';
//			if(!stristr($ship_names[$killed_id],'<img')){
//				if(stristr($ship_names[$killed_id],'<mar')) $msg .= '<span class="yellow">' . strip_tags($ship_names[$killed_id]) . '</span>';
//				else {
//					$msg .= $ship_names[$killed_id];
//				}
//			} else $msg .= strip_tags($ship_names[$killed_id], '<img>');
//		}
		$msg .= ' was destroyed by ' . $killer->getDisplayName();
//		if(isset($ship_names[$killer_id])) {
//			$msg .= ' flying ';
//			if(!stristr($ship_names[$killer_id],'<img')){
//				if(stristr($ship_names[$killer_id],'<mar')) $msg .= '<span class="yellow">' . strip_tags($ship_names[$killer_id]) . '</span>';
//				else {
//					$msg .= $ship_names[$killer_id];
//				}
//			} else $msg .= strip_tags($ship_names[$killer_id], '<img>');
//		}
		$msg .= ' in Sector&nbsp;#' . $this->getSectorID();
		$this->db->query('INSERT INTO news (game_id,time,news_message,type) VALUES (' . $this->getGameID() . ',' . TIME . ',' . $this->db->escapeString($msg,true) . ',\'regular\')');
		
		$killer->sendMessage($this->getAccountID(),MSG_PLAYER,'You were <span class="red">DESTROYED</span> by ' . $killer->getPlayerName() . ' in sector <span class="blue">#' . $this->getSectorID() . '</span>',false);
		$this->sendMessage($killer->getAccountID(),MSG_PLAYER,'You <span class="red">DESTROYED</span> ' . $this->getPlayerName() . ' in sector <span class="blue">#' . $this->getSectorID() . '</span>',false);
		
		// The person who got the kill gains experience
		$gainedExp = ceil(0.75*((((($this->getLevelID() - $killer->getLevelID()) / max($this->getLevelID(), $killer->getLevelID())) + 1) * 0.04 * $this->getExperience()) + (0.025 * $this->getExperience())));
		
		$return['KillerExp'] = $gainedExp;
		$killer->increaseExperience($gainedExp);
		$killer->increaseHOF($gainedExp,'kill_exp_gain');
		$killer->increaseHOF($this->getExperience(),'traders_killed_exp');
		// Dead player loses experience
		$expLossPercentage = 0.20 + (($this->getLevelID() - $killer->getLevelID()) * 0.005);
		$return['DeadExp'] = 0;
		if($expLossPercentage > 0)
		{
			$lostExp = floor($this->getExperience() * $expLossPercentage);
			$return['DeadExp'] = $lostExp;
			$this->decreaseExperience($lostExp);
		}
		
		$creditsGained = $this->getCredits();
		$return['KillerCredits'] = $creditsGained;
		$killer->increaseCredits($creditsGained);
		$killer->increaseHOF($creditsGained,'kill_money_gain');
		$killer->increaseHOF($this->getShip()->getCost(),'ships_killed_cost');

		// The killer may change alignment
		$relations = Globals::getRaceRelations($this->getGameID(),$killer->getRaceID());
		$relation = $relations[$this->getRaceID()];

		$alignChangePerRelation = 0.1;
		if($relation >= 300 || $relation <= -300)
			$alignChangePerRelation = 0.04;

		$alignChange = $relation * $alignChangePerRelation;
		$return['KillerAlign'] = $alignChange;
		if($alignChange>0)
			$killer->increaseAlignment($alignChange);
		else
			$killer->decreaseAlignment(-$alignChange);
		// War setting gives them military pay
		if($relation <= -300)
		{
			$killer->increaseMilitaryPayment(-floor($relation * 100 * (pow($gainedExp/2,0.25))));
		}
		
		//check for federal bounty being offered for current port raiders
		$allowed = TIME - 60 * 60 * 3;
		$this->db->query('DELETE FROM player_attacks_port WHERE time < '.$allowed);
		$query = 'SELECT count(*) as numAttacks
					FROM player_attacks_port, player, port
					WHERE player_attacks_port.game_id = port.game_id
					AND port.game_id = player.game_id
					AND armor > 0
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
				$multiplier = round(.4 * $this->getLevelID());
				$this->increaseCurrentBountyAmount('HQ',$numAttacks * 1000000 * $multiplier);
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
				$killer->increaseCurrentBountyAmount($bounty_type,floor(pow($alignment_diff, 2.56)));
			}
		}
		
		$killer->increaseKills(1);
		$killer->increaseHOF(1,'kills');

		if($this->hasAlliance())
		{
			$this->db->query('UPDATE alliance SET alliance_deaths=alliance_deaths+1 WHERE alliance_id=' . $this->getAllianceID() . ' AND game_id=' . $this->getGameID() . ' LIMIT 1');
		}
		if($killer->hasAlliance())
		{
			$this->db->query('UPDATE alliance SET alliance_kills=alliance_kills+1 WHERE alliance_id=' . $killer->getAllianceID() . ' AND game_id=' . $killer->getGameID() . ' LIMIT 1');
		}

		if($this->hasAlliance() && $killer->hasAlliance())
		{
			$this->db->query('SELECT kills FROM alliance_vs_alliance WHERE alliance_id_1=' . $killer->getAllianceID() . ' AND alliance_id_2=' . $this->getAllianceID() . ' AND game_id=' . $this->getGameID() . ' LIMIT 1');
			if($this->db->nextRecord())
			{
				$this->db->query('UPDATE alliance_vs_alliance SET kills=kills+1 WHERE alliance_id_1=' . $this->getAllianceID() . ' AND alliance_id_2=' . $this->getAllianceID() . ' AND game_id=' . $this->getGameID() . ' LIMIT 1');
			}
			else
			{
				$this->db->query('INSERT INTO alliance_vs_alliance VALUES (' . $this->getGameID() . ',' . $killer->getAllianceID() . ',' . $this->getAllianceID() . ',1)');
			}
		}
		
		
		$this->killPlayer($this->getSectorID());
		return $return;
	}
	
	public function killPlayerByForces(SmrForce &$forces)
	{
		global $PLAYERMSG;
		
		$owner =& $forces->getOwner();
		// send a message to the person who died
		$this->sendMessage($owner->getAccountID(), $PLAYERMSG, $this->db->escapeString('Your forces <span style="color:red;">DESTROYED</span> '.$this->getPlayerName().' in sector '.$forces->getSectorID()),false);
		$owner->sendMessage($this->getAccountID(), $PLAYERMSG, $this->db->escapeString('You were <span style="color:red;">DESTROYED</span> by forces in sector '.$forces->getSectorID()),false);

		$news_message = $this->getDisplayName();
		$db2 = new SMR_DB();
		$db = new SMR_DB();
		$db2->query('SELECT * FROM ship_has_name WHERE game_id = '.$this->getGameID().' AND ' .
					'account_id = '.$this->getAccountID());
		if ($db2->next_record())
		{
			
			//they have a name so we echo it
			$db->query('SELECT ' . htmlentities($db2->f('ship_name')) . ' LIKE \'&lt;img%\'');
			$named_ship = stripslashes($db2->f('ship_name'));
			$named_ship = strip_tags($db2->f('ship_name'), '<font><span>');
			if ($db->next_record() && $db->f(0) != 0) {
				//nothing
			} else
				$news_message .= ' flying <font color="yellow">'.$named_ship.'</font>';
			
		}
		$news_message .= ' was destroyed ' .
						'by forces in sector&nbsp#'.$forces->getSectorID();
		// insert the news entry
		$this->db->query('INSERT INTO news ' .
					 '(game_id, time, news_message) ' .
					 'VALUES('.$this->getGameID().', ' . time() . ', ' . $this->db->escapeString($news_message) . ')');
		
		$percentage = 33;
		if ($percentage > 0 )
			$this->decreaseExperience($this->getExperience() * $percentage / 100);
		$this->db->query('SELECT * FROM alliance_vs_alliance WHERE alliance_id_1 = -1 AND ' . 
						'alliance_id_2 = '.$this->getAllianceID().' AND game_id = '.$this->getGameID());
		if ($this->db->next_record()) $this->db->query('UPDATE alliance_vs_alliance SET kills = kills + 1 ' .
											'WHERE alliance_id_1 = -1 AND alliance_id_2 = '.$this->getAllianceID().' AND ' .
											'game_id = '.$this->getGameID());
		else $this->db->query('REPLACE INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) ' .
						'VALUES ('.$this->getGameID().', -1, '.$this->getAllianceID().', 1)');
		
		
		$this->killPlayer($forces->getSectorID());
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
				', player_id='.$this->db->escapeString($this->playerID).
				', sector_id='.$this->db->escapeString($this->sectorID).
				', last_sector_id='.$this->db->escapeString($this->lastSectorID).
				', turns='.$this->db->escapeString($this->turns).
				', last_turn_update='.$this->db->escapeString($this->lastMaintenanceUpdate).
				', newbie_turns='.$this->db->escapeString($this->newbieTurns).
				', last_ship_mod='.$this->db->escapeString($this->lastShipMod).
				', last_news_update='.$this->db->escapeString($this->lastNewsUpdate).
				', stunned='.$this->db->escapeString($this->stunned).
				', fleed='.$this->db->escapeBoolean($this->fleed).
				', attack_warning='.$this->db->escapeString($this->attackColor).
				', dead='.$this->db->escapeBoolean($this->dead).
				', land_on_planet='.$this->db->escapeBoolean($this->landedOnPlanet).
				', kicked='.$this->db->escapeBoolean($this->kicked).
				', last_shield_update='.$this->db->escapeString($this->lastShieldUpdate).
				', last_active='.$this->db->escapeString($this->lastActive).
				', last_cpl_action='.$this->db->escapeString($this->lastCPLAction).
				', race_id='.$this->db->escapeString($this->raceID).
				', credits='.$this->db->escapeString($this->credits).
				', experience='.$this->db->escapeString($this->experience).
				', alignment='.$this->db->escapeString($this->alignment).
				', military_payment='.$this->db->escapeString($this->militaryPayment).
//				', past_knowledge='.$this->db->escapeString($this->pastKnowledge).
				', government_help='.$this->db->escapeString($this->governmentHelp).
				', alliance_id='.$this->db->escapeString($this->allianceID).
//				', alliance_join='.$this->db->escapeString($this->allianceJoinable).
				', ship_type_id='.$this->db->escapeString($this->shipID).
				', kills='.$this->db->escapeString($this->kills).
				', deaths='.$this->db->escapeString($this->deaths).
				', last_port='.$this->db->escapeString($this->lastPort).
				', bank='.$this->db->escapeString($this->bank).
				', zoom='.$this->db->escapeString($this->zoom).
//				', chat='.$this->db->escapeString($this->chat).
//				', chat_position='.$this->db->escapeString($this->chatPosition).
				', display_missions='.$this->db->escapeBoolean($this->displayMissions).
				', ignore_globals='.$this->db->escapeBoolean($this->ignoreGlobals).
//				', changed_stats='.$this->db->escapeString($this->changedStats).
				', newbie_warning = ' . $this->db->escapeBoolean($this->newbieWarning).
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
					if($bounty['Amount'] > 0)
						$this->db->query('INSERT INTO bounty (account_id,game_id,type,amount,claimer_id,time) VALUES (' . $this->getAccountID() . ',' . $this->getGameID() . ',' . $this->db->escapeString($bounty['Type']) . ',' . $bounty['Amount'] .',' . $bounty['Claimer'] . ',' . $bounty['Time'] .')');
				}
				else
				{
					if($bounty['Amount'] > 0)
						$this->db->query('UPDATE bounty 
							SET amount=' . $bounty['Amount'] . ',
							type=' . $this->db->escapeString($bounty['Type']) . ',
							claimer_id=' . $bounty['Claimer'] . ',
							time=' . $bounty['Time'] . '
							WHERE bounty_id=' . $bounty['ID'] . ' AND account_id = ' . $this->getAccountID() . ' AND game_id = ' . $this->getGameID() . ' LIMIT 1');
					else
						$this->db->query('DELETE FROM bounty WHERE bounty_id=' . $bounty['ID'] . ' AND account_id = ' . $this->getAccountID() . ' AND game_id = ' . $this->getGameID() . ' LIMIT 1');
				}
			}
		}
	}
	
	protected function getHOFData()
	{
		if(!isset($this->HOF))
		{
			//Get Player HOF
			$result = $this->db->query('SELECT * FROM player_hof WHERE ' . $this->SQL .' LIMIT 1');
			$this->HOF = array();
			if($this->db->nextRecord($result))
			{
				foreach($this->db->getRow() as $key => $value)
				{
					if($key == 'account_id' || $key == 'game_id')
						continue;
					if (strpos($key,'-'))
					{
						list($type,$subtype) = explode('-',$key);
						$this->HOF[$type][$subtype] = $value;
					}
					else
					{
						$this->HOF[$key] = $value;
					}
				}
			}
		}
	}
	
	protected function getBountiesData()
	{
		if(!isset($this->bounties))
		{
			$this->bounties = array();
			$this->db->query('SELECT bounty_id,claimer_id,amount,type,time FROM bounty WHERE account_id = ' . $this->getAccountID() . ' AND game_id=' . $this->getGameID());
			while($this->db->nextRecord())
			{
				$this->bounties[$this->db->getField('bounty_id')] = array('Amount' => $this->db->getField('amount'),
							'Type' => $this->db->getField('type'),
							'Claimer' => $this->db->getField('claimer_id'),
							'Time' => $this->db->getField('time'),
							'ID' => $this->db->getField('bounty_id'),
							'New' => false);
			}
		}
	}
	
	function isInCouncil()
	{
		$president = false;
		$sql = $this->db->query('SELECT account_id,alignment FROM player WHERE game_id = '.$this->gameID.' AND race_id = '.$this->getRaceID().' AND experience > 0 ORDER BY experience DESC, player_name LIMIT 20');
		while ($this->db->nextRecord($sql))
		{
			if ($this->db->getField('alignment') >= 150 && $president == false) $president = $this->db->getField('account_id');
			if ($this->db->getField('account_id') == $this->accountID)
			{
				if ($president == $this->accountID) return 'President';
				else return 'Member';
			}
		}
		return false;
	}
	
	
	
	public function isValidated()
	{
		return $this->validated;
	}
	
	public function setNewbieWarning($bool)
	{
		$this->newbieWarning=$bool;
//		$query = 'UPDATE player SET newbie_warning = ' . $this->db->escapeBoolean($this->newbieWarning);
		$this->hasChanged=true;
	}
	
	public function getNewbieWarning()
	{
		return $this->newbieWarning;
	}
	
	public function getTicker()
	{
		if(!isset($this->ticker))
		{
			//get ticker info
			$this->db->query('SELECT type FROM player_has_ticker WHERE account_id = ' . $this->SQL .' AND type != '.$this->db->escapeString('BLOCK').' LIMIT 1');
			if ($this->db->nextRecord()) $this->ticker = strtoupper($this->db->getField('type'));
			else $this->ticker = false;
		}
		return $this->ticker;
	}
	
	public function hasTicker()
	{
	 	return $this->getTicker()!== false && $this->getTicker() != 'BLOCK';
	}
	
	public function getTurnsLevel()
	{
		if ($this->getTurns()     <= (300 - round($this->getStat('Mechanics') / 4) ) / 2 ) return 'LOW';
		else if ($this->getTurns() <= (300 - round($this->getStat('Mechanics') / 4)) ) return 'MEDIUM';
		else return 'HIGH';
	}
	
	public function canSeeAny(array $otherPlayerArray)
	{
		$canSee=false;
		foreach($otherPlayerArray as $otherPlayer)
		{
			if($this->canSee($otherPlayer))
				$canSee = true;
		}
		return $canSee;
	}
	
	public function canSee(AbstractSmrPlayer $otherPlayer)
	{
		if($this->getLevelID()>$otherPlayer->getLevelID())
			return true;
		if($otherPlayer->getShip()->isCloaked())
			return false;
		return true;
	}
	
	public function update()
	{
		$this->save();
	}
	
	function sendMessage($receiver_id, $message_type_id, $message, $canBeIgnored = true)
	{
		//get expire time
		global $GLOBALMSG, $PLAYERMSG, $PLANETMSG, $SCOUTMSG, $POLITICALMSG, $ALLIANCEMSG, $ADMINMSG;
		
		if($canBeIgnored)
		{
			// Don't send messages to players ignoring us
			$this->db->query('SELECT account_id FROM message_blacklist WHERE account_id=' . $receiver_id . ' AND blacklisted_id=' . $this->accountID . ' AND game_id = ' . $this->gameID . ' LIMIT 1');
			if($this->db->nextRecord()) {
				return;
			}		
		}
				
		$message = word_filter($message);
		
		if ($message_type_id == $GLOBALMSG) $expire = 3600;
		elseif ($message_type_id == $PLAYERMSG) $expire = 3600 * 24 * 7;
		elseif ($message_type_id == $PLANETMSG) $expire = 3600 * 24 * 3;
		elseif ($message_type_id == $SCOUTMSG) $expire = 3600 * 24 * 3;
		elseif ($message_type_id == $POLITICALMSG) $expire = 3600 * 24 * 2;
		elseif ($message_type_id == $ALLIANCEMSG) $expire = 3600 * 24 * 2;
		elseif ($message_type_id == $ADMINMSG) $expire = 3600 * 24 * 7;
		else $expire = 3600 * 24 * 2;
		$expire += time();
		// send him the message
		$this->db->query('INSERT INTO message
			(account_id,game_id,message_type_id,message_text,
			sender_id,send_time,expire_time) VALUES(' . 
			$receiver_id . ',' .
			$this->getGameID() . ',' .
			$message_type_id . ',' .
			$this->db->escapeString($message,true) . ',' .
			$this->getAccountID() . ',' .
			TIME . ',' .
			$expire . ')'
		);

		// give him the message icon
		$this->db->query('REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES ' .
						'('.$this->gameID.', '.$receiver_id.', '.$message_type_id.')');

	}
	
	function setMessagesRead($messageTypeID)
	{

		$this->db->query('DELETE FROM player_has_unread_messages WHERE '.$this->SQL.' AND ' .
					'message_type_id = '.$messageTypeID);
	}
	
	public function getPlottedCourse()
	{
		if(!isset($this->plottedCourse))
		{
			// check if we have a course plotted
			$this->db->query('SELECT course,distance FROM player_plotted_course 
			WHERE account_id=' . $this->getAccountID() . '
			AND game_id=' . $this->getGameID() . '
			LIMIT 1'
			);
			
			if ($this->db->nextRecord())
			{
				// get the array back
				$course	= unserialize($this->db->getField('course'));
			
			
				$this->plottedCourse = array('Course' => $course,
							'Length' => $this->db->getField('distance'));
			
				// get the first sector in that list
				$this->plottedCourse['NextSector'] = current($this->plottedCourse['Course']);

				$plottedCourse = $this->getPlottedCourse();
				$sectors = '';
				$first=true;
				foreach($this->plottedCourse['Course'] as $nextSec)
				{
					if($first)
					{
						$sectors .= $nextSec;
						$first=false;
					}
					else
						$sectors .= ' - '.$nextSec;
				}
				$this->plottedCourse['CourseString'] = $sectors;
			}
			else
				return false;
		}
		return $this->plottedCourse;
	}
	
	public function hasPlottedCourse()
	{
		return $this->getPlottedCourse()!==false;
	}
	
	function isPartOfCourse($sectorOrSectorID)
	{
		if(!$this->hasPlottedCourse())
			return false;
		if($sectorOrSectorID instanceof SmrSector )
		{
			$sectorID = $sectorOrSectorID->getSectorID();
		}
		else
		{
			$sectorID = $sectorOrSectorID;
		}
		$course = $this->getPlottedCourse();
		foreach($course['Course'] as $plotSector)
		{
			if($plotSector == $sectorID)
				return true;
		}
		return false;
	}
	
	function deletePlottedCourse()
	{
		$this->db->query('DELETE FROM player_plotted_course ' .
					 'WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public function getExamineTraderHREF()
	{
		if(!isset($this->examineTraderHREF))
		{
			$container = array();
			$container['url']		= 'skeleton.php';
			$container['body']		= 'trader_examine.php';
			$container['target']	= $this->getAccountID();
			$this->examineTraderHREF = SmrSession::get_new_href($container);
		}
		return $this->examineTraderHREF;
	}
	
	public function getAttackTraderHREF()
	{
		if(!isset($this->attackTraderHREF))
		{
			$container = array();
			$container['url']		= 'skeleton.php';
			$container['body']		= 'trader_attack_processing_new.php';
			$container['target']	= $this->getAccountID();
			$this->attackTraderHREF = SmrSession::get_new_href($container);
		}
		return $this->attackTraderHREF;
	}
	
	public function getTraderSearchHREF()
	{
		if(!isset($this->traderSearchHREF))
		{
			$container = array();
			$container['url']		= 'skeleton.php';
			$container['body']		= 'trader_search_result.php';
			$container['player_id']	= $this->getPlayerID();
			$this->traderSearchHREF = SmrSession::get_new_href($container);
		}
		return $this->traderSearchHREF;
	}
	
	public function getAllianceRosterHREF()
	{
		if(!isset($this->allianceRosterHREF))
		{
			$container = array();
			$container['url']			= 'skeleton.php';
			$container['body']			= 'alliance_roster.php';
			$container['alliance_id']	= $this->getAllianceID();
			$this->allianceRosterHREF = SmrSession::get_new_href($container);
		}
		return $this->allianceRosterHREF;
	}
	
	public function equals(AbstractSmrPlayer $otherPlayer)
	{
		return $this->getAccountID()==$otherPlayer->getAccountID()&&$this->getGameID()==$otherPlayer->getGameID();
	}
	
	public function sameAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->equals($otherPlayer) || ($this->getAllianceID()>0&&$this->getAllianceID()==$otherPlayer->getAllianceID());
	}
	
	public function sharedForceAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->sameAlliance($otherPlayer);
	}
	
	public function forceNAPAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->sameAlliance($otherPlayer);
	}
	
	public function traderNAPAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->sameAlliance($otherPlayer);
	}
	
	public function traderAttackTraderAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->sameAlliance($otherPlayer);
	}
	
	public function traderDefendTraderAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->sameAlliance($otherPlayer);
	}
	
	public function meetsAlignmentRestriction($restriction)
	{
		if($restriction<0)
			return $this->getAlignment()<=$restriction;
		if($restriction>0)
			return $this->getAlignment()>=$restriction;
		return true;
	}
}
?>