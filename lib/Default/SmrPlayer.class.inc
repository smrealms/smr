<?php

// Exception thrown when a player cannot be found in the database
class PlayerNotFoundException extends Exception {}

require_once(get_file_loc('AbstractSmrPlayer.class.inc'));
require_once(get_file_loc('council.inc'));
class SmrPlayer extends AbstractSmrPlayer {
	const TIME_FOR_FEDERAL_BOUNTY_ON_PR = 10800;
	const TIME_FOR_ALLIANCE_SWITCH = 0;
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
	protected $attackColour;
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
	protected $ignoreGlobals;
//	protected $changedStats;
	protected $mining;
	protected $plottedCourse;
	protected $nameChanged;
	protected $combatDronesKamikazeOnMines;

	protected $customShipName;

	protected $SQL;


	public static function refreshCache() {
		foreach(self::$CACHE_PLAYERS as $gameID => &$gamePlayers) {
			foreach($gamePlayers as $accountID => &$player) {
				$player = self::getPlayer($accountID,$gameID,true);
			}
		}
	}

	public static function clearCache() {
		self::$CACHE_PLAYERS = array();
		self::$CACHE_SECTOR_PLAYERS = array();
	}

	public static function savePlayers() {
		foreach(self::$CACHE_PLAYERS as $gamePlayers) {
			foreach($gamePlayers as $player) {
				$player->save();
			}
		}
	}

	public static function &getSectorPlayersByAlliances($gameID,$sectorID, array $allianceIDs, $forceUpdate = false) {
		$players = self::getSectorPlayers($gameID,$sectorID,$forceUpdate); // Don't use & as we do an unset
		foreach($players as $accountID => $player) {
			if(!in_array($player->getAllianceID(),$allianceIDs))
				unset($players[$accountID]);
		}
		return $players;
	}

