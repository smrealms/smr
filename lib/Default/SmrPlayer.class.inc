<?php

// Exception thrown when a player cannot be found in the database
class PlayerNotFoundException extends Exception {}

class SmrPlayer extends AbstractSmrPlayer {
	const TIME_FOR_FEDERAL_BOUNTY_ON_PR = 10800;
	const TIME_FOR_ALLIANCE_SWITCH = 0;
	protected static $CACHE_SECTOR_PLAYERS = array();
	protected static $CACHE_PLANET_PLAYERS = array();
	protected static $CACHE_ALLIANCE_PLAYERS = array();
	protected static $CACHE_PLAYERS = array();

	protected $db;
	protected $SQL;

	protected $newbieWarning;
	protected $tickers;
	protected $lastTurnUpdate;
	protected $lastNewsUpdate;
	protected $attackColour;
//	protected $pastKnowledge;
	protected $allianceJoinable;
	protected $lastPort;
	protected $bank;
	protected $zoom;
	protected $displayMissions;
	protected $displayWeapons;
	protected $ignoreGlobals;
	protected $plottedCourse;
	protected $plottedCourseFrom;
	protected $nameChanged;
	protected $combatDronesKamikazeOnMines;
	protected $customShipName;


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
				$players[$accountID] = self::getPlayer($accountID, $gameID, $forceUpdate, $db);
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
				$players[$accountID] = self::getPlayer($accountID, $gameID, $forceUpdate, $db);
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
				$players[$accountID] = self::getPlayer($accountID, $gameID, $forceUpdate, $db);
			}
			self::$CACHE_ALLIANCE_PLAYERS[$gameID][$allianceID] = $players;
		}
		return self::$CACHE_ALLIANCE_PLAYERS[$gameID][$allianceID];
	}

	public static function &getPlayer($accountID, $gameID, $forceUpdate=false, $db=null) {
		if ($forceUpdate || !isset(self::$CACHE_PLAYERS[$gameID][$accountID])) {
			self::$CACHE_PLAYERS[$gameID][$accountID] = new SmrPlayer($gameID, $accountID, $db);
		}
		return self::$CACHE_PLAYERS[$gameID][$accountID];
	}

	public static function &getPlayerByPlayerID($playerID,$gameID,$forceUpdate = false) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT * FROM player WHERE game_id = '.$db->escapeNumber($gameID).' AND player_id = '.$db->escapeNumber($playerID).' LIMIT 1');
		if ($db->nextRecord()) {
			return self::getPlayer($db->getInt('account_id'), $gameID, $forceUpdate, $db);
		}
		throw new PlayerNotFoundException('Player ID not found.');
	}

	protected function __construct($gameID, $accountID, $db=null) {
		parent::__construct();
		$this->db = new SmrMySqlDatabase();
		$this->SQL = 'account_id = ' . $this->db->escapeNumber($accountID) . ' AND game_id = ' . $this->db->escapeNumber($gameID);

		if (isset($db)) {
			$playerExists = true;
		} else {
			$db = $this->db;
			$this->db->query('SELECT * FROM player WHERE ' . $this->SQL . ' LIMIT 1');
			$playerExists = $db->nextRecord();
		}

		if ($playerExists) {
			$this->accountID = (int) $accountID;
			$this->gameID = (int) $gameID;
			$this->playerName = $db->getField('player_name');
			$this->playerID = $db->getInt('player_id');
			$this->sectorID = $db->getInt('sector_id');
			$this->lastSectorID = $db->getInt('last_sector_id');
			$this->turns = $db->getInt('turns');
			$this->lastTurnUpdate = $db->getInt('last_turn_update');
			$this->newbieTurns = $db->getInt('newbie_turns');
			$this->lastNewsUpdate = $db->getInt('last_news_update');
			$this->attackColour = $db->getField('attack_warning');
			$this->dead = $db->getBoolean('dead');
			$this->npc = $db->getBoolean('npc');
			$this->newbieStatus = $db->getBoolean('newbie_status');
			$this->landedOnPlanet = $db->getBoolean('land_on_planet');
			$this->lastActive = $db->getInt('last_active');
			$this->lastCPLAction = $db->getInt('last_cpl_action');
			$this->raceID = $db->getInt('race_id');
			$this->credits = $db->getInt('credits');
			$this->experience = $db->getInt('experience');
			$this->alignment = $db->getInt('alignment');
			$this->militaryPayment = $db->getInt('military_payment');
			$this->allianceID = $db->getInt('alliance_id');
			$this->allianceJoinable = $db->getInt('alliance_join');
			$this->shipID = $db->getInt('ship_type_id');
			$this->kills = $db->getInt('kills');
			$this->deaths = $db->getInt('deaths');
			$this->assists = $db->getInt('assists');
			$this->lastPort = $db->getInt('last_port');
			$this->bank = $db->getInt('bank');
			$this->zoom = $db->getInt('zoom');
			$this->displayMissions = $db->getBoolean('display_missions');
			$this->displayWeapons = $db->getBoolean('display_weapons');
			$this->forceDropMessages = $db->getBoolean('force_drop_messages');
			$this->groupScoutMessages = $db->getField('group_scout_messages');
			$this->ignoreGlobals = $db->getBoolean('ignore_globals');
			$this->newbieWarning = $db->getBoolean('newbie_warning');
			$this->nameChanged = $db->getBoolean('name_changed');
			$this->combatDronesKamikazeOnMines = $db->getBoolean('combat_drones_kamikaze_on_mines');
		}
		else {
			throw new PlayerNotFoundException('Invalid accountID: '.$accountID. ' OR gameID:'.$gameID);
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
			case 'Default Zoom':
				return $this->zoom;
			case 'Bank':
				return $this->bank;
			case 'Turns':
				return $this->turns;
			case 'Last Turn Update':
				return $this->lastTurnUpdate;
			case 'Last Active':
				return $this->lastActive;
			case 'Last CPL Action':
				return $this->lastCPLAction;
			case 'Attack Colour':
				return $this->attackWarning;
			case 'Display Missions':
				return $this->displayMissions;
			case 'Ignore Global':
				return $this->ignoreGlobals;
			case 'Level ID':
				return $this->getLevelID();
			case 'Display Name':
				return $this->getDisplayName();

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
		return SmrAccount::getAccount($this->getAccountID());
	}

	function getZoom() {
		return $this->zoom;
	}

	function setZoom($zoom) {
		// Set the zoom level between [1, 9]
		$zoom = max(1, min(9, $zoom));
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

	public function setSectorID($sectorID) {
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

		if (!$this->isAllianceLeader()) {
			// Do not throw an exception if the NHL account doesn't exist.
			try {
				$this->sendMessage($alliance->getLeaderID(), MSG_PLAYER, 'I joined your alliance!', false);
			} catch (AccountNotFoundException $e) {
				if ($alliance->getLeaderID() != ACCOUNT_ID_NHL) throw $e;
			}
		}

		$this->db->query('INSERT INTO player_has_alliance_role (game_id, account_id, role_id, alliance_id) VALUES (' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($this->getAccountID()) . ', 2,' . $this->db->escapeNumber($alliance->getAllianceID()) . ')');
		$this->actionTaken('JoinAlliance', array('Alliance' => $alliance));

		// Joining an alliance cancels all open invitations
		$this->db->query('DELETE FROM alliance_invites_player WHERE ' . $this->SQL);
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

//	function getPastKnowledge() {
//		return $this->pastKnowledge;
//	}

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

	/**
	 * Grant the player their starting turns.
	 */
	public function giveStartingTurns() {
		$startTurns = $this->getShip()->getRealSpeed() * $this->getGame()->getStartTurnHours();
		$this->giveTurns($startTurns);
		$this->setLastTurnUpdate($this->getGame()->getStartTime());
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
			// recalc the time to avoid rounding errors
			$newLastTurnUpdate = $this->getLastTurnUpdate() + ceil($extraTurns * 3600 / $this->getShip()->getRealSpeed());
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

	/**
	 * Should weapons be displayed in the right panel?
	 * This updates the player database directly because it is used with AJAX,
	 * which does not acquire a sector lock.
	 */
	function setDisplayWeapons($bool) {
		if($this->displayWeapons == $bool)
			return;
		$this->displayWeapons=$bool;
		$this->db->query('UPDATE player SET display_weapons=' . $this->db->escapeBoolean($this->displayWeapons) . ' WHERE ' . $this->SQL);
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

	function getScoutMessageGroupLimit() {
		if ($this->groupScoutMessages == 'ALWAYS') {
			return 0;
		} elseif ($this->groupScoutMessages == 'AUTO') {
			return MESSAGES_PER_PAGE;
		} elseif ($this->groupScoutMessages == 'NEVER') {
			return PHP_INT_MAX;
		}
	}

	function getGroupScoutMessages() {
		return $this->groupScoutMessages;
	}

	function setGroupScoutMessages($setting) {
		if ($this->groupScoutMessages == $setting) {
			return;
		}
		$this->groupScoutMessages = $setting;
		$this->hasChanged = true;
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

	/**
	 * Use this method when the player is changing their own name.
	 * This will flag the player as having used their free name change.
	 */
	public function setPlayerNameByPlayer($playerName) {
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

		$this->setSectorID($this::getHome($this->getGameID(), $this->getRaceID()));
		$this->increaseDeaths(1);
		$this->setLandedOnPlanet(false);
		$this->setDead(true);
		$this->setNewbieWarning(true);
		$this->getShip()->getPod($this->hasNewbieStatus());

		// Update turns due to ship change
		$new_speed = $this->getShip()->getSpeed();
		$this->setTurns(round($this->turns / $old_speed * $new_speed));
		$this->setNewbieTurns(100);
	}

	static public function getHome($gameID, $raceID) {
		// get his home sector
		$hq_id = GOVERNMENT + $raceID;
		$raceHqSectors = SmrSector::getLocationSectors($gameID, $hq_id);
		if (!empty($raceHqSectors)) {
			// If race has multiple HQ's for some reason, use the first one
			return key($raceHqSectors);
		} else {
			return 1;
		}
	}

	public function incrementAllianceVsKills($otherID) {
		$values = [$this->getGameID(), $this->getAllianceID(), $otherID, 1];
		$this->db->query('INSERT INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) VALUES (' . $this->db->escapeArray($values) . ') ON DUPLICATE KEY UPDATE kills = kills + 1');
	}

	public function incrementAllianceVsDeaths($otherID) {
		$values = [$this->getGameID(), $otherID, $this->getAllianceID(), 1];
		$this->db->query('INSERT INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) VALUES (' . $this->db->escapeArray($values) . ') ON DUPLICATE KEY UPDATE kills = kills + 1');
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

			if($this->getShip()->getAttackRatingWithMaxCDs() <= MAX_ATTACK_RATING_NEWBIE && $this->hasNewbieStatus() && !$killer->hasNewbieStatus()) { //Newbie kill
				$killer->increaseHOF(1,array('Killing','Newbie Kills'), HOF_PUBLIC);
			}
			else {
				$killer->increaseKills(1);
				$killer->increaseHOF(1,array('Killing','Kills'), HOF_PUBLIC);

				if($killer->hasAlliance()) {
					$this->db->query('UPDATE alliance SET alliance_kills=alliance_kills+1 WHERE alliance_id=' . $this->db->escapeNumber($killer->getAllianceID()) . ' AND game_id=' . $this->db->escapeNumber($killer->getGameID()) . ' LIMIT 1');
				}

				// alliance vs. alliance stats
				$this->incrementAllianceVsDeaths($killer->getAllianceID());
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

		// alliance vs. alliance stats
		$this->incrementAllianceVsDeaths(ALLIANCE_VS_FORCES);
		$owner->incrementAllianceVsKills(ALLIANCE_VS_FORCES);

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

		// alliance vs. alliance stats
		$this->incrementAllianceVsDeaths(ALLIANCE_VS_PORTS);

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

		// alliance vs. alliance stats
		$this->incrementAllianceVsDeaths(ALLIANCE_VS_PLANETS);
		$planetOwner->incrementAllianceVsKills(ALLIANCE_VS_PLANETS);

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
				', last_news_update='.$this->db->escapeNumber($this->lastNewsUpdate).
				', attack_warning='.$this->db->escapeString($this->attackColour).
				', dead='.$this->db->escapeBoolean($this->dead).
				', newbie_status='.$this->db->escapeBoolean($this->newbieStatus).
				', land_on_planet='.$this->db->escapeBoolean($this->landedOnPlanet).
				', last_active='.$this->db->escapeNumber($this->lastActive).
				', last_cpl_action='.$this->db->escapeNumber($this->lastCPLAction).
				', race_id='.$this->db->escapeNumber($this->raceID).
				', credits='.$this->db->escapeNumber($this->credits).
				', experience='.$this->db->escapeNumber($this->experience).
				', alignment='.$this->db->escapeNumber($this->alignment).
				', military_payment='.$this->db->escapeString($this->militaryPayment).
//				', past_knowledge='.$this->db->escapeString($this->pastKnowledge).
				', alliance_id='.$this->db->escapeNumber($this->allianceID).
				', alliance_join='.$this->db->escapeNumber($this->allianceJoinable).
				', ship_type_id='.$this->db->escapeNumber($this->shipID).
				', kills='.$this->db->escapeNumber($this->kills).
				', deaths='.$this->db->escapeNumber($this->deaths).
				', assists='.$this->db->escapeNumber($this->assists).
				', last_port='.$this->db->escapeNumber($this->lastPort).
				', bank='.$this->db->escapeNumber($this->bank).
				', zoom='.$this->db->escapeString($this->zoom).
				', display_missions='.$this->db->escapeBoolean($this->displayMissions).
				', force_drop_messages='.$this->db->escapeBoolean($this->forceDropMessages).
				', group_scout_messages='.$this->db->escapeString($this->groupScoutMessages).
				', ignore_globals='.$this->db->escapeBoolean($this->ignoreGlobals).
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

	/**
	 * Has this player been designated as the alliance flagship?
	 */
	public function isFlagship() {
		return $this->hasAlliance() && $this->getAlliance()->getFlagshipID() == $this->getAccountID();
	}

	public function isPresident() {
		return Council::getPresidentID($this->getGameID(), $this->getRaceID()) == $this->getAccountID();
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

	/**
	 * Returns the CSS class color to use when displaying the player's turns
	 */
	public function getTurnsColor() {
		switch($this->getTurnsLevel()) {
			case 'NONE':
			case 'LOW':
				return 'red';
			break;
			case 'MEDIUM':
				return 'yellow';
			break;
			default:
				return 'green';
		}
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

	public function sendMessage($receiverID, $messageTypeID, $message, $canBeIgnored = true, $unread = true, $expires = false, $senderDelete = false) {
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

		// If expires not specified, use default based on message type
		if ($expires === false) {
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
		}

		// Do not put scout messages in the sender's sent box
		if ($messageTypeID == MSG_SCOUT) {
			$senderDelete = true;
		}

		// send him the message
		self::doMessageSending($this->getAccountID(),$receiverID, $this->getGameID(), $messageTypeID, $message, $expires, $senderDelete, $unread);
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

	public function getPlottedCourse() {
		if(!isset($this->plottedCourse)) {
			// check if we have a course plotted
			$this->db->query('SELECT course FROM player_plotted_course WHERE ' . $this->SQL . ' LIMIT 1');

			if ($this->db->nextRecord()) {
				// get the course back
				$this->plottedCourse = unserialize($this->db->getField('course'));
			} else {
				$this->plottedCourse = false;
			}
		}

		// Update the plotted course if we have moved since the last query
		if ($this->plottedCourse !== false && (!isset($this->plottedCourseFrom) || $this->plottedCourseFrom != $this->getSectorID())) {
			$this->plottedCourseFrom = $this->getSectorID();

			if ($this->plottedCourse->getNextOnPath() == $this->getSectorID()) {
				// We have walked into the next sector of the course
				$this->plottedCourse->followPath();
				$this->setPlottedCourse($this->plottedCourse);
			} elseif ($this->plottedCourse->isInPath($this->getSectorID())) {
				// We have skipped to some later sector in the course
				$this->plottedCourse->skipToSector($this->getSectorID());
				$this->setPlottedCourse($this->plottedCourse);
			}
		}
		return $this->plottedCourse;
	}

	public function setPlottedCourse(Distance $plottedCourse) {
		$hadPlottedCourse = $this->hasPlottedCourse();
		$this->plottedCourse = $plottedCourse;
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

	function getExperienceRank() {
		return $this->computeRanking('experience', $this->getExperience());
	}
	function getKillsRank() {
		return $this->computeRanking('kills', $this->getKills());
	}
	function getDeathsRank() {
		return $this->computeRanking('deaths', $this->getDeaths());
	}
	function getAssistsRank() {
		return $this->computeRanking('assists', $this->getAssists());
	}
	private function computeRanking($dbField, $playerAmount) {
		$this->db->query('SELECT count(*) FROM player
			WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()) . '
			AND (
				'.$dbField.' > '.$this->db->escapeNumber($playerAmount).'
				OR (
					'.$dbField.' = '.$this->db->escapeNumber($playerAmount).'
					AND player_name <= ' . $this->db->escapeString($this->getPlayerName()) . '
				)
			)');
		$this->db->nextRecord();
		$rank = $this->db->getInt('count(*)');
		return $rank;
	}
}