	public static function &getSectorPlayers($gameID,$sectorID,$forceUpdate = false) {
		if($forceUpdate || !isset(self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID])) {
			$db = new SmrMySqlDatabase();
			$db->query('SELECT * FROM player WHERE sector_id = ' . $db->escapeNumber($sectorID) . ' AND game_id=' . $db->escapeNumber($gameID) . ' AND land_on_planet = ' . $db->escapeBoolean(false) . ' AND (last_cpl_action > ' . $db->escapeNumber(TIME-TIME_BEFORE_INACTIVE) . ' OR newbie_turns = 0) AND account_id NOT IN (' . $db->escapeArray(Globals::getHiddenPlayers()) . ') ORDER BY last_cpl_action DESC');
			$players = array();
			while($db->nextRecord()) {
				$accountID = $db->getInt('account_id');
				$players[$accountID] = self::getPlayer($db->getRow(), $gameID, $forceUpdate);
			}
			self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID] = $players;
		}
		return self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID];
	}

	public static function &getPlanetPlayers($gameID,$sectorID,$forceUpdate = false) {
		if($forceUpdate || !isset(self::$CACHE_PLANET_PLAYERS[$gameID][$sectorID])) {
			$db = new SmrMySqlDatabase();
			$db->query('SELECT * FROM player WHERE sector_id = ' . $db->escapeNumber($sectorID) . ' AND game_id=' . $db->escapeNumber($gameID) . ' AND land_on_planet = ' . $db->escapeBoolean(true) . ' AND account_id NOT IN (' . $db->escapeArray(Globals::getHiddenPlayers()) . ') ORDER BY last_cpl_action DESC');
			$players = array();
			while($db->nextRecord()) {
				$accountID = $db->getField('account_id');
				$players[$accountID] = self::getPlayer($db->getRow(), $gameID, $forceUpdate);
			}
			self::$CACHE_PLANET_PLAYERS[$gameID][$sectorID] = $players;
		}
		return self::$CACHE_PLANET_PLAYERS[$gameID][$sectorID];
	}

	public static function &getAlliancePlayers($gameID,$allianceID,$forceUpdate = false) {
		if($forceUpdate || !isset(self::$CACHE_ALLIANCE_PLAYERS[$gameID][$allianceID])) {
			$db = new SmrMySqlDatabase();
			$db->query('SELECT * FROM player WHERE alliance_id = ' . $db->escapeNumber($allianceID) . ' AND game_id=' . $db->escapeNumber($gameID) .' ORDER BY experience DESC');
			$players = array();
			while($db->nextRecord()) {
				$accountID = $db->getInt('account_id');
				$players[$accountID] = self::getPlayer($db->getRow(), $gameID, $forceUpdate);
			}
			self::$CACHE_ALLIANCE_PLAYERS[$gameID][$allianceID] = $players;
		}
		return self::$CACHE_ALLIANCE_PLAYERS[$gameID][$allianceID];
	}

	public static function &getPlayer($accountIDOrResultArray, $gameID, $forceUpdate = false) {
		$accountID = is_array($accountIDOrResultArray) ?
		             $accountIDOrResultArray['account_id'] :
		             $accountIDOrResultArray;
		if ($forceUpdate || !isset(self::$CACHE_PLAYERS[$gameID][$accountID])) {
			$p = new SmrPlayer($gameID,$accountIDOrResultArray);
			self::$CACHE_PLAYERS[$gameID][$p->getAccountID()] = $p;
		}
		return self::$CACHE_PLAYERS[$gameID][$accountID];
	}

	public static function &getPlayerByPlayerID($playerID,$gameID,$forceUpdate = false) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT account_id FROM player WHERE game_id = '.$db->escapeNumber($gameID).' AND player_id = '.$db->escapeNumber($playerID).' LIMIT 1');
		if($db->nextRecord())
			return self::getPlayer($db->getInt('account_id'),$gameID,$forceUpdate);
		throw new PlayerNotFoundException('Player ID not found.');
	}

	protected function __construct($gameID, $accountIDOrResultArray) {
		parent::__construct();
		$this->db = new SmrMySqlDatabase();
		$result=false;
		if (is_array($accountIDOrResultArray))
			$result = $accountIDOrResultArray;
		else {
			$this->db->query('SELECT * FROM player WHERE account_id = ' . $this->db->escapeNumber($accountIDOrResultArray) . ' AND game_id = ' . $this->db->escapeNumber($gameID) . ' LIMIT 1');
			$this->db->nextRecord();
			$result = $this->db->getRow();
		}
		if($result) {
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
			$this->attackColour					= (string) $result['attack_warning'];
			$this->dead							= $result['dead']=='TRUE';
			$this->npc							= $result['npc']=='TRUE';
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

			$this->SQL = 'account_id = ' . $this->db->escapeNumber($this->accountID) . ' AND game_id = ' . $this->db->escapeNumber($this->gameID);
		}
		else {
			throw new PlayerNotFoundException('Invalid accountID: '.$accountIDOrResultArray . ' OR gameID:'.$gameID);
		}
	}

	// Get array of players whose info can be accessed by this player.
	// Skips players who are not in the same alliance as this player.
	public function getSharingPlayers($forceUpdate=false) {
		$results = array($this);

		// Only return this player if not in an alliance
		if (!$this->hasAlliance()) {
			return $results;
		}

		// Get other players who are sharing info for this game.
		// NOTE: game_id=0 means that player shares info for all games.
		$this->db->query('SELECT from_account_id FROM account_shares_info WHERE to_account_id=' . $this->db->escapeNumber($this->getAccountID()) . ' AND (game_id=0 OR game_id=' . $this->db->escapeNumber($this->getGameID()) . ')');
		while ($this->db->nextRecord()) {
			try {
				$otherPlayer = SmrPlayer::getPlayer($this->db->getInt('from_account_id'),
				                                    $this->getGameID(), $forceUpdate);
			} catch (PlayerNotFoundException $e) {
				// Skip players that have not joined this game
				continue;
			}

			// players must be in the same alliance
			if ($this->sameAlliance($otherPlayer)) {
				$results[] = $otherPlayer;
			}
		}
		return $results;
	}

	public function getSQL() {
		return $this->SQL;
	}

	function get($bit) {
		switch($bit) {
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
			case 'Attack Colour':
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
			case 'Span Colour':
				return $this->getSpanColour();
//			case 'Ship Size':
//				return $this->getShipSize();

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

	public function &getShip($forceUpdate = false) {
		return SmrShip::getShip($this, $forceUpdate);
	}

	public function &getAccount() {
		require_once(get_file_loc('SmrAccount.class.inc'));
		return SmrAccount::getAccount($this->getAccountID());
	}

	public function getKicked() {
		return $this->kicked;
	}

	public function setKicked($bool) {
		if($this->kicked == $bool)
			return;
		$this->kicked=$bool;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET kicked = '.$this->db->escapeBoolean($bool).' WHERE ' . $this->SQL . ' LIMIT 1');
	}

	function getStunned() {
		return $this->stunned;
	}

	function setStunned($time) {
		if($this->stunned == $time)
			return;
		$this->stunned=$time;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET stunned = ' . $this->stunned . ' WHERE ' . $this->SQL . ' LIMIT 1');
	}

	function getFleed() {
		return $this->fleed;
	}

	function setFleed($bool) {
		if($this->fleed == $bool)
			return;
		$this->fleed=$bool;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET fleed='.$this->db->escapeBoolean($bool).' WHERE ' . $this->SQL . ' LIMIT 1');
	}

	function getZoom() {
		return $this->zoom;
	}

	function setZoom($zoom) {
		if ($zoom < 0) $zoom = 0;
		if ($zoom > 12) $zoom = 12;
		if($this->zoom == $zoom)
			return;
		$this->zoom=$zoom;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET zoom = ' . $zoom . ' WHERE ' . $this->SQL . ' LIMIT 1');
	}

	function increaseZoom($zoom) {
		if ($zoom < 0)
			throw new Exception('Trying to increase negative zoom.');
		$this->setZoom($this->getZoom()+$zoom);
	}

	function decreaseZoom($zoom) {
		if ($zoom < 0)
			throw new Exception('Trying to decrease negative zoom.');
		$this->setZoom($this->getZoom()-$zoom);
	}

//	function isZoomOn() {
//		return $this->zoomOn;
//	}

	public function setSectorID($sectorID) {
		require_once('SmrPort.class.inc');
		$port = SmrPort::getPort($this->getGameID(),$this->getSectorID());
		$port->addCachePort($this->getAccountID()); //Add port of sector we were just in, to make sure it is left totally up to date.

		parent::setSectorID($sectorID);

		$port = SmrPort::getPort($this->getGameID(),$sectorID);
		$port->addCachePort($this->getAccountID()); //Add the port of sector we are now in.
	}

	function setLastSectorID($lastSectorID) {
		if($this->lastSectorID == $lastSectorID)
			return;
		$this->lastSectorID=$lastSectorID;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET last_sector_id = '.$this->lastSectorID.' WHERE '.$this->SQL.' LIMIT 1');
	}

	function leaveAlliance(AbstractSmrPlayer $kickedBy = null) {
		$allianceID = $this->getAllianceID();
		$alliance = $this->getAlliance();
		if($kickedBy != null) {
			$kickedBy->sendMessage($this->getAccountID(), MSG_PLAYER, 'You were kicked out of the alliance!', false);
			$this->actionTaken('PlayerKicked', array('Alliance' => $alliance, 'Player' => $kickedBy));
			$kickedBy->actionTaken('KickPlayer', array('Alliance' => $alliance, 'Player' => $this));
		}
		else if($this->isAllianceLeader()) {
			$this->actionTaken('DisbandAlliance', array('Alliance' => $alliance));
		}
		else {
			$this->actionTaken('LeaveAlliance', array('Alliance' => $alliance));
			if ($alliance->getLeaderID() != 0 && $alliance->getLeaderID() != ACCOUNT_ID_NHL) {
				$this->sendMessage($alliance->getLeaderID(), MSG_PLAYER, 'I left your alliance!', false);
			}
		}

		$this->setAllianceID(0);
		$this->db->query('DELETE FROM player_has_alliance_role WHERE ' . $this->SQL);

		if(!$this->isAllianceLeader() && $allianceID!=NHA_ID) { // Don't have a delay for switching alliance after leaving NHA, or for disbanding an alliance.
			$this->setAllianceJoinable(TIME + self::TIME_FOR_ALLIANCE_SWITCH);
			$alliance->getLeader()->setAllianceJoinable(TIME + self::TIME_FOR_ALLIANCE_SWITCH); //We set the joinable time for leader here, that way a single player alliance won't cause a player to wait before switching.
		}
	}

	function joinAlliance($allianceID) {
		$this->setAllianceID($allianceID);
		$alliance = $this->getAlliance();
		if (!$this->isAllianceLeader())
			$this->sendMessage($alliance->getLeaderID(), MSG_PLAYER, 'I joined your alliance!', false);
		$this->db->query('INSERT INTO player_has_alliance_role (game_id, account_id, role_id, alliance_id) VALUES (' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($this->getAccountID()) . ', 2,' . $this->db->escapeNumber($alliance->getAllianceID()) . ')');
		$this->actionTaken('JoinAlliance', array('Alliance' => $alliance));
	}

	function getAllianceJoinable() {
		return $this->allianceJoinable;
	}

	function setAllianceJoinable($time) {
		if($this->allianceJoinable == $time)
			return;
		$this->allianceJoinable=$time;
		$this->hasChanged=true;
	}

	function getAttackColour() {
		return $this->attackColour;
	}

	function setAttackColour($colour) {
		if($this->attackColour == $colour)
			return;
		$this->attackColour=$colour;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET attack_warning = ' . $this->db->escapeString($this->attackColour) . ' WHERE ' . $this->SQL . ' LIMIT 1');
	}

	function getBank() {
		return $this->bank;
	}

	function increaseBank($credits) {
		if($credits < 0)
			throw new Exception('Trying to increase negative credits.');
		if($credits == 0)
			return;
		$credits += $this->bank;
		$this->setBank($credits);
	}
	function decreaseBank($credits) {
		if($credits < 0)
			throw new Exception('Trying to decrease negative credits.');
		if($credits == 0)
			return;
		$credits = $this->bank - $credits;
		$this->setBank($credits);
	}
	function setBank($credits) {
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

	function getLastNewsUpdate() {
		return $this->lastNewsUpdate;
	}

	function setLastNewsUpdate($time) {
		if($this->lastNewsUpdate == $time)
			return;
		$this->lastNewsUpdate=$time;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET last_news_update = ' . $time . ' WHERE ' . $this->SQL . ' LIMIT 1');
	}

	function updateLastNewsUpdate() {
		$this->setLastNewsUpdate(TIME);
	}

	function getGovernmentHelp() {
		return $this->governmentHelp;
	}

//	function getPastKnowledge() {
//		return $this->pastKnowledge;
//	}

//	function getChangedStats() {
//		return $this->changedStats;
//	}
//
//	function setChangedStats($boolStr) {
//		if($this->changedStats == $boolStr)
//			return;
//		$this->changedStats=$boolStr;
//		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET changed_stats = '.$this->db->escapeString($boolStr).' WHERE '.$this->SQL.' LIMIT 1');
//	}

//	function getChat() {
//		return $this->chat;
//	}
//
//	function setChat($chat) {
//		if($this->chat == $chat)
//			return;
//		$this->chat=$chat;
//		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET chat = ' . $this->db->escapeString($chat) . ' WHERE ' . $this->SQL . ' LIMIT 1');
//	}
//
//	function getChatPosition() {
//		return $this->chatPosition;
//	}
//
//	function setChatPosition($chatPosition) {
//		if($this->chatPosition == $chatPosition)
//			return;
//		$this->chatPosition=$chatPosition;
//		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET chat_position = ' . $this->db->escapeString($chatPosition) . ' WHERE ' . $this->SQL . ' LIMIT 1');
//	}

	function getOriginalMaintenance() {
		return $this->originalMaintenance;
	}

	/**
	 * Calculate the time in seconds between the given time and when the
	 * player will be at max turns.
	 */
	public function getTimeUntilMaxTurns($time, $forceUpdate=false) {
		$timeDiff = $time - $this->getLastTurnUpdate();
		$turnsDiff = $this->getMaxTurns() - $this->getTurns();
		$ship = $this->getShip($forceUpdate);
		$maxTurnsTime = ceil(($turnsDiff * 3600 / $ship->getRealSpeed())) - $timeDiff;
		// If already at max turns, return 0
		return max(0, $maxTurnsTime);
	}

	// Turns only update when player is active.
	// Calculate turns gained between given time and the last turn update
	public function getTurnsGained($time, $forceUpdate=false) {
		$timeDiff = $time - $this->getLastTurnUpdate();
		$ship = $this->getShip($forceUpdate);
		$extraTurns = floor($timeDiff * $ship->getRealSpeed() / 3600);
		return $extraTurns;
	}

	public function updateTurns() {
		// is account validated?
		if (!$this->getAccount()->isValidated()) return;

		// how many turns would he get right now?
		$extraTurns = $this->getTurnsGained(TIME);

		// do we have at least one turn to give?
		if ($extraTurns > 0) {
			// recalc the time to avoid errors
			$ship = $this->getShip();
			$newLastTurnUpdate = $this->getLastTurnUpdate() + ceil($extraTurns * 3600 / $ship->getRealSpeed());

			$startTurnsDate = $this->getGame()->getStartTurnsDate();
			if($startTurnsDate < $newLastTurnUpdate && $startTurnsDate >= $this->getLastTurnUpdate()) {
				$startingTurns = min($ship->getRealSpeed() * $this->getGame()->getStartTurnHours(), $this->getMaxTurns() - $this->getTurns());
				$extraUpdateTime = (ceil($startingTurns) - $startingTurns) * 3600 / $ship->getRealSpeed();
				$extraTurns += $startingTurns;
				$newLastTurnUpdate += $extraUpdateTime;
			}

			$this->setLastTurnUpdate($newLastTurnUpdate);
			$this->giveTurns($extraTurns);
		}
	}

	function isIgnoreGlobals() {
		return $this->ignoreGlobals;
	}

	function setIgnoreGlobals($bool) {
		if($this->ignoreGlobals == $bool)
			return;
		$this->ignoreGlobals=$bool;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET ignore_globals = '.$this->db->escapeBoolean($bool).' WHERE '.$this->SQL.' LIMIT 1');
	}


	function getLastPort() {
		return $this->lastPort;
	}

	function setLastPort($lastPort) {
		if($this->lastPort == $lastPort)
			return;
		$this->lastPort=$lastPort;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET last_port = ' . $this->lastPort . ' WHERE ' . $this->SQL . ' LIMIT 1');
	}

	function isDisplayMissions() {
		return $this->displayMissions;
	}

	function setDisplayMissions($bool) {
		if($this->displayMissions == $bool)
			return;
		$this->displayMissions=$bool;
		$this->hasChanged=true;
	}

	function isDisplayWeapons() {
		return $this->displayWeapons;
	}

	function setDisplayWeapons($bool) {
		if($this->displayWeapons == $bool)
			return;
		$this->displayWeapons=$bool;
		$this->hasChanged=true;
	}

	function isForceDropMessages() {
		return $this->forceDropMessages;
	}

	function setForceDropMessages($bool) {
		if($this->forceDropMessages == $bool)
			return;
		$this->forceDropMessages=$bool;
		$this->hasChanged=true;
	}

	function getLastTurnUpdate() {
		return $this->lastTurnUpdate;
	}

	function setLastTurnUpdate($time) {
		if($this->lastTurnUpdate == $time)
			return;
		$this->lastTurnUpdate=$time;
		$this->hasChanged=true;
//		$sql = $this->db->query('UPDATE player SET last_turn_update = ' . $this->lastTurnUpdate . ' WHERE '. $this->SQL . ' LIMIT 1');
	}

	function getLastShipMod() {
		return $this->lastShipMod;
	}

	function setLastShipMod($time) {
		if($this->lastShipMod == $time)
			return;
		$this->lastShipMod = $time;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET last_ship_mod = ' . $time . ' WHERE ' . $this->SQL . ' LIMIT 1');
	}

	function getPastMaintBought() {
		if(!isset($this->pastMaintBought)) {
			//get past maint
			$this->db->query('DELETE FROM player_repaired WHERE time < ' . $this->db->escapeNumber(TIME - 3600 * 20));
			$this->db->query('SELECT sum(amount) as sum FROM player_repaired WHERE ' . $this->SQL . ' AND amount > 0 LIMIT 1');
			if ($this->db->nextRecord()) {
				$this->pastMaintBought = $this->db->getInt('sum');
			}
			else {
				$this->pastMaintBought = 0;
			}
		}
		return $this->pastMaintBought;
	}

	function getPastMaintCredit() {
		if(!isset($this->pastMaintCredit)) {
			//get past maint
			$this->db->query('DELETE FROM player_repaired WHERE time < ' . $this->db->escapeNumber(TIME - 3600 * 20));
			$this->db->query('SELECT sum(amount) as sum FROM player_repaired WHERE '.$this->SQL.' AND amount < 0 LIMIT 1');
			if ($this->db->nextRecord()) $this->pastMaintCredit = $this->db->getInt('sum');
			else $this->pastMaintCredit = 0;
		}
		return $this->pastMaintCredit;
	}

	function removePastMaint() {
		$this->pastMaint=0;
		$this->db->query('DELETE FROM player_repaired WHERE '.$this->SQL);
	}

	function addPastMaintBought($amount,$type='Normal') {
		$this->getPastMaintBought();
		$this->db->query('REPLACE INTO player_repaired (account_id, game_id, time, amount, source) VALUES (' . $this->db->escapeNumber($this->accountID) . ',' . $this->db->escapeNumber($this->gameID) . ',' . $this->db->escapeNumber(TIME) . ',' . $this->db->escapeNumber($amount) . ',' . $this->db->escapeString($type) . ')');
		$this->giveTurns($amount*10);
		$this->pastMaintBought+=$amount;
	}

	protected function getPureRelationsData() {
		if(!isset($this->pureRelations)) {
			//get relations
			$RACES = Globals::getRaces();
			$this->pureRelations = array();
			foreach ($RACES as $raceID => $raceName) {
				$this->pureRelations[$raceID] = 0;
			}
			$this->db->query('SELECT race_id,relation FROM player_has_relation WHERE ' . $this->SQL . ' LIMIT ' . count($RACES));
			while ($this->db->nextRecord()) {
				$this->pureRelations[$this->db->getInt('race_id')] = $this->db->getInt('relation');
			}
		}
	}

	function increaseRelationsByTrade($numGoods,$raceID) {
		$relations = ceil(min($numGoods,300)/30);
		//Cap relations to a max of 1 after 500 have been reached
		if($this->getPureRelation($raceID) + $relations >= 500)
			$relations = max(1, min($relations, 500 - $this->getPureRelation($raceID)));
		$this->increaseRelations($relations,$raceID);
	}

	function decreaseRelationsByTrade($numGoods,$raceID) {
		$relations = ceil(min($numGoods,300)/30);
		$this->decreaseRelations($relations,$raceID);
	}

	function increaseRelations($relations,$raceID) {
		if($relations < 0)
			throw new Exception('Trying to increase negative relations.');
		if($relations == 0)
			return;
		$relations += $this->getPureRelation($raceID);
		$this->setRelations($relations,$raceID);
	}
	function decreaseRelations($relations,$raceID) {
		if($relations < 0)
			throw new Exception('Trying to decrease negative relations.');
		if($relations == 0)
			return;
		$relations = $this->getPureRelation($raceID) - $relations;
		$this->setRelations($relations,$raceID);
	}
	function setRelations($relations,$raceID) {
		$this->getRelations();
		if($this->pureRelations[$raceID] == $relations)
			return;
		if($relations < MIN_RELATIONS)
			$relations = MIN_RELATIONS;
		$relationsDiff = $relations - $this->pureRelations[$raceID];
		$this->pureRelations[$raceID] = $relations;
		$this->relations[$raceID] += round($relationsDiff);
		$this->db->query('REPLACE INTO player_has_relation (account_id,game_id,race_id,relation) values (' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeNumber($raceID) . ',' . $this->db->escapeNumber($this->pureRelations[$raceID]) . ')');
	}

	public function setPlayerName($playerName) {
		$this->playerName=$playerName;
		$this->setNameChanged(true);
		$this->hasChanged=true;
	}

	public function isNameChanged() {
		return $this->nameChanged;
	}

	public function setNameChanged($bool) {
		$this->nameChanged = $bool;
		$this->hasChanged=true;
	}

	public function hasCustomShipName() {
		return $this->getCustomShipName()!==false;
	}

	public function getCustomShipName() {
		if(!isset($this->customShipName)) {
			$this->db->query('SELECT * FROM ship_has_name WHERE ' . $this->SQL . ' LIMIT 1');
			if ($this->db->nextRecord()) {
				$this->customShipName = $this->db->getField('ship_name');
			}
			else {
				$this->customShipName = false;
			}
		}
		return $this->customShipName;
	}

//	protected function getGoodsInfo() {
//		if(!isset($this->goods) || !isset($this->goodsExpGained)) {
//			//Get Player Goods
//			global $GOODS;
//			$this->goods = array();
//			$this->goodsExpGained = array();
//			foreach ($GOODS as $good_id => $good_name) {
//				$this->goods[$good_id] = 0;
//				$this->goodsExpGained[$good_id] = 0;
//			}
//			$this->db->query('SELECT good_id,amount,exp_gained FROM player_has_goods WHERE ' . $this->SQL);
//			while ($this->db->nextRecord()) {
//				$this->goods[$this->db->getField('good_id')] = $this->db->getField('amount');
//				$this->goodsExpGained[$this->db->getField('good_id')] = $this->db->getField('exp_gained');
//			}
//		}
//	}
//
//	function getGoods() {
//		if(!isset($this->goods)) {
//			$this->getGoodsInfo();
//		}
//		return $this->goods;
//	}
//	function getGood($goodID) {
//		$goods = $this->getGoods();
//		return $goods[$goodID];
//	}
//	function getGoodsExpGained() {
//		if(!isset($this->goodsExpGained)) {
//			$this->getGoodsInfo();
//		}
//		return $this->goodsExpGained;
//	}
//	function getGoodExpGained($goodID) {
//		$goodsExp = $this->getGoodsExpGained();
//		return $goodsExp[$goodID];
//	}

	protected function getGadgetsData() {
		if(!isset($this->gadgets)) {
			//Get Player Gadgets
			$this->db->query('SELECT gadget_id,equipped,cooldown,lasts_until FROM player_has_gadget WHERE ' . $this->SQL);
			$this->gadgets = array();
			while ($this->db->nextRecord())
				$this->gadgets[$this->db->getField('gadget_id')] = array('Equipped' => $this->db->getField('equipped'), 'Cooldown' => $this->db->getField('cooldown'), 'Expires' => $this->db->getField('lasts_until'));
		}
	}

	public function setGadgets($keyOrUpdateArray,$value) {
		$this->getGadgets();

		if(is_array($keyOrUpdateArray)) {
			$set = '';
			foreach($keyOrUpdateArray as $key => $newInfo) {
				$field = $this->gadgetKeyToField($key);
				if($field!==false) {
					foreach($this->gadgets as $gadgetID => $gadget) {
						$this->gadgets[$gadgetID][$key] = $newInfo;
					}
					$set .= '`' . $field . '` = '.$this->db->escapeString($newInfo).',';
				}
			}
			if($set!=='') {
				$set=substr($set,0,-1);
				$this->db->query('UPDATE player_has_gadget SET '.$set.' WHERE '.$this->SQL.' LIMIT '.count($this->gadgets));
			}
		}
		else {
			$field = $this->gadgetKeyToField($keyOrUpdateArray);
			if($field!==false) {
				foreach($this->gadgets as $gadgetID => $gadget) {
					$this->gadgets[$gadgetID][$keyOrUpdateArray] = $value;
				}
				$this->db->query('UPDATE player_has_gadget SET `'.$field.'` = ' . $this->db->escapeString($value) . ' WHERE '.$this->SQL.' LIMIT '.count($this->gadgets));
			}
		}
	}

	function setGadget($gadgetID,$keyOrUpdateArray,$value) {
		if(!is_numeric($gadgetID)) {
			global $GADGETS;
			$gadgetID = $GADGETS[$gadgetID]['ID'];
		}
		$this->getGadgets();
		if(isset($this->gadgets[$gadgetID])) {
			if(is_array($keyOrUpdateArray)) {
				$set = '';
				foreach($keyOrUpdateArray as $key => $newInfo) {
					$field = $this->gadgetKeyToField($key);
					if($field!==false) {
						if($this->gadgets[$gadgetID][$key] != $newInfo) {
							$this->gadgets[$gadgetID][$key]=$newInfo;
							$set .= '`' . $field . '` = '.$this->db->escapeString($newInfo).',';
						}
					}
				}
				if($set!=='') {
					$set=substr($set,0,-1);
					$this->db->query('UPDATE player_has_gadget SET '.$set.' WHERE '.$this->SQL.' AND gadget_id = ' . $this->db->escapeNumber($gadgetID) . ' LIMIT 1');
					return true;
				}
			}
			else {
				$field = $this->gadgetKeyToField($keyOrUpdateArray);
				if($field!==false) {
					if($this->gadgets[$gadgetID][$keyOrUpdateArray] == $value)
						return;
					$this->gadgets[$gadgetID][$keyOrUpdateArray]=$value;
					$this->db->query('UPDATE player_has_gadget SET `' . $field . '` = ' . $this->db->escapeString($value) . ' WHERE '.$this->SQL.' AND gadget_id = ' . $this->db->escapeNumber($gadgetID) . ' LIMIT 1');
					return true;
				}
			}
		}
		return false;
	}

	public function addGadget($gadgetID) {
		if(isset($this->gadgets[$gadgetID]))
			return false;
		$this->gadgets[$gadgetID] = array('Equipped' => 0, 'Cooldown' => 0, 'Expires' => 0);
		$this->db->query('REPLACE INTO player_has_gadget (game_id, account_id, gadget_id, cooldown, equipped,lasts_until) VALUES (' . $this->db->escapeNumber($this->gameID) . ',' . $this->db->escapeNumber($this->accountID) . ',' . $this->db->escapeNumber($gadgetID) . ',0,0,0)');
		return true;
	}

	protected function gadgetKeyToField($key) {
		switch($key) {
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

	function getMining($mineID=false) {
		if(!isset($this->mining)) {
			//Get Player Mining Info
			$this->mining = array();
			$this->db->query('SELECT mine_id, level, type, started, amount, starting_amount
							FROM player_is_mining
							JOIN sector_has_mining USING(game_id, mine_id)
							WHERE sector_id = ' . $this->db->escapeNumber($this->sectorID) . '
								AND ' . $this->SQL . ' LIMIT 1');
			while ($this->db->nextRecord()) {
				$this->mining[$this->db->getInt('mine_id')] = array('Level' => $this->db->getInt('level'),
																	'Started' => $this->db->getInt('started'),
																	'Amount' => $this->db->getInt('amount'),
																	'Type' => $this->db->getField('type'),
																	'Starting Amount' => $this->db->getInt('starting_amount'));
			}
		}
		if($mineID===false)
			return $this->mining;
		if(isset($this->mining[$mineID]))
			return $this->mining[$mineID];
		return false;
	}

	function updateMiningStarted($mineID,$time) {
		$this->getMining();
		$this->mining[$mineID]['Started'] = $time;
		$this->db->query('UPDATE player_is_mining SET started = ' . $this->db->escapeNumber($time) . ' WHERE ' . $this->SQL . ' AND mine_id = ' . $this->db->escapeNumber($mineID) . ' LIMIT 1');
	}

	function deleteMining($mineID=false) {
		$this->getMining();
		if($mineID===false) {
			$this->db->query('DELETE FROM player_is_mining WHERE '.$this->SQL . ' LIMIT ' . count($this->getMining()));
			$this->mining=array();
		}
		else if(isset($this->mining[$mineID])) {

			unset($this->mining[$mineID]);
			$this->db->query('DELETE FROM player_is_mining WHERE '.$this->SQL.' AND mine_id='.$this->db->escapeNumber($mineID).' LIMIT 1');
		}
	}

	function getKnowledge($knowledgeType=false) {
		if(!isset($this->knowledge)) {
			//get players faction knowledge
			$this->db->query('SELECT * FROM player_knows_faction WHERE ' . $this->SQL . ' LIMIT 1');
			if ($this->db->nextRecord()) {
				$this->knowledge['Erebus'] = $this->db->getInt('erebus');
				$this->knowledge['Aether'] = $this->db->getInt('aether');
				$this->knowledge['Tartarus'] = $this->db->getInt('tartarus');
				$this->knowledge['Nyx'] = $this->db->getInt('nyx');
				$this->knowledge['Federation'] = 0;
				$this->knowledge['Underground'] = 0;
			}
			else {
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

	public function killPlayer($sectorID) {
		$sector = SmrSector::getSector($this->getGameID(),$sectorID);
		//msg taken care of in trader_att_proc.php
		// forget plotted course
		$this->deletePlottedCourse();

		$sector->diedHere($this);

		// if we are in an alliance we increase their deaths
		if ($this->hasAlliance())
			$this->db->query('UPDATE alliance SET alliance_deaths = alliance_deaths + 1
							WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()) . ' AND alliance_id = ' . $this->db->escapeNumber($this->getAllianceID()) . ' LIMIT 1');

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

		$this->setSectorID($this->getHome());
		$this->increaseDeaths(1);
		$this->setLandedOnPlanet(false);
		$this->setDead(true);
		$this->setNewbieWarning(true);
		$this->getShip()->getPod($this->getAccount()->isNewbie());

		// Update turns due to ship change
		$new_speed = $this->getShip()->getSpeed();
		$this->setTurns(round($this->turns / $old_speed * $new_speed),100);
	}

	public function getHome() {
		// get his home sector
		$hq_id = GOVERNMENT + $this->getRaceID();
		$this->db->query('SELECT sector_id FROM location JOIN sector USING(game_id, sector_id)
							WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()) . '
							AND location_type_id = ' . $this->db->escapeNumber($hq_id) . ' LIMIT 1');
		if ($this->db->nextRecord())
			return $this->db->getInt('sector_id');
		else
			return 1;
	}

	public function &killPlayerByPlayer(AbstractSmrPlayer $killer) {
		$return = array();
		$msg = $this->getBBLink();

		if ($this->hasCustomShipName()) {
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$msg .= ' flying <span class="yellow">'.$named_ship.'</span>';
		}
		$msg .= ' was destroyed by ' . $killer->getBBLink();
		if ($killer->hasCustomShipName()) {
			$named_ship = strip_tags($killer->getCustomShipName(), '<font><span><img>');
			$msg .= ' flying <span class="yellow">'.$named_ship.'</span>';
		}
		$msg .= ' in Sector&nbsp;' . Globals::getSectorBBLink($this->getSectorID());
		$this->getSector()->increaseBattles(1);
		$this->db->query('INSERT INTO news (game_id,time,news_message,type,killer_id,killer_alliance,dead_id,dead_alliance) VALUES (' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeNumber(TIME) . ',' . $this->db->escapeString($msg,true) . ',\'regular\',' . $this->db->escapeNumber($killer->getAccountID()) . ',' . $this->db->escapeNumber($killer->getAllianceID()) . ',' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ')');

		self::sendMessageFromFedClerk($this->getGameID(), $this->getAccountID(), 'You were <span class="red">DESTROYED</span> by ' . $killer->getBBLink() . ' in sector ' . Globals::getSectorBBLink($this->getSectorID()));
		self::sendMessageFromFedClerk($this->getGameID(), $killer->getAccountID(), 'You <span class="red">DESTROYED</span>&nbsp;' . $this->getBBLink() . ' in sector ' . Globals::getSectorBBLink($this->getSectorID()));

		// Dead player loses between 5% and 25% experience
		$expLossPercentage = 0.15 + 0.10 * ($this->getLevelID() - $killer->getLevelID()) / $this->getMaxLevel();
		$return['DeadExp'] = max(0, floor($this->getExperience() * $expLossPercentage));
		$this->decreaseExperience($return['DeadExp']);

		// Killer gains 50% of the lost exp
		$return['KillerExp'] = max(0, ceil(0.5 * $return['DeadExp']));
		$killer->increaseExperience($return['KillerExp']);

		$return['KillerCredits'] = $this->getCredits();
		$killer->increaseCredits($return['KillerCredits']);

		// The killer may change alignment
		$relations = Globals::getRaceRelations($this->getGameID(), $this->getRaceID());
		$relation = $relations[$killer->getRaceID()];

		$alignChangePerRelation = 0.1;
		if($relation >= RELATIONS_PEACE || $relation <= RELATIONS_WAR)
			$alignChangePerRelation = 0.04;

		$return['KillerAlign'] = -$relation * $alignChangePerRelation; //Lose relations when killing a peaceful race
		if($return['KillerAlign'] > 0) {
			$killer->increaseAlignment($return['KillerAlign']);
		}
		else {
			$killer->decreaseAlignment(-$return['KillerAlign']);
		}
		// War setting gives them military pay
		if($relation <= RELATIONS_WAR) {
			$killer->increaseMilitaryPayment(-floor($relation * 100 * (pow($return['KillerExp']/2,0.25))));
		}

		//check for federal bounty being offered for current port raiders;
		$this->db->query('DELETE FROM player_attacks_port WHERE time < ' . $this->db->escapeNumber(TIME - self::TIME_FOR_FEDERAL_BOUNTY_ON_PR));
		$query = 'SELECT 1
					FROM player_attacks_port
					JOIN port USING(game_id, sector_id)
					JOIN player USING(game_id, account_id)
					WHERE armour > 0 AND ' . $this->SQL . ' LIMIT 1';
		$this->db->query($query);
		if ($this->db->nextRecord()) {
			$bounty = round(DEFEND_PORT_BOUNTY_PER_LEVEL * $this->getLevelID());
			$this->increaseCurrentBountyAmount('HQ', $bounty);
		}

		// Killer get marked as claimer of podded player's bounties even if they don't exist
		$this->setBountiesClaimable($killer);

		// If the alignment difference is greater than 200 then a bounty may be set
		$alignmentDiff = abs($this->getAlignment() - $killer->getAlignment());
		$return['BountyGained'] = array(
			'Type' => 'None',
			'Amount' => 0
		);
		if($alignmentDiff >= 200) {
			// If the podded players alignment makes them deputy or member then set bounty
			if($this->getAlignment() >= 100) {
				$return['BountyGained']['Type'] = 'HQ';
			}
			else if ($this->getAlignment() <= 100) {
				$return['BountyGained']['Type'] = 'UG';
			}

			if($return['BountyGained']['Type'] != 'None') {
				$return['BountyGained']['Amount'] = floor(pow($alignmentDiff, 2.56));
				$killer->increaseCurrentBountyAmount($return['BountyGained']['Type'], $return['BountyGained']['Amount']);
			}
		}

		if($this->isNPC()) {
			$killer->increaseHOF($return['KillerExp'],array('Killing','NPC','Experience','Gained'), HOF_PUBLIC);
			$killer->increaseHOF($this->getExperience(),array('Killing','NPC','Experience','Of Traders Killed'), HOF_PUBLIC);

			$killer->increaseHOF($return['DeadExp'],array('Killing','Experience','Lost By NPCs Killed'), HOF_PUBLIC);

			$killer->increaseHOF($return['KillerCredits'],array('Killing','NPC','Money','Lost By Traders Killed'), HOF_PUBLIC);
			$killer->increaseHOF($return['KillerCredits'],array('Killing','NPC','Money','Gain'), HOF_PUBLIC);
			$killer->increaseHOF($this->getShip()->getCost(),array('Killing','NPC','Money','Cost Of Ships Killed'), HOF_PUBLIC);

			if($return['KillerAlign'] > 0) {
				$killer->increaseHOF($return['KillerAlign'],array('Killing','NPC','Alignment','Gain'), HOF_PUBLIC);
			}
			else {
				$killer->increaseHOF(-$return['KillerAlign'],array('Killing','NPC','Alignment','Loss'), HOF_PUBLIC);
			}

			$killer->increaseHOF($return['BountyGained']['Amount'],array('Killing','NPC','Money','Bounty Gained'), HOF_PUBLIC);

			$killer->increaseHOF(1,array('Killing','NPC Kills'), HOF_PUBLIC);
		}
		else {
			$killer->increaseHOF($return['KillerExp'],array('Killing','Experience','Gained'), HOF_PUBLIC);
			$killer->increaseHOF($this->getExperience(),array('Killing','Experience','Of Traders Killed'), HOF_PUBLIC);

			$killer->increaseHOF($return['DeadExp'],array('Killing','Experience','Lost By Traders Killed'), HOF_PUBLIC);

			$killer->increaseHOF($return['KillerCredits'],array('Killing','Money','Lost By Traders Killed'), HOF_PUBLIC);
			$killer->increaseHOF($return['KillerCredits'],array('Killing','Money','Gain'), HOF_PUBLIC);
			$killer->increaseHOF($this->getShip()->getCost(),array('Killing','Money','Cost Of Ships Killed'), HOF_PUBLIC);

			if($return['KillerAlign'] > 0) {
				$killer->increaseHOF($return['KillerAlign'],array('Killing','Alignment','Gain'), HOF_PUBLIC);
			}
			else {
				$killer->increaseHOF(-$return['KillerAlign'],array('Killing','Alignment','Loss'), HOF_PUBLIC);
			}

			$killer->increaseHOF($return['BountyGained']['Amount'],array('Killing','Money','Bounty Gained'), HOF_PUBLIC);

			if($this->getShip()->getAttackRatingWithMaxCDs() <= MAX_ATTACK_RATING_NEWBIE && $this->getAccount()->isNewbie() && $killer->getAccount()->isVeteran()) { //Newbie kill
				$killer->increaseHOF(1,array('Killing','Newbie Kills'), HOF_PUBLIC);
			}
			else {
				$killer->increaseKills(1);
				$killer->increaseHOF(1,array('Killing','Kills'), HOF_PUBLIC);

				if($killer->hasAlliance()) {
					$this->db->query('UPDATE alliance SET alliance_kills=alliance_kills+1 WHERE alliance_id=' . $this->db->escapeNumber($killer->getAllianceID()) . ' AND game_id=' . $this->db->escapeNumber($killer->getGameID()) . ' LIMIT 1');
				}

				if($this->hasAlliance() && $killer->hasAlliance()) {
					$this->db->query('SELECT kills FROM alliance_vs_alliance WHERE alliance_id_1=' . $this->db->escapeNumber($killer->getAllianceID()) . ' AND alliance_id_2=' . $this->db->escapeNumber($this->getAllianceID()) . ' AND game_id=' . $this->db->escapeNumber($this->getGameID()) . ' LIMIT 1');
					if($this->db->nextRecord()) {
						$this->db->query('UPDATE alliance_vs_alliance SET kills=kills+1 WHERE alliance_id_1=' . $this->db->escapeNumber($killer->getAllianceID()) . ' AND alliance_id_2=' . $this->db->escapeNumber($this->getAllianceID()) . ' AND game_id=' . $this->db->escapeNumber($this->getGameID()) . ' LIMIT 1');
					}
					else {
						$this->db->query('INSERT INTO alliance_vs_alliance VALUES (' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeNumber($killer->getAllianceID()) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ',1)');
					}
				}
			}
		}

		$this->increaseHOF($return['BountyGained']['Amount'],array('Dying','Players','Money','Bounty Gained By Killer'), HOF_PUBLIC);
		$this->increaseHOF($return['KillerExp'],array('Dying','Players','Experience','Gained By Killer'), HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'],array('Dying','Experience','Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'],array('Dying','Players','Experience','Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['KillerCredits'],array('Dying','Players','Money Lost'), HOF_PUBLIC);
		$this->increaseHOF($this->getShip()->getCost(),array('Dying','Players','Money','Cost Of Ships Lost'), HOF_PUBLIC);
		$this->increaseHOF(1,array('Dying','Players','Deaths'), HOF_PUBLIC);

		$this->killPlayer($this->getSectorID());
		return $return;
	}

	public function &killPlayerByForces(SmrForce $forces) {
		$return = array();
		$owner = $forces->getOwner();
		// send a message to the person who died
		self::sendMessageFromFedClerk($this->getGameID(), $owner->getAccountID(), 'Your forces <span class="red">DESTROYED </span>'.$this->getBBLink().' in sector '.Globals::getSectorBBLink($forces->getSectorID()));
		self::sendMessageFromFedClerk($this->getGameID(), $this->getAccountID(), 'You were <span class="red">DESTROYED</span> by '.$owner->getBBLink().'\'s forces in sector '.Globals::getSectorBBLink($this->getSectorID()));

		$news_message = $this->getBBLink();
		if ($this->hasCustomShipName()) {
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$news_message .= ' flying <span class="yellow">'.$named_ship.'</span>';
		}
		$news_message .= ' was destroyed by ' . $owner->getBBLink() . '\'s forces in sector ' . Globals::getSectorBBLink($forces->getSectorID());
		// insert the news entry
		$this->db->query('INSERT INTO news (game_id, time, news_message,killer_id,killer_alliance,dead_id,dead_alliance)
						VALUES(' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber(TIME) . ', ' . $this->db->escapeString($news_message) . ',' . $this->db->escapeNumber($owner->getAccountID()) . ',' . $this->db->escapeNumber($owner->getAllianceID()) . ',' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ')');

		// Player loses 25% experience
		$expLossPercentage = .25;
		$return['DeadExp'] = floor($this->getExperience() * $expLossPercentage);
		$this->decreaseExperience($return['DeadExp']);

		$return['LostCredits'] = $this->getCredits();

		$this->db->query('SELECT * FROM alliance_vs_alliance
						WHERE alliance_id_1 = -1 AND alliance_id_2 = ' . $this->db->escapeNumber($this->getAllianceID()) . ' AND game_id = ' . $this->db->escapeNumber($this->getGameID()));
		if ($this->db->nextRecord()) {
			$this->db->query('UPDATE alliance_vs_alliance SET kills = kills + 1
							WHERE alliance_id_1 = -1
								AND alliance_id_2 = ' . $this->db->escapeNumber($this->getAllianceID()) . '
								AND game_id = ' . $this->db->escapeNumber($this->getGameID()));
		}
		else {
			$this->db->query('REPLACE INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills)
							VALUES (' . $this->db->escapeNumber($this->getGameID()) . ', -1, ' . $this->db->escapeNumber($this->getAllianceID()) . ', 1)');
		}

		$this->increaseHOF($return['DeadExp'], array('Dying','Experience','Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'], array('Dying','Forces','Experience Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['LostCredits'], array('Dying','Forces','Money Lost'), HOF_PUBLIC);
		$this->increaseHOF($this->getShip()->getCost(), array('Dying','Forces','Cost Of Ships Lost'), HOF_PUBLIC);
		$this->increaseHOF(1, array('Dying','Forces','Deaths'), HOF_PUBLIC);

		$this->killPlayer($forces->getSectorID());
		return $return;
	}

	public function &killPlayerByPort(SmrPort $port) {
		$return = array();
		// send a message to the person who died
		self::sendMessageFromFedClerk($this->getGameID(), $this->getAccountID(), 'You were <span class="red">DESTROYED</span> by the defenses of '.$port->getDisplayName());

		$news_message = $this->getBBLink();
		if ($this->hasCustomShipName()) {
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$news_message .= ' flying <span class="yellow">'.$named_ship.'</span>';
		}
		$news_message .= ' was destroyed while invading '.$port->getDisplayName().'.';
		// insert the news entry
		$this->db->query('INSERT INTO news (game_id, time, news_message,killer_id,dead_id,dead_alliance)
						VALUES(' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber(TIME) . ', ' . $this->db->escapeString($news_message) . ',' . $this->db->escapeNumber(ACCOUNT_ID_PORT) . ',' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ')');

		// Player loses between 20% and 28% experience
		$expLossPercentage = .29 - .09 * $port->getLevel()/$port->getMaxLevel();
		$return['DeadExp'] = max(0, floor($this->getExperience() * $expLossPercentage));
		$this->decreaseExperience($return['DeadExp']);

		$return['LostCredits'] = $this->getCredits();

		$this->increaseHOF($return['DeadExp'], array('Dying','Experience','Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'], array('Dying','Ports','Experience Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['LostCredits'], array('Dying','Ports','Money Lost'), HOF_PUBLIC);
		$this->increaseHOF($this->getShip()->getCost(), array('Dying','Ports','Cost Of Ships Lost'), HOF_PUBLIC);
		$this->increaseHOF(1, array('Dying','Ports','Deaths'), HOF_PUBLIC);

		$this->killPlayer($port->getSectorID());
		return $return;
	}

	public function &killPlayerByPlanet(SmrPlanet $planet) {
		$return = array();
		// send a message to the person who died
		$planetOwner = $planet->getOwner();
		self::sendMessageFromFedClerk($this->getGameID(), $planetOwner->getAccountID(), 'Your planet <span class="red">DESTROYED</span>&nbsp;'.$this->getBBLink().' in sector '.Globals::getSectorBBLink($planet->getSectorID()));
		self::sendMessageFromFedClerk($this->getGameID(), $this->getAccountID(), 'You were <span class="red">DESTROYED</span> by the planetary defenses of '.$planet->getDisplayName());

		$news_message = $this->getBBLink();
		if ($this->hasCustomShipName()) {
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$news_message .= ' flying <span class="yellow">'.$named_ship.'</span>';
		}
		$news_message .= ' was destroyed by ' . $planet->getDisplayName() . '\'s planetary defenses in sector ' . Globals::getSectorBBLink($planet->getSectorID()) . '.';
		// insert the news entry
		$this->db->query('INSERT INTO news (game_id, time, news_message,killer_id,killer_alliance,dead_id,dead_alliance)
						VALUES(' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber(TIME) . ', ' . $this->db->escapeString($news_message) . ',' . $this->db->escapeNumber($planetOwner->getAccountID()) . ',' . $this->db->escapeNumber($planetOwner->getAllianceID()) . ',' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ')');

		// Player loses between 20% and 27% experience
		$expLossPercentage = .27 - .07 * $planet->getLevel()/$planet->getMaxLevel();
		$return['DeadExp'] = max(0, floor($this->getExperience() * $expLossPercentage));
		$this->decreaseExperience($return['DeadExp']);

		$return['LostCredits'] = $this->getCredits();

		$this->increaseHOF($return['DeadExp'], array('Dying','Experience','Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'], array('Dying','Planets','Experience Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['LostCredits'], array('Dying','Planets','Money Lost'), HOF_PUBLIC);
		$this->increaseHOF($this->getShip()->getCost(), array('Dying','Planets','Cost Of Ships Lost'), HOF_PUBLIC);
		$this->increaseHOF(1, array('Dying','Planets','Deaths'), HOF_PUBLIC);

		$this->killPlayer($planet->getSectorID());
		return $return;
	}

	public function save() {
		if($this->hasChanged === true) {
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
				', attack_warning='.$this->db->escapeString($this->attackColour).
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
		foreach($this->hasBountyChanged as $key => &$bountyChanged) {
			if($bountyChanged===true) {
				$bountyChanged = false;
				$bounty = $this->getBounty($key);
				if($bounty['New'] === true) {
					if($bounty['Amount'] > 0 || $bounty['SmrCredits'] > 0)
						$this->db->query('INSERT INTO bounty (account_id,game_id,type,amount,smr_credits,claimer_id,time) VALUES (' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeString($bounty['Type']) . ',' . $this->db->escapeNumber($bounty['Amount']) . ',' . $this->db->escapeNumber($bounty['SmrCredits']) . ',' . $this->db->escapeNumber($bounty['Claimer']) . ',' . $this->db->escapeNumber($bounty['Time']) .')');
				}
				else {
					if($bounty['Amount'] > 0 || $bounty['SmrCredits'] > 0)
						$this->db->query('UPDATE bounty
							SET amount=' . $this->db->escapeNumber($bounty['Amount']) . ',
							smr_credits=' . $this->db->escapeString($bounty['SmrCredits']) . ',
							type=' . $this->db->escapeString($bounty['Type']) . ',
							claimer_id=' . $this->db->escapeNumber($bounty['Claimer']) . ',
							time=' . $this->db->escapeNumber($bounty['Time']) . '
							WHERE bounty_id=' . $this->db->escapeNumber($bounty['ID']) . ' AND ' . $this->SQL . ' LIMIT 1');
					else
						$this->db->query('DELETE FROM bounty WHERE bounty_id=' . $this->db->escapeNumber($bounty['ID']) . ' AND ' . $this->SQL . ' LIMIT 1');
				}
			}
		}
		$this->saveHOF();
	}

	public function saveHOF() {
		if($this->hasHOFChanged!==false)
			$this->doHOFSave($this->hasHOFChanged);
		if(!empty(self::$hasHOFVisChanged)) {
			foreach(self::$hasHOFVisChanged as $hofType => $changeType) {
				if($changeType==self::HOF_NEW)
					$this->db->query('INSERT INTO hof_visibility (type, visibility) VALUES ('.$this->db->escapeString($hofType).','.$this->db->escapeString(self::$HOFVis[$hofType]).')');
				else
					$this->db->query('UPDATE hof_visibility SET visibility = '.$this->db->escapeString(self::$HOFVis[$hofType]).' WHERE type = '.$this->db->escapeString($hofType).' LIMIT 1');
				unset(self::$hasHOFVisChanged[$hofType]);
			}
		}
	}
	protected function doHOFSave(array &$hasChangedList,array $typeList = array()) {
		foreach($hasChangedList as $type => &$hofChanged) {
			$tempTypeList = $typeList;
			$tempTypeList[] = $type;
			if(is_array($hofChanged)) {
				$this->doHOFSave($hofChanged,$tempTypeList);
			}
			else {
				$amount = $this->getHOF($tempTypeList);
				if($hofChanged==self::HOF_NEW) {
					if($amount > 0)
						$this->db->query('INSERT INTO player_hof (account_id,game_id,type,amount) VALUES (' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeArray($tempTypeList,false,true,':',false) . ',' . $this->db->escapeNumber($amount) . ')');
				}
				else if($hofChanged==self::HOF_CHANGED) {
	//				if($amount > 0)
						$this->db->query('UPDATE player_hof
							SET amount=' . $this->db->escapeNumber($amount) . '
							WHERE ' . $this->SQL . ' AND type = ' . $this->db->escapeArray($tempTypeList,false,true,':',false).' LIMIT 1');
	//				else
	//					$this->db->query('DELETE FROM player_hof WHERE account_id=' . $this->getAccountID() . ' AND game_id = ' . $this->getGameID() . ' AND type = ' . $this->db->escapeArray($tempTypeList,false,true,':',false) . ' LIMIT 1');
	//				}
				}
				$hofChanged = false;
			}
		}
	}

	protected function getHOFData() {
		if(!isset($this->HOF)) {
			//Get Player HOF
			$this->db->query('SELECT type,amount FROM player_hof WHERE ' . $this->SQL);
			$this->HOF = array();
			while($this->db->nextRecord()) {
				$hof =& $this->HOF;
				$typeList = explode(':',$this->db->getField('type'));
				foreach($typeList as $type) {
					if(!isset($hof[$type])) {
						$hof[$type] = array();
					}
					$hof =& $hof[$type];
				}
				$hof = $this->db->getField('amount');
			}
			self::getHOFVis();
		}
	}

	public static function getHOFVis() {
		if(!isset(self::$HOFVis)) {
			//Get Player HOF Vis
			$db = new SmrMySqlDatabase();
			$db->query('SELECT type,visibility FROM hof_visibility');
			self::$HOFVis = array();
			while($db->nextRecord()) {
				self::$HOFVis[$db->getField('type')] = $db->getField('visibility');
			}
		}
	}

	protected function getBountiesData() {
		if(!isset($this->bounties)) {
			$this->bounties = array();
			$this->db->query('SELECT * FROM bounty WHERE ' . $this->SQL);
			while($this->db->nextRecord()) {
				$this->bounties[$this->db->getField('bounty_id')] = array(
							'Amount' => $this->db->getInt('amount'),
							'SmrCredits' => $this->db->getInt('smr_credits'),
							'Type' => $this->db->getField('type'),
							'Claimer' => $this->db->getInt('claimer_id'),
							'Time' => $this->db->getInt('time'),
							'ID' => $this->db->getInt('bounty_id'),
							'New' => false);
			}
		}
	}

	// Get bounties that can be claimed by this player
	// Type must be 'HQ' or 'UG'
	public function getClaimableBounties($type) {
		$bounties = array();
		$this->db->query('SELECT * FROM bounty WHERE claimer_id=' . $this->db->escapeNumber($this->getAccountID()) . ' AND game_id=' . $this->db->escapeNumber($this->getGameID()) . ' AND type=' . $this->db->escapeString($type));
		while ($this->db->nextRecord()) {
			$bounties[] = array(
				'player' => SmrPlayer::getPlayer($this->db->getInt('account_id'), $this->getGameID()),
				'bounty_id' => $this->db->getInt('bounty_id'),
				'credits' => $this->db->getInt('amount'),
				'smr_credits' => $this->db->getInt('smr_credits'),
			);
		}
		return $bounties;
	}

	public function isPresident() {
		$president = Council::getPresident($this->getGameID(),$this->getRaceID());
		return is_object($president) && $this->equals($president);
	}

	public function isOnCouncil() {
		return Council::isOnCouncil($this->getGameID(),$this->getRaceID(),$this->getAccountID());
	}

	public function setNewbieWarning($bool) {
		if($this->newbieWarning==$bool) {
			return;
		}
		$this->newbieWarning=$bool;
		$this->hasChanged=true;
	}

	public function getNewbieWarning() {
		return $this->newbieWarning;
	}

	public function getTickers() {
		if(!isset($this->tickers)) {
			$this->tickers = array();
			//get ticker info
			$this->db->query('SELECT type,time,expires,recent FROM player_has_ticker WHERE ' . $this->SQL .' AND expires > ' . $this->db->escapeNumber(TIME));
			while($this->db->nextRecord())
				$this->tickers[$this->db->getField('type')] = array( 'Type' => $this->db->getField('type'),
																				'Time' => $this->db->getInt('time'),
																				'Expires' => $this->db->getInt('expires'),
																				'Recent' => $this->db->getField('recent'));
		}
		return $this->tickers;
	}

	public function hasTickers() {
		return count($this->getTickers()) > 0;
	}

	public function getTicker($tickerType) {
		$tickers = $this->getTickers();
		if(isset($tickers[$tickerType]))
			return $tickers[$tickerType];
		return false;
	}

	public function hasTicker($tickerType) {
		return $this->getTicker($tickerType) !== false;
	}

	public function getTurnsLevel() {
		if (!$this->hasTurns()) return 'NONE';
		if ($this->getTurns() <= 25 ) return 'LOW';
		if ($this->getTurns() <= 75 ) return 'MEDIUM';
		return 'HIGH';
	}

	public function update() {
		$this->save();
	}

	protected static function doMessageSending($senderID,$receiverID, $gameID, $messageTypeID, $message, $expires, $senderDelete = false, $unread = true) {
		$message = trim($message);
		$db = new SmrMySqlDatabase();
		// send him the message
		$db->query('INSERT INTO message
			(account_id,game_id,message_type_id,message_text,
			sender_id,send_time,expire_time,sender_delete) VALUES(' .
			$db->escapeNumber($receiverID) . ',' .
			$db->escapeNumber($gameID) . ',' .
			$db->escapeNumber($messageTypeID) . ',' .
			$db->escapeString($message) . ',' .
			$db->escapeNumber($senderID) . ',' .
			$db->escapeNumber(TIME) . ',' .
			$db->escapeNumber($expires) . ',' .
			$db->escapeBoolean($senderDelete) . ')'
		);

		if($unread === true) {
			// give him the message icon
			$db->query('REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES
						(' . $db->escapeNumber($gameID) . ', ' . $db->escapeNumber($receiverID) . ', ' . $db->escapeNumber($messageTypeID) . ')');
		}

		switch ($messageTypeID) {
			case MSG_PLAYER:
				$receiverAccount = SmrAccount::getAccount($receiverID);
				if($receiverAccount->isValidated() && $receiverAccount->isReceivingMessageNotifications($messageTypeID) && !$receiverAccount->isLoggedIn()) {
					require_once(get_file_loc('message.functions.inc'));
					$sender = getMessagePlayer($senderID, $gameID, $messageTypeID);
					if ($sender instanceof SmrPlayer) {
						$sender = $sender->getDisplayName();
					}
					$mail = setupMailer();
					$mail->Subject = 'Message Notification';
					$mail->setFrom('notifications@smrealms.de', 'SMR Notifications');
					$bbifiedMessage = 'From: ' . $sender . ' Date: ' . date($receiverAccount->getShortDateFormat().' '.$receiverAccount->getShortTimeFormat(),TIME) . "<br/>\r\n<br/>\r\n" . bbifyMessage($message,true);
					$mail->msgHTML($bbifiedMessage);
					$mail->AltBody = strip_tags($bbifiedMessage);
					$mail->addAddress($receiverAccount->getEmail(), $receiverAccount->getHofName());
					$mail->send();
					$receiverAccount->decreaseMessageNotifications($messageTypeID,1);
				}
			break;
		}
	}

	public function sendMessageToBox($boxTypeID, $message) {
		// send him the message
		SmrAccount::doMessageSendingToBox($this->getAccountID(), $boxTypeID, $message, $this->getGameID());
	}

	public function sendGlobalMessage($message, $canBeIgnored = true) {
		if($canBeIgnored) {
			if($this->getAccount()->isMailBanned())
				create_error('You are currently banned from sending messages');
		}
		$this->sendMessageToBox(BOX_GLOBALS,$message);

		// send to all online player
		$db = new SmrMySqlDatabase();
		$db->query('SELECT account_id
					FROM active_session
					JOIN player USING (game_id, account_id)
					WHERE active_session.last_accessed >= ' . $db->escapeNumber(TIME - SmrSession::TIME_BEFORE_EXPIRY) . '
						AND game_id = ' . $db->escapeNumber($this->getGameID()) . '
						AND ignore_globals = \'FALSE\'
						AND account_id != ' . $db->escapeNumber($this->getAccountID()));

		while ($db->nextRecord()) {
			$this->sendMessage($db->getInt('account_id'), MSG_GLOBAL, $message, $canBeIgnored);
		}
		$this->sendMessage($this->getAccountID(), MSG_GLOBAL, $message, $canBeIgnored, false);
	}

	public function sendMessage($receiverID, $messageTypeID, $message, $canBeIgnored = true, $unread = true) {
		//get expire time
		if($canBeIgnored) {
			if($this->getAccount()->isMailBanned())
				create_error('You are currently banned from sending messages');
			// Don't send messages to players ignoring us
			$this->db->query('SELECT account_id FROM message_blacklist WHERE account_id=' . $this->db->escapeNumber($receiverID) . ' AND blacklisted_id=' . $this->db->escapeNumber($this->getAccountID()) . ' LIMIT 1');
			if($this->db->nextRecord())
				return;
		}

		$message = word_filter($message);

		switch ($messageTypeID) {
			case MSG_GLOBAL: //We don't send globals to the box here or it gets done loads of times.
				$expires = 3600; // 1h
			break;
			case MSG_PLAYER:
				$expires = 86400 * 31;
			break;
			case MSG_PLANET:
				$expires = 86400 * 7;
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
			case MSG_CASINO:
				$expires = 86400 * 31;
			break;
			default:
				$expires = 86400 * 7;
		}
		$expires += TIME;
		// send him the message
		self::doMessageSending($this->getAccountID(),$receiverID, $this->getGameID(), $messageTypeID, $message, $expires, $messageTypeID == MSG_SCOUT, $unread);
	}

	public function sendMessageFromOpAnnounce($receiverID, $message, $expires=false) {
		// get expire time if not set
		if ($expires === false) {
			$expires = TIME + 86400 * 14;
		}
		self::doMessageSending(ACCOUNT_ID_OP_ANNOUNCE, $receiverID, $this->getGameID(), MSG_ALLIANCE, $message, $expires);
	}

	public function sendMessageFromAllianceCommand($receiverID, $message) {
		$expires = TIME + 86400 * 365;
		self::doMessageSending(ACCOUNT_ID_ALLIANCE_COMMAND, $receiverID, $this->getGameID(), MSG_PLAYER, $message, $expires);
	}

	public static function sendMessageFromPlanet($gameID, $receiverID, $message) {
		//get expire time
		$expires = TIME + 86400 * 31;
		// send him the message
		self::doMessageSending(ACCOUNT_ID_PLANET,$receiverID, $gameID, MSG_PLANET, $message, $expires);
	}

	public static function sendMessageFromPort($gameID, $receiverID, $message) {
		//get expire time
		$expires = TIME + 86400 * 31;
		// send him the message
		self::doMessageSending(ACCOUNT_ID_PORT,$receiverID, $gameID, MSG_PLAYER, $message, $expires);
	}

	public static function sendMessageFromFedClerk($gameID, $receiverID, $message) {
		$expires = TIME + 86400 * 365;
		self::doMessageSending(ACCOUNT_ID_FED_CLERK, $receiverID, $gameID, MSG_PLAYER, $message, $expires);
	}

	public static function sendMessageFromAdmin($gameID, $receiverID, $message, $expires = false) {
		//get expire time
		if($expires===false)
			$expires = TIME + 86400 * 365;
		// send him the message
		self::doMessageSending(ACCOUNT_ID_ADMIN,$receiverID, $gameID, MSG_ADMIN, $message, $expires);
	}

	public static function sendMessageFromAllianceAmbassador($gameID, $receiverID, $message, $expires = false) {
		//get expire time
		if($expires===false)
			$expires = TIME + 86400 * 31;
		// send him the message
		self::doMessageSending(ACCOUNT_ID_ALLIANCE_AMBASSADOR, $receiverID, $gameID, MSG_ALLIANCE, $message, $expires);
	}

	public static function sendMessageFromCasino($gameID, $receiverID, $message, $expires = false) {
		//get expire time
		if($expires===false)
			$expires = TIME + 86400 * 7;
		// send him the message
		self::doMessageSending(ACCOUNT_ID_CASINO, $receiverID, $gameID, MSG_CASINO, $message, $expires);
	}

	public static function sendMessageFromRace($raceID, $gameID, $receiverID, $message, $expires = false) {
		//get expire time
		if($expires===false)
			$expires = TIME + 86400 * 5;
		// send him the message
		self::doMessageSending(ACCOUNT_ID_GROUP_RACES+$raceID,$receiverID, $gameID, MSG_POLITICAL, $message, $expires);
	}

	public function setMessagesRead($messageTypeID) {
		$this->db->query('DELETE FROM player_has_unread_messages
							WHERE '.$this->SQL.' AND message_type_id = ' . $this->db->escapeNumber($messageTypeID));
	}

	public function &getPlottedCourse() {
		if(!isset($this->plottedCourse)) {
			require_once(get_file_loc('Plotter.class.inc'));
			// check if we have a course plotted
			$this->db->query('SELECT course FROM player_plotted_course WHERE ' . $this->SQL . ' LIMIT 1');

			if ($this->db->nextRecord()) {
				// get the course back
				$this->plottedCourse = unserialize($this->db->getField('course'));
				$endSectorID = $this->plottedCourse->getEndSectorID();
				if ($endSectorID != $this->getSectorID() && $this->isPartOfCourse($this->getSector())) {
					// Current sector is in the path, we have jumped along it somehow
					$this->setPlottedCourse($this->plottedCourse->skipToSector($this->getSectorID()));
				}
			}
			else
				$this->plottedCourse = false;
		}
		return $this->plottedCourse;
	}

	public function setPlottedCourse(Distance &$plottedCourse) {
		$hadPlottedCourse = $this->hasPlottedCourse();
		$this->plottedCourse =& $plottedCourse;
		if ($this->plottedCourse->getTotalSectors()>0)
			$this->db->query('REPLACE INTO player_plotted_course
				(account_id, game_id, course)
				VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeBinary(serialize($this->plottedCourse)) . ')');
		else if($hadPlottedCourse) {
			$this->deletePlottedCourse();
		}
	}

	public function hasPlottedCourse() {
		return $this->getPlottedCourse()!==false;
	}

	public function isPartOfCourse($sectorOrSectorID) {
		if(!$this->hasPlottedCourse())
			return false;
		if($sectorOrSectorID instanceof SmrSector )
			$sectorID = $sectorOrSectorID->getSectorID();
		else
			$sectorID = $sectorOrSectorID;
		return $this->getPlottedCourse()->isInPath($sectorID);
	}

	public function deletePlottedCourse() {
		$this->plottedCourse = false;
		$this->db->query('DELETE FROM player_plotted_course WHERE '.$this->SQL.' LIMIT 1');
	}

	// Computes the turn cost and max misjump between current and target sector
	public function getJumpInfo(SmrSector $targetSector) {
		require_once(get_file_loc('Plotter.class.inc'));
		$path = Plotter::findDistanceToX($targetSector, $this->getSector(), true);
		if ($path===false) {
			create_error('Unable to plot from '.$this->getSectorID().' to '.$targetSector->getSectorID().'.');
		}
		$distance = $path->getRelativeDistance();

		$turnCost = max(TURNS_JUMP_MINIMUM, round($distance * TURNS_PER_JUMP_DISTANCE));
		$maxMisjump = max(0, round(($distance - $turnCost) * MISJUMP_DISTANCE_DIFF_FACTOR / (1 + $this->getLevelID() * MISJUMP_LEVEL_FACTOR)));
		return array('turn_cost' => $turnCost, 'max_misjump' => $maxMisjump);
	}

	public function __sleep() {
		return array('accountID', 'gameID', 'sectorID', 'alignment', 'playerID', 'playerName');
	}

	public function &getStoredDestinations() {
		if(!isset($this->storedDestinations)) {
			$storedDestinations = array();
			$this->db->query('SELECT * FROM player_stored_sector WHERE ' . $this->SQL);
			while($this->db->nextRecord()) {
				$storedDestinations[] = array(
					'Label' => $this->db->getField('label'),
					'SectorID' => $this->db->getInt('sector_id'),
					'OffsetTop' => $this->db->getInt('offset_top'),
					'OffsetLeft' => $this->db->getInt('offset_left')
				);
			}
			$this->storedDestinations =& $storedDestinations;
		}
		return $this->storedDestinations;
	}


	function moveDestinationButton($sectorID, $offsetTop, $offsetLeft) {

		if( !is_numeric($offsetLeft) || !is_numeric($offsetTop)) {
			create_error('The position of the saved sector must be numeric!.');
		}
		$offsetTop = round($offsetTop);
		$offsetLeft = round($offsetLeft);

		if($offsetLeft < 0 || $offsetLeft > 500 || $offsetTop < 0 || $offsetTop > 300) {
			create_error('The saved sector must be in the box!');
		}

		$storedDestinations =& $this->getStoredDestinations();
		foreach($storedDestinations as &$sd) {
			if($sd['SectorID'] == $sectorID) {
				$sd['OffsetTop'] = $offsetTop;
				$sd['OffsetLeft'] = $offsetLeft;
				$this->db->query('
					UPDATE player_stored_sector
						SET offset_left = ' . $this->db->escapeNumber($offsetLeft) . ', offset_top=' . $this->db->escapeNumber($offsetTop) . '
					WHERE ' . $this->SQL . ' AND sector_id = ' . $this->db->escapeNumber($sectorID)
				);
				return true;
			}
		}

		create_error('You do not have a saved sector for #' . $sectorID);
	}

	function addDestinationButton($sectorID, $label) {

		if (!is_numeric($sectorID) || !SmrSector::sectorExists($this->getGameID(), $sectorID)) {
			create_error('You want to add a non-existent sector?');
		}

		// sector already stored ?
		foreach($this->getStoredDestinations() as $sd) {
			if($sd['SectorID'] == $sectorID) {
				create_error('Sector already stored!');
			}
		}

		$this->storedDestinations[] = array(
			'Label' => $label,
			'SectorID' => (int)$sectorID,
			'OffsetTop' => 1,
			'OffsetLeft' => 1
		);

		$this->db->query('
			INSERT INTO player_stored_sector (account_id, game_id, sector_id, label, offset_top, offset_left)
			VALUES (' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($sectorID) . ',' . $this->db->escapeString($label, true) . ',1,1)'
		);
	}

	function deleteDestinationButton($sectorID) {
		if (!is_numeric($sectorID) || $sectorID < 1) {
			create_error('You want to remove a non-existent sector?');
		}

		foreach($this->getStoredDestinations() as $key => $sd) {
			if($sd['SectorID'] == $sectorID) {
				$this->db->query('
					DELETE FROM player_stored_sector
					WHERE ' . $this->SQL . '
					AND sector_id = ' . $this->db->escapeNumber($sectorID)
				);
				unset($this->storedDestinations[$key]);
				return true;
			}
		}
		return false;
	}
}
