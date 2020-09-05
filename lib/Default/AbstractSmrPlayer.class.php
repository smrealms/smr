<?php declare(strict_types=1);
require_once('missions.inc');

// Exception thrown when a player cannot be found in the database
class PlayerNotFoundException extends Exception {}

abstract class AbstractSmrPlayer {

	const TIME_FOR_FEDERAL_BOUNTY_ON_PR = 10800;
	const TIME_FOR_ALLIANCE_SWITCH = 0;

	const HOF_CHANGED = 1;
	const HOF_NEW = 2;

	protected static $CACHE_SECTOR_PLAYERS = array();
	protected static $CACHE_PLANET_PLAYERS = array();
	protected static $CACHE_ALLIANCE_PLAYERS = array();
	protected static $CACHE_PLAYERS = array();

	protected $db;
	protected $SQL;

	protected $accountID;
	protected $gameID;
	protected $playerName;
	protected $playerID;
	protected $sectorID;
	protected $lastSectorID;
	protected $newbieTurns;
	protected $dead;
	protected $npc;
	protected $newbieStatus;
	protected $newbieWarning;
	protected $landedOnPlanet;
	protected $lastActive;
	protected $raceID;
	protected $credits;
	protected $alignment;
	protected $experience;
	protected $level;
	protected $allianceID;
	protected $shipID;
	protected $kills;
	protected $deaths;
	protected $assists;
	protected $stats;
	protected $personalRelations;
	protected $relations;
	protected $militaryPayment;
	protected $bounties;
	protected $turns;
	protected $lastCPLAction;
	protected $missions;

	protected $tickers;
	protected $lastTurnUpdate;
	protected $lastNewsUpdate;
	protected $attackColour;
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
	protected bool $raceChanged;
	protected $combatDronesKamikazeOnMines;
	protected $customShipName;
	protected $storedDestinations;

	protected $visitedSectors;
	protected $allianceRoles = array(
		0 => 0
	);

	protected $draftLeader;
	protected $gpWriter;
	protected $HOF;
	protected static $HOFVis;

	protected $hasChanged = false;
	protected array $hasHOFChanged = [];
	protected static $hasHOFVisChanged = array();
	protected $hasBountyChanged = array();

	public static function refreshCache() {
		foreach (self::$CACHE_PLAYERS as $gameID => &$gamePlayers) {
			foreach ($gamePlayers as $accountID => &$player) {
				$player = self::getPlayer($accountID, $gameID, true);
			}
		}
	}

	public static function clearCache() {
		self::$CACHE_PLAYERS = array();
		self::$CACHE_SECTOR_PLAYERS = array();
	}

	public static function savePlayers() {
		foreach (self::$CACHE_PLAYERS as $gamePlayers) {
			foreach ($gamePlayers as $player) {
				$player->save();
			}
		}
	}

	public static function getSectorPlayersByAlliances($gameID, $sectorID, array $allianceIDs, $forceUpdate = false) {
		$players = self::getSectorPlayers($gameID, $sectorID, $forceUpdate); // Don't use & as we do an unset
		foreach ($players as $accountID => $player) {
			if (!in_array($player->getAllianceID(), $allianceIDs)) {
				unset($players[$accountID]);
			}
		}
		return $players;
	}

	/**
	 * Returns the same players as getSectorPlayers (e.g. not on planets),
	 * but for an entire galaxy rather than a single sector. This is useful
	 * for reducing the number of queries in galaxy-wide processing.
	 */
	public static function getGalaxyPlayers($gameID, $galaxyID, $forceUpdate = false) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT player.*, sector_id FROM sector LEFT JOIN player USING(game_id, sector_id) WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND land_on_planet = ' . $db->escapeBoolean(false) . ' AND (last_cpl_action > ' . $db->escapeNumber(TIME - TIME_BEFORE_INACTIVE) . ' OR newbie_turns = 0) AND galaxy_id = ' . $db->escapeNumber($galaxyID));
		$galaxyPlayers = [];
		while ($db->nextRecord()) {
			$sectorID = $db->getInt('sector_id');
			if (!$db->hasField('account_id')) {
				self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID] = [];
			} else {
				$accountID = $db->getInt('account_id');
				$player = self::getPlayer($accountID, $gameID, $forceUpdate, $db);
				self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID][$accountID] = $player;
				$galaxyPlayers[$sectorID][$accountID] = $player;
			}
		}
		return $galaxyPlayers;
	}

	public static function getSectorPlayers($gameID, $sectorID, $forceUpdate = false) {
		if ($forceUpdate || !isset(self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID])) {
			$db = new SmrMySqlDatabase();
			$db->query('SELECT * FROM player WHERE sector_id = ' . $db->escapeNumber($sectorID) . ' AND game_id=' . $db->escapeNumber($gameID) . ' AND land_on_planet = ' . $db->escapeBoolean(false) . ' AND (last_cpl_action > ' . $db->escapeNumber(TIME - TIME_BEFORE_INACTIVE) . ' OR newbie_turns = 0) AND account_id NOT IN (' . $db->escapeArray(Globals::getHiddenPlayers()) . ') ORDER BY last_cpl_action DESC');
			$players = array();
			while ($db->nextRecord()) {
				$accountID = $db->getInt('account_id');
				$players[$accountID] = self::getPlayer($accountID, $gameID, $forceUpdate, $db);
			}
			self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID] = $players;
		}
		return self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID];
	}

	public static function getPlanetPlayers($gameID, $sectorID, $forceUpdate = false) {
		if ($forceUpdate || !isset(self::$CACHE_PLANET_PLAYERS[$gameID][$sectorID])) {
			$db = new SmrMySqlDatabase();
			$db->query('SELECT * FROM player WHERE sector_id = ' . $db->escapeNumber($sectorID) . ' AND game_id=' . $db->escapeNumber($gameID) . ' AND land_on_planet = ' . $db->escapeBoolean(true) . ' AND account_id NOT IN (' . $db->escapeArray(Globals::getHiddenPlayers()) . ') ORDER BY last_cpl_action DESC');
			$players = array();
			while ($db->nextRecord()) {
				$accountID = $db->getInt('account_id');
				$players[$accountID] = self::getPlayer($accountID, $gameID, $forceUpdate, $db);
			}
			self::$CACHE_PLANET_PLAYERS[$gameID][$sectorID] = $players;
		}
		return self::$CACHE_PLANET_PLAYERS[$gameID][$sectorID];
	}

	public static function getAlliancePlayers($gameID, $allianceID, $forceUpdate = false) {
		if ($forceUpdate || !isset(self::$CACHE_ALLIANCE_PLAYERS[$gameID][$allianceID])) {
			$db = new SmrMySqlDatabase();
			$db->query('SELECT * FROM player WHERE alliance_id = ' . $db->escapeNumber($allianceID) . ' AND game_id=' . $db->escapeNumber($gameID) . ' ORDER BY experience DESC');
			$players = array();
			while ($db->nextRecord()) {
				$accountID = $db->getInt('account_id');
				$players[$accountID] = self::getPlayer($accountID, $gameID, $forceUpdate, $db);
			}
			self::$CACHE_ALLIANCE_PLAYERS[$gameID][$allianceID] = $players;
		}
		return self::$CACHE_ALLIANCE_PLAYERS[$gameID][$allianceID];
	}

	public static function getPlayer($accountID, $gameID, $forceUpdate = false, $db = null) {
		if ($forceUpdate || !isset(self::$CACHE_PLAYERS[$gameID][$accountID])) {
			self::$CACHE_PLAYERS[$gameID][$accountID] = new SmrPlayer($gameID, $accountID, $db);
		}
		return self::$CACHE_PLAYERS[$gameID][$accountID];
	}

	public static function getPlayerByPlayerID($playerID, $gameID, $forceUpdate = false) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT * FROM player WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND player_id = ' . $db->escapeNumber($playerID) . ' LIMIT 1');
		if ($db->nextRecord()) {
			return self::getPlayer($db->getInt('account_id'), $gameID, $forceUpdate, $db);
		}
		throw new PlayerNotFoundException('Player ID not found.');
	}

	public static function getPlayerByPlayerName($playerName, $gameID, $forceUpdate = false) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT * FROM player WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND player_name = ' . $db->escapeString($playerName) . ' LIMIT 1');
		if ($db->nextRecord()) {
			return self::getPlayer($db->getInt('account_id'), $gameID, $forceUpdate, $db);
		}
		throw new PlayerNotFoundException('Player Name not found.');
	}

	protected function __construct($gameID, $accountID, $db = null) {
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
			$this->accountID = (int)$accountID;
			$this->gameID = (int)$gameID;
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
			$this->raceChanged = $db->getBoolean('race_changed');
			$this->combatDronesKamikazeOnMines = $db->getBoolean('combat_drones_kamikaze_on_mines');
		} else {
			throw new PlayerNotFoundException('Invalid accountID: ' . $accountID . ' OR gameID:' . $gameID);
		}
	}

	/**
	 * Insert a new player into the database. Returns the new player object.
	 */
	public static function createPlayer($accountID, $gameID, $playerName, $raceID, $isNewbie, $npc=false) {
		$db = new SmrMySqlDatabase();
		$db->lockTable('player');

		// Player names must be unique within each game
		$db->query('SELECT 1 FROM player WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND player_name = ' . $db->escapeString($playerName) . ' LIMIT 1');
		if ($db->nextRecord() > 0) {
			$db->unlock();
			create_error('The player name already exists.');
		}

		// get last registered player id in that game and increase by one.
		$db->query('SELECT MAX(player_id) FROM player WHERE game_id = ' . $db->escapeNumber($gameID));
		if ($db->nextRecord()) {
			$playerID = $db->getInt('MAX(player_id)') + 1;
		} else {
			$playerID = 1;
		}

		$startSectorID = 0; // Temporarily put player into non-existent sector
		$db->query('INSERT INTO player (account_id, game_id, player_id, player_name, race_id, sector_id, last_cpl_action, last_active, npc, newbie_status)
					VALUES(' . $db->escapeNumber($accountID) . ', ' . $db->escapeNumber($gameID) . ', ' . $db->escapeNumber($playerID) . ', ' . $db->escapeString($playerName) . ', ' . $db->escapeNumber($raceID) . ', ' . $db->escapeNumber($startSectorID) . ', ' . $db->escapeNumber(TIME) . ', ' . $db->escapeNumber(TIME) . ',' . $db->escapeBoolean($npc) . ',' . $db->escapeBoolean($isNewbie) . ')');

		$db->unlock();

		$player = SmrPlayer::getPlayer($accountID, $gameID);
		$player->setSectorID($player->getHome());
		return $player;
	}

	/**
	 * Get array of players whose info can be accessed by this player.
	 * Skips players who are not in the same alliance as this player.
	 */
	public function getSharingPlayers($forceUpdate = false) {
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

	public function getSQL() : string {
		return $this->SQL;
	}

	public function getZoom() {
		return $this->zoom;
	}

	protected function setZoom($zoom) {
		// Set the zoom level between [1, 9]
		$zoom = max(1, min(9, $zoom));
		if ($this->zoom == $zoom) {
			return;
		}
		$this->zoom = $zoom;
		$this->hasChanged = true;
	}

	public function increaseZoom($zoom) {
		if ($zoom < 0) {
			throw new Exception('Trying to increase negative zoom.');
		}
		$this->setZoom($this->getZoom() + $zoom);
	}

	public function decreaseZoom($zoom) {
		if ($zoom < 0) {
			throw new Exception('Trying to decrease negative zoom.');
		}
		$this->setZoom($this->getZoom() - $zoom);
	}

	public function getAttackColour() {
		return $this->attackColour;
	}

	public function setAttackColour($colour) {
		if ($this->attackColour == $colour) {
			return;
		}
		$this->attackColour = $colour;
		$this->hasChanged = true;
	}

	public function isIgnoreGlobals() {
		return $this->ignoreGlobals;
	}

	public function setIgnoreGlobals($bool) {
		if ($this->ignoreGlobals == $bool) {
			return;
		}
		$this->ignoreGlobals = $bool;
		$this->hasChanged = true;
	}

	public function getAccount() {
		return SmrAccount::getAccount($this->getAccountID());
	}

	public function getAccountID() {
		return $this->accountID;
	}

	public function getGameID() {
		return $this->gameID;
	}

	public function getGame() {
		return SmrGame::getGame($this->gameID);
	}

	public function getNewbieTurns() {
		return $this->newbieTurns;
	}

	public function hasNewbieTurns() {
		return $this->getNewbieTurns() > 0;
	}
	public function setNewbieTurns($newbieTurns) {
		if ($this->newbieTurns == $newbieTurns) {
			return;
		}
		$this->newbieTurns = $newbieTurns;
		$this->hasChanged = true;
	}

	public function getShip($forceUpdate = false) {
		return SmrShip::getShip($this, $forceUpdate);
	}

	public function getShipTypeID() {
		return $this->shipID;
	}

	/**
	 * Do not call directly. Use SmrShip::setShipTypeID instead.
	 */
	public function setShipTypeID($shipID) {
		if ($this->shipID == $shipID) {
			return;
		}
		$this->shipID = $shipID;
		$this->hasChanged = true;
	}

	public function hasCustomShipName() {
		return $this->getCustomShipName() !== false;
	}

	public function getCustomShipName() {
		if (!isset($this->customShipName)) {
			$this->db->query('SELECT * FROM ship_has_name WHERE ' . $this->SQL . ' LIMIT 1');
			if ($this->db->nextRecord()) {
				$this->customShipName = $this->db->getField('ship_name');
			} else {
				$this->customShipName = false;
			}
		}
		return $this->customShipName;
	}

	public function setCustomShipName(string $name) {
		$this->db->query('REPLACE INTO ship_has_name (game_id, account_id, ship_name)
			VALUES (' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeString($name) . ')');
	}

	/**
	 * Get planet owned by this player.
	 * Returns false if this player does not own a planet.
	 */
	public function getPlanet() {
		$this->db->query('SELECT * FROM planet WHERE game_id=' . $this->db->escapeNumber($this->getGameID()) . ' AND owner_id=' . $this->db->escapeNumber($this->getAccountID()));
		if ($this->db->nextRecord()) {
			return SmrPlanet::getPlanet($this->getGameID(), $this->db->getInt('sector_id'), false, $this->db);
		} else {
			return false;
		}
	}

	public function getSectorPlanet() {
		return SmrPlanet::getPlanet($this->getGameID(), $this->getSectorID());
	}

	public function getSectorPort() {
		return SmrPort::getPort($this->getGameID(), $this->getSectorID());
	}

	public function getSectorID() {
		return $this->sectorID;
	}

	public function getSector() {
		return SmrSector::getSector($this->getGameID(), $this->getSectorID());
	}

	public function setSectorID($sectorID) {
		if ($this->sectorID == $sectorID) {
			return;
		}

		$port = SmrPort::getPort($this->getGameID(), $this->getSectorID());
		$port->addCachePort($this->getAccountID()); //Add port of sector we were just in, to make sure it is left totally up to date.

		$this->setLastSectorID($this->getSectorID());
		$this->actionTaken('LeaveSector', ['SectorID' => $this->getSectorID()]);
		$this->sectorID = $sectorID;
		$this->actionTaken('EnterSector', ['SectorID' => $this->getSectorID()]);
		$this->hasChanged = true;

		$port = SmrPort::getPort($this->getGameID(), $this->getSectorID());
		$port->addCachePort($this->getAccountID()); //Add the port of sector we are now in.
	}

	public function getLastSectorID() {
		return $this->lastSectorID;
	}

	public function setLastSectorID($lastSectorID) {
		if ($this->lastSectorID == $lastSectorID) {
			return;
		}
		$this->lastSectorID = $lastSectorID;
		$this->hasChanged = true;
	}

	public function getHome() {
		// get his home sector
		$hq_id = GOVERNMENT + $this->getRaceID();
		$raceHqSectors = SmrSector::getLocationSectors($this->getGameID(), $hq_id);
		if (!empty($raceHqSectors)) {
			// If race has multiple HQ's for some reason, use the first one
			return key($raceHqSectors);
		} else {
			return 1;
		}
	}

	public function isDead() {
		return $this->dead;
	}

	public function isNPC() {
		return $this->npc;
	}

	/**
	 * Does the player have Newbie status?
	 */
	public function hasNewbieStatus() {
		return $this->newbieStatus;
	}

	/**
	 * Update the player's newbie status if it has changed.
	 * This function queries the account, so use sparingly.
	 */
	public function updateNewbieStatus() {
		$accountNewbieStatus = !$this->getAccount()->isVeteran();
		if ($this->newbieStatus != $accountNewbieStatus) {
			$this->newbieStatus = $accountNewbieStatus;
			$this->hasChanged = true;
		}
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
		return Council::isOnCouncil($this->getGameID(), $this->getRaceID(), $this->getAccountID());
	}

	public function isDraftLeader() {
		if (!isset($this->draftLeader)) {
			$this->draftLeader = false;
			$this->db->query('SELECT 1 FROM draft_leaders WHERE ' . $this->SQL . ' LIMIT 1');
			if ($this->db->nextRecord()) {
				$this->draftLeader = true;
			}
		}
		return $this->draftLeader;
	}

	public function getGPWriter() {
		if (!isset($this->gpWriter)) {
			$this->gpWriter = false;
			$this->db->query('SELECT position FROM galactic_post_writer WHERE ' . $this->SQL);
			if ($this->db->nextRecord()) {
				$this->gpWriter = $this->db->getField('position');
			}
		}
		return $this->gpWriter;
	}

	public function isGPEditor() {
		return $this->getGPWriter() == 'editor';
	}

	public function isForceDropMessages() {
		return $this->forceDropMessages;
	}

	public function setForceDropMessages($bool) {
		if ($this->forceDropMessages == $bool) {
			return;
		}
		$this->forceDropMessages = $bool;
		$this->hasChanged = true;
	}

	public function getScoutMessageGroupLimit() {
		if ($this->groupScoutMessages == 'ALWAYS') {
			return 0;
		} elseif ($this->groupScoutMessages == 'AUTO') {
			return MESSAGES_PER_PAGE;
		} elseif ($this->groupScoutMessages == 'NEVER') {
			return PHP_INT_MAX;
		}
	}

	public function getGroupScoutMessages() {
		return $this->groupScoutMessages;
	}

	public function setGroupScoutMessages($setting) {
		if ($this->groupScoutMessages == $setting) {
			return;
		}
		$this->groupScoutMessages = $setting;
		$this->hasChanged = true;
	}

	protected static function doMessageSending($senderID, $receiverID, $gameID, $messageTypeID, $message, $expires, $senderDelete = false, $unread = true) {
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
		// Keep track of the message_id so it can be returned
		$insertID = $db->getInsertID();

		if ($unread === true) {
			// give him the message icon
			$db->query('REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES
						(' . $db->escapeNumber($gameID) . ', ' . $db->escapeNumber($receiverID) . ', ' . $db->escapeNumber($messageTypeID) . ')');
		}

		switch ($messageTypeID) {
			case MSG_PLAYER:
				$receiverAccount = SmrAccount::getAccount($receiverID);
				if ($receiverAccount->isValidated() && $receiverAccount->isReceivingMessageNotifications($messageTypeID) && !$receiverAccount->isLoggedIn()) {
					require_once(get_file_loc('message.functions.inc'));
					$sender = getMessagePlayer($senderID, $gameID, $messageTypeID);
					if ($sender instanceof SmrPlayer) {
						$sender = $sender->getDisplayName();
					}
					$mail = setupMailer();
					$mail->Subject = 'Message Notification';
					$mail->setFrom('notifications@smrealms.de', 'SMR Notifications');
					$bbifiedMessage = 'From: ' . $sender . ' Date: ' . date($receiverAccount->getShortDateFormat() . ' ' . $receiverAccount->getShortTimeFormat(), TIME) . "<br/>\r\n<br/>\r\n" . bbifyMessage($message, true);
					$mail->msgHTML($bbifiedMessage);
					$mail->AltBody = strip_tags($bbifiedMessage);
					$mail->addAddress($receiverAccount->getEmail(), $receiverAccount->getHofName());
					$mail->send();
					$receiverAccount->decreaseMessageNotifications($messageTypeID, 1);
					$receiverAccount->update();
				}
			break;
		}

		return $insertID;
	}

	public function sendMessageToBox($boxTypeID, $message) {
		// send him the message
		SmrAccount::doMessageSendingToBox($this->getAccountID(), $boxTypeID, $message, $this->getGameID());
	}

	public function sendGlobalMessage($message, $canBeIgnored = true) {
		if ($canBeIgnored) {
			if ($this->getAccount()->isMailBanned()) {
				create_error('You are currently banned from sending messages');
			}
		}
		$this->sendMessageToBox(BOX_GLOBALS, $message);

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
		if ($canBeIgnored) {
			if ($this->getAccount()->isMailBanned()) {
				create_error('You are currently banned from sending messages');
			}
			// Don't send messages to players ignoring us
			$this->db->query('SELECT account_id FROM message_blacklist WHERE account_id=' . $this->db->escapeNumber($receiverID) . ' AND blacklisted_id=' . $this->db->escapeNumber($this->getAccountID()) . ' LIMIT 1');
			if ($this->db->nextRecord()) {
				return;
			}
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

		// send him the message and return the message_id
		return self::doMessageSending($this->getAccountID(), $receiverID, $this->getGameID(), $messageTypeID, $message, $expires, $senderDelete, $unread);
	}

	public function sendMessageFromOpAnnounce($receiverID, $message, $expires = false) {
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
		self::doMessageSending(ACCOUNT_ID_PLANET, $receiverID, $gameID, MSG_PLANET, $message, $expires);
	}

	public static function sendMessageFromPort($gameID, $receiverID, $message) {
		//get expire time
		$expires = TIME + 86400 * 31;
		// send him the message
		self::doMessageSending(ACCOUNT_ID_PORT, $receiverID, $gameID, MSG_PLAYER, $message, $expires);
	}

	public static function sendMessageFromFedClerk($gameID, $receiverID, $message) {
		$expires = TIME + 86400 * 365;
		self::doMessageSending(ACCOUNT_ID_FED_CLERK, $receiverID, $gameID, MSG_PLAYER, $message, $expires);
	}

	public static function sendMessageFromAdmin($gameID, $receiverID, $message, $expires = false) {
		//get expire time
		if ($expires === false) {
			$expires = TIME + 86400 * 365;
		}
		// send him the message
		self::doMessageSending(ACCOUNT_ID_ADMIN, $receiverID, $gameID, MSG_ADMIN, $message, $expires);
	}

	public static function sendMessageFromAllianceAmbassador($gameID, $receiverID, $message, $expires = false) {
		//get expire time
		if ($expires === false) {
			$expires = TIME + 86400 * 31;
		}
		// send him the message
		self::doMessageSending(ACCOUNT_ID_ALLIANCE_AMBASSADOR, $receiverID, $gameID, MSG_ALLIANCE, $message, $expires);
	}

	public static function sendMessageFromCasino($gameID, $receiverID, $message, $expires = false) {
		//get expire time
		if ($expires === false) {
			$expires = TIME + 86400 * 7;
		}
		// send him the message
		self::doMessageSending(ACCOUNT_ID_CASINO, $receiverID, $gameID, MSG_CASINO, $message, $expires);
	}

	public static function sendMessageFromRace($raceID, $gameID, $receiverID, $message, $expires = false) {
		//get expire time
		if ($expires === false) {
			$expires = TIME + 86400 * 5;
		}
		// send him the message
		self::doMessageSending(ACCOUNT_ID_GROUP_RACES + $raceID, $receiverID, $gameID, MSG_POLITICAL, $message, $expires);
	}

	public function setMessagesRead($messageTypeID) {
		$this->db->query('DELETE FROM player_has_unread_messages
							WHERE '.$this->SQL . ' AND message_type_id = ' . $this->db->escapeNumber($messageTypeID));
	}

	public function getSafeAttackRating() {
		return max(0, min(8, $this->getAlignment() / 150 + 4));
	}

	public function hasFederalProtection() {
		$sector = SmrSector::getSector($this->getGameID(), $this->getSectorID());
		if (!$sector->offersFederalProtection()) {
			return false;
		}

		$ship = $this->getShip();
		if ($ship->hasIllegalGoods()) {
			return false;
		}

		if ($ship->getAttackRating() <= $this->getSafeAttackRating()) {
			foreach ($sector->getFedRaceIDs() as $fedRaceID) {
				if ($this->canBeProtectedByRace($fedRaceID)) {
					return true;
				}
			}
		}

		return false;
	}

	public function canBeProtectedByRace($raceID) {
		if (!isset($this->canFed)) {
			$this->canFed = array();
			$RACES = Globals::getRaces();
			foreach ($RACES as $raceID2 => $raceName) {
				$this->canFed[$raceID2] = $this->getRelation($raceID2) >= ALIGN_FED_PROTECTION;
			}
			$this->db->query('SELECT race_id, allowed FROM player_can_fed
								WHERE ' . $this->SQL . ' AND expiry > ' . $this->db->escapeNumber(TIME));
			while ($this->db->nextRecord()) {
				$this->canFed[$this->db->getInt('race_id')] = $this->db->getBoolean('allowed');
			}
		}
		return $this->canFed[$raceID];
	}

	/**
	 * Returns a boolean identifying if the player can currently
	 * participate in battles.
	 */
	public function canFight() {
		return !($this->hasNewbieTurns() ||
		         $this->isDead() ||
		         $this->isLandedOnPlanet() ||
		         $this->hasFederalProtection());
	}

	public function setDead($bool) {
		if ($this->dead == $bool) {
			return;
		}
		$this->dead = $bool;
		$this->hasChanged = true;
	}

	public function getKills() {
		return $this->kills;
	}

	public function increaseKills($kills) {
		if ($kills < 0) {
			throw new Exception('Trying to increase negative kills.');
		}
		$this->setKills($this->kills + $kills);
	}

	public function setKills($kills) {
		if ($this->kills == $kills) {
			return;
		}
		$this->kills = $kills;
		$this->hasChanged = true;
	}

	public function getDeaths() {
		return $this->deaths;
	}

	public function increaseDeaths($deaths) {
		if ($deaths < 0) {
			throw new Exception('Trying to increase negative deaths.');
		}
		$this->setDeaths($this->getDeaths() + $deaths);
	}

	public function setDeaths($deaths) {
		if ($this->deaths == $deaths) {
			return;
		}
		$this->deaths = $deaths;
		$this->hasChanged = true;
	}

	public function getAssists() {
		return $this->assists;
	}

	public function increaseAssists($assists) {
		if ($assists < 1) {
			throw new Exception('Must increase by a positive number.');
		}
		$this->assists += $assists;
		$this->hasChanged = true;
	}

	public function getAlignment() {
		return $this->alignment;
	}

	public function increaseAlignment($align) {
		if ($align < 0) {
			throw new Exception('Trying to increase negative align.');
		}
		if ($align == 0) {
			return;
		}
		$align += $this->alignment;
		$this->setAlignment($align);
	}

	public function decreaseAlignment($align) {
		if ($align < 0) {
			throw new Exception('Trying to decrease negative align.');
		}
		if ($align == 0) {
			return;
		}
		$align = $this->alignment - $align;
		$this->setAlignment($align);
	}

	public function setAlignment($align) {
		if ($this->alignment == $align) {
			return;
		}
		$this->alignment = $align;
		$this->hasChanged = true;
	}

	public function getCredits() {
		return $this->credits;
	}

	public function getBank() {
		return $this->bank;
	}

	/**
	 * Increases personal bank account up to the maximum allowed credits.
	 * Returns the amount that was actually added to handle overflow.
	 */
	public function increaseBank(int $credits) : int {
		if ($credits == 0) {
			return 0;
		}
		if ($credits < 0) {
			throw new Exception('Trying to increase negative credits.');
		}
		$newTotal = min($this->bank + $credits, MAX_MONEY);
		$actualAdded = $newTotal - $this->bank;
		$this->setBank($newTotal);
		return $actualAdded;
	}

	public function decreaseBank(int $credits) : void {
		if ($credits == 0) {
			return;
		}
		if ($credits < 0) {
			throw new Exception('Trying to decrease negative credits.');
		}
		$newTotal = $this->bank - $credits;
		$this->setBank($newTotal);
	}

	public function setBank(int $credits) : void {
		if ($this->bank == $credits) {
			return;
		}
		if ($credits < 0) {
			throw new Exception('Trying to set negative credits.');
		}
		if ($credits > MAX_MONEY) {
			throw new Exception('Trying to set more than max credits.');
		}
		$this->bank = $credits;
		$this->hasChanged = true;
	}

	public function getExperience() {
		return $this->experience;
	}

	/**
	 * Returns the percent progress towards the next level.
	 * This value is rounded because it is used primarily in HTML img widths.
	 */
	public function getNextLevelPercentAcquired() : int {
		if ($this->getNextLevelExperience() == $this->getThisLevelExperience()) {
			return 100;
		}
		return max(0, min(100, IRound(($this->getExperience() - $this->getThisLevelExperience()) / ($this->getNextLevelExperience() - $this->getThisLevelExperience()) * 100)));
	}

	public function getNextLevelPercentRemaining() {
		return 100 - $this->getNextLevelPercentAcquired();
	}

	public function getNextLevelExperience() {
		$LEVELS_REQUIREMENTS = Globals::getLevelRequirements();
		if (!isset($LEVELS_REQUIREMENTS[$this->getLevelID() + 1])) {
			return $this->getThisLevelExperience(); //Return current level experience if on last level.
		}
		return $LEVELS_REQUIREMENTS[$this->getLevelID() + 1]['Requirement'];
	}

	public function getThisLevelExperience() {
		$LEVELS_REQUIREMENTS = Globals::getLevelRequirements();
		return $LEVELS_REQUIREMENTS[$this->getLevelID()]['Requirement'];
	}

	public function setExperience($experience) {
		if ($this->experience == $experience) {
			return;
		}
		if ($experience < MIN_EXPERIENCE) {
			$experience = MIN_EXPERIENCE;
		}
		if ($experience > MAX_EXPERIENCE) {
			$experience = MAX_EXPERIENCE;
		}
		$this->experience = $experience;
		$this->hasChanged = true;

		// Since exp has changed, invalidate the player level so that it can
		// be recomputed next time it is queried (in case it has changed).
		$this->level = null;
	}

	/**
	 * Increases onboard credits up to the maximum allowed credits.
	 * Returns the amount that was actually added to handle overflow.
	 */
	public function increaseCredits(int $credits) : int {
		if ($credits == 0) {
			return 0;
		}
		if ($credits < 0) {
			throw new Exception('Trying to increase negative credits.');
		}
		$newTotal = min($this->credits + $credits, MAX_MONEY);
		$actualAdded = $newTotal - $this->credits;
		$this->setCredits($newTotal);
		return $actualAdded;
	}

	public function decreaseCredits(int $credits) : void {
		if ($credits == 0) {
			return;
		}
		if ($credits < 0) {
			throw new Exception('Trying to decrease negative credits.');
		}
		$newTotal = $this->credits - $credits;
		$this->setCredits($newTotal);
	}

	public function setCredits(int $credits) : void {
		if ($this->credits == $credits) {
			return;
		}
		if ($credits < 0) {
			throw new Exception('Trying to set negative credits.');
		}
		if ($credits > MAX_MONEY) {
			throw new Exception('Trying to set more than max credits.');
		}
		$this->credits = $credits;
		$this->hasChanged = true;
	}

	public function increaseExperience($experience) {
		if ($experience < 0) {
			throw new Exception('Trying to increase negative experience.');
		}
		if ($experience == 0) {
			return;
		}
		$newExperience = $this->experience + $experience;
		$this->setExperience($newExperience);
		$this->increaseHOF($experience, array('Experience', 'Total', 'Gain'), HOF_PUBLIC);
	}
	public function decreaseExperience($experience) {
		if ($experience < 0) {
			throw new Exception('Trying to decrease negative experience.');
		}
		if ($experience == 0) {
			return;
		}
		$newExperience = $this->experience - $experience;
		$this->setExperience($newExperience);
		$this->increaseHOF($experience, array('Experience', 'Total', 'Loss'), HOF_PUBLIC);
	}

	public function isLandedOnPlanet() {
		return $this->landedOnPlanet;
	}

	public function setLandedOnPlanet($bool) {
		if ($this->landedOnPlanet == $bool) {
			return;
		}
		$this->landedOnPlanet = $bool;
		$this->hasChanged = true;
	}

	/**
	 * Returns the numerical level of the player (e.g. 1-50).
	 */
	public function getLevelID() {
		// The level is cached for performance reasons unless `setExperience`
		// is called and the player's experience changes.
		if ($this->level === null) {
			$LEVELS_REQUIREMENTS = Globals::getLevelRequirements();
			foreach ($LEVELS_REQUIREMENTS as $level_id => $require) {
				if ($this->getExperience() >= $require['Requirement']) {
					continue;
				}
				$this->level = $level_id - 1;
				return $this->level;
			}
			$this->level = max(array_keys($LEVELS_REQUIREMENTS));
		}
		return $this->level;
	}

	public function getLevelName() {
		$level_name = Globals::getLevelRequirements()[$this->getLevelID()]['Name'];
		if ($this->isPresident()) {
			$level_name = '<img src="images/council_president.png" title="' . Globals::getRaceName($this->getRaceID()) . ' President" height="12" width="16" />&nbsp;' . $level_name;
		}
		return $level_name;
	}

	public function getMaxLevel() {
		return max(array_keys(Globals::getLevelRequirements()));
	}

	public function getPlayerID() {
		return $this->playerID;
	}

	/**
	 * Returns the player name.
	 * Use getDisplayName or getLinkedDisplayName for HTML-safe versions.
	 */
	public function getPlayerName() {
		return $this->playerName;
	}

	public function setPlayerName($name) {
		$this->playerName = $name;
		$this->hasChanged = true;
	}

	/**
	 * Returns the decorated player name, suitable for HTML display.
	 */
	public function getDisplayName($includeAlliance = false) {
		$name = htmlentities($this->playerName) . ' (' . $this->getPlayerID() . ')';
		$return = get_colored_text($this->getAlignment(), $name);
		if ($this->isNPC()) {
			$return .= ' <span class="npcColour">[NPC]</span>';
		}
		if ($includeAlliance) {
			$return .= ' (' . $this->getAllianceDisplayName() . ')';
		}
		return $return;
	}

	public function getBBLink() {
			return '[player=' . $this->getPlayerID() . ']';
	}

	public function getLinkedDisplayName($includeAlliance = true) {
		$return = '<a href="' . $this->getTraderSearchHREF() . '">' . $this->getDisplayName() . '</a>';
		if ($includeAlliance) {
			$return .= ' (' . $this->getAllianceDisplayName(true) . ')';
		}
		return $return;
	}

	/**
	 * Use this method when the player is changing their own name.
	 * This will flag the player as having used their free name change.
	 */
	public function setPlayerNameByPlayer($playerName) {
		$this->setPlayerName($playerName);
		$this->setNameChanged(true);
	}

	public function isNameChanged() {
		return $this->nameChanged;
	}

	public function setNameChanged($bool) {
		$this->nameChanged = $bool;
		$this->hasChanged = true;
	}

	public function isRaceChanged() : bool {
		return $this->raceChanged;
	}

	public function setRaceChanged(bool $raceChanged) : void {
		$this->raceChanged = $raceChanged;
		$this->hasChanged = true;
	}

	public function canChangeRace() : bool {
		return !$this->isRaceChanged() && (TIME - $this->getGame()->getStartTime() < TIME_FOR_RACE_CHANGE);
	}

	public function getRaceID() {
		return $this->raceID;
	}

	public function getRaceName() {
		return Globals::getRaceName($this->getRaceID());
	}

	public static function getColouredRaceNameOrDefault($otherRaceID, AbstractSmrPlayer $player = null, $linked = false) {
		$relations = 0;
		if ($player !== null) {
			$relations = $player->getRelation($otherRaceID);
		}
		return Globals::getColouredRaceName($otherRaceID, $relations, $linked);
	}

	public function getColouredRaceName($otherRaceID, $linked = false) {
		return self::getColouredRaceNameOrDefault($otherRaceID, $this, $linked);
	}

	public function setRaceID($raceID) {
		if ($this->raceID == $raceID) {
			return;
		}
		$this->raceID = $raceID;
		$this->hasChanged = true;
	}

	public function isAllianceLeader($forceUpdate = false) {
		return $this->getAccountID() == $this->getAlliance($forceUpdate)->getLeaderID();
	}

	public function getAlliance($forceUpdate = false) {
		return SmrAlliance::getAlliance($this->getAllianceID(), $this->getGameID(), $forceUpdate);
	}

	public function getAllianceID() {
		return $this->allianceID;
	}

	public function hasAlliance() {
		return $this->getAllianceID() != 0;
	}

	protected function setAllianceID($ID) {
		if ($this->allianceID == $ID) {
			return;
		}
		$this->allianceID = $ID;
		if ($this->allianceID != 0) {
			$status = $this->hasNewbieStatus() ? 'NEWBIE' : 'VETERAN';
			$this->db->query('INSERT IGNORE INTO player_joined_alliance (account_id,game_id,alliance_id,status) ' .
				'VALUES (' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ',' . $this->db->escapeString($status) . ')');
		}
		$this->hasChanged = true;
	}

	public function getAllianceBBLink() {
		return $this->hasAlliance() ? $this->getAlliance()->getAllianceBBLink() : $this->getAllianceDisplayName();
	}

	public function getAllianceDisplayName($linked = false, $includeAllianceID = false) {
		if ($this->hasAlliance()) {
			return $this->getAlliance()->getAllianceDisplayName($linked, $includeAllianceID);
		} else {
			return 'No Alliance';
		}
	}

	public function getAllianceRole($allianceID = false) {
		if ($allianceID === false) {
			$allianceID = $this->getAllianceID();
		}
		if (!isset($this->allianceRoles[$allianceID])) {
			$this->allianceRoles[$allianceID] = 0;
			$this->db->query('SELECT role_id
						FROM player_has_alliance_role
						WHERE ' . $this->SQL . '
						AND alliance_id=' . $this->db->escapeNumber($allianceID) . '
						LIMIT 1');
			if ($this->db->nextRecord()) {
				$this->allianceRoles[$allianceID] = $this->db->getInt('role_id');
			}
		}
		return $this->allianceRoles[$allianceID];
	}

	public function leaveAlliance(AbstractSmrPlayer $kickedBy = null) {
		$allianceID = $this->getAllianceID();
		$alliance = $this->getAlliance();
		if ($kickedBy != null) {
			$kickedBy->sendMessage($this->getAccountID(), MSG_PLAYER, 'You were kicked out of the alliance!', false);
			$this->actionTaken('PlayerKicked', array('Alliance' => $alliance, 'Player' => $kickedBy));
			$kickedBy->actionTaken('KickPlayer', array('Alliance' => $alliance, 'Player' => $this));
		} elseif ($this->isAllianceLeader()) {
			$this->actionTaken('DisbandAlliance', array('Alliance' => $alliance));
		} else {
			$this->actionTaken('LeaveAlliance', array('Alliance' => $alliance));
			if ($alliance->getLeaderID() != 0 && $alliance->getLeaderID() != ACCOUNT_ID_NHL) {
				$this->sendMessage($alliance->getLeaderID(), MSG_PLAYER, 'I left your alliance!', false);
			}
		}

		$this->setAllianceID(0);
		$this->db->query('DELETE FROM player_has_alliance_role WHERE ' . $this->SQL);

		if (!$this->isAllianceLeader() && $allianceID != NHA_ID) { // Don't have a delay for switching alliance after leaving NHA, or for disbanding an alliance.
			$this->setAllianceJoinable(TIME + self::TIME_FOR_ALLIANCE_SWITCH);
			$alliance->getLeader()->setAllianceJoinable(TIME + self::TIME_FOR_ALLIANCE_SWITCH); //We set the joinable time for leader here, that way a single player alliance won't cause a player to wait before switching.
		}
	}

	/**
	 * Join an alliance (used for both Leader and New Member roles)
	 */
	public function joinAlliance($allianceID) {
		$this->setAllianceID($allianceID);
		$alliance = $this->getAlliance();

		if (!$this->isAllianceLeader()) {
			// Do not throw an exception if the NHL account doesn't exist.
			try {
				$this->sendMessage($alliance->getLeaderID(), MSG_PLAYER, 'I joined your alliance!', false);
			} catch (AccountNotFoundException $e) {
				if ($alliance->getLeaderID() != ACCOUNT_ID_NHL) {
					throw $e;
				}
			}

			$roleID = ALLIANCE_ROLE_NEW_MEMBER;
		} else {
			$roleID = ALLIANCE_ROLE_LEADER;
		}
		$this->db->query('INSERT INTO player_has_alliance_role (game_id, account_id, role_id, alliance_id) VALUES (' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($roleID) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ')');

		$this->actionTaken('JoinAlliance', array('Alliance' => $alliance));
	}

	public function getAllianceJoinable() {
		return $this->allianceJoinable;
	}

	private function setAllianceJoinable($time) {
		if ($this->allianceJoinable == $time) {
			return;
		}
		$this->allianceJoinable = $time;
		$this->hasChanged = true;
	}

	/**
	 * Invites player with $accountID to this player's alliance.
	 */
	public function sendAllianceInvitation(int $accountID, string $message, int $expires) : void {
		if (!$this->hasAlliance()) {
			throw new Exception('Must be in an alliance to send alliance invitations');
		}
		// Send message to invited player
		$messageID = $this->sendMessage($accountID, MSG_PLAYER, $message, false, true, $expires, true);
		SmrInvitation::send($this->getAllianceID(), $this->getGameID(), $accountID, $this->getAccountID(), $messageID, $expires);
	}

	public function isCombatDronesKamikazeOnMines() {
		return $this->combatDronesKamikazeOnMines;
	}

	public function setCombatDronesKamikazeOnMines($bool) {
		if ($this->combatDronesKamikazeOnMines == $bool) {
			return;
		}
		$this->combatDronesKamikazeOnMines = $bool;
		$this->hasChanged = true;
	}

	protected function getPersonalRelationsData() {
		if (!isset($this->personalRelations)) {
			//get relations
			$RACES = Globals::getRaces();
			$this->personalRelations = array();
			foreach ($RACES as $raceID => $raceName) {
				$this->personalRelations[$raceID] = 0;
			}
			$this->db->query('SELECT race_id,relation FROM player_has_relation WHERE ' . $this->SQL . ' LIMIT ' . count($RACES));
			while ($this->db->nextRecord()) {
				$this->personalRelations[$this->db->getInt('race_id')] = $this->db->getInt('relation');
			}
		}
	}

	public function getPersonalRelations() {
		$this->getPersonalRelationsData();
		return $this->personalRelations;
	}

	/**
	 * Get personal relations with a race
	 */
	public function getPersonalRelation($raceID) {
		$rels = $this->getPersonalRelations();
		return $rels[$raceID];
	}

	/**
	 * Get total relations with all races (personal + political)
	 */
	public function getRelations() {
		if (!isset($this->relations)) {
			//get relations
			$RACES = Globals::getRaces();
			$raceRelations = Globals::getRaceRelations($this->getGameID(), $this->getRaceID());
			$personalRels = $this->getPersonalRelations(); // make sure they're initialised.
			$this->relations = array();
			foreach ($RACES as $raceID => $raceName) {
				$this->relations[$raceID] = $personalRels[$raceID] + $raceRelations[$raceID];
			}
		}
		return $this->relations;
	}

	/**
	 * Get total relations with a race (personal + political)
	 */
	public function getRelation($raceID) {
		$rels = $this->getRelations();
		return $rels[$raceID];
	}

	/**
	 * Increases personal relations from trading $numGoods units with the race
	 * of the port given by $raceID.
	 */
	public function increaseRelationsByTrade($numGoods, $raceID) {
		$relations = ICeil(min($numGoods, 300) / 30);
		//Cap relations to a max of 1 after 500 have been reached
		if ($this->getPersonalRelation($raceID) + $relations >= 500) {
			$relations = max(1, min($relations, 500 - $this->getPersonalRelation($raceID)));
		}
		$this->increaseRelations($relations, $raceID);
	}

	/**
	 * Decreases personal relations from trading failures, e.g. rejected
	 * bargaining and getting caught stealing.
	 */
	public function decreaseRelationsByTrade($numGoods, $raceID) {
		$relations = ICeil(min($numGoods, 300) / 30);
		$this->decreaseRelations($relations, $raceID);
	}

	/**
	 * Increase personal relations.
	 */
	public function increaseRelations($relations, $raceID) {
		if ($relations < 0) {
			throw new Exception('Trying to increase negative relations.');
		}
		if ($relations == 0) {
			return;
		}
		$relations += $this->getPersonalRelation($raceID);
		$this->setRelations($relations, $raceID);
	}

	/**
	 * Decrease personal relations.
	 */
	public function decreaseRelations($relations, $raceID) {
		if ($relations < 0) {
			throw new Exception('Trying to decrease negative relations.');
		}
		if ($relations == 0) {
			return;
		}
		$relations = $this->getPersonalRelation($raceID) - $relations;
		$this->setRelations($relations, $raceID);
	}

	/**
	 * Set personal relations.
	 */
	public function setRelations($relations, $raceID) {
		$this->getRelations();
		if ($this->personalRelations[$raceID] == $relations) {
			return;
		}
		if ($relations < MIN_RELATIONS) {
			$relations = MIN_RELATIONS;
		}
		$relationsDiff = IRound($relations - $this->personalRelations[$raceID]);
		$this->personalRelations[$raceID] = $relations;
		$this->relations[$raceID] += $relationsDiff;
		$this->db->query('REPLACE INTO player_has_relation (account_id,game_id,race_id,relation) values (' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeNumber($raceID) . ',' . $this->db->escapeNumber($this->personalRelations[$raceID]) . ')');
	}

	/**
	 * Set any starting personal relations bonuses or penalties.
	 */
	public function giveStartingRelations() {
		if ($this->getRaceID() === RACE_ALSKANT) {
			// Give Alskants 250 personal relations to start.
			foreach (Globals::getRaces() as $raceID => $raceInfo) {
				$this->setRelations(250, $raceID);
			}
		}
	}

	public function getLastNewsUpdate() {
		return $this->lastNewsUpdate;
	}

	private function setLastNewsUpdate($time) {
		if ($this->lastNewsUpdate == $time) {
			return;
		}
		$this->lastNewsUpdate = $time;
		$this->hasChanged = true;
	}

	public function updateLastNewsUpdate() {
		$this->setLastNewsUpdate(TIME);
	}

	public function getLastPort() {
		return $this->lastPort;
	}

	public function setLastPort($lastPort) {
		if ($this->lastPort == $lastPort) {
			return;
		}
		$this->lastPort = $lastPort;
		$this->hasChanged = true;
	}

	public function getPlottedCourse() {
		if (!isset($this->plottedCourse)) {
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
		if ($this->plottedCourse->getTotalSectors() > 0) {
			$this->db->query('REPLACE INTO player_plotted_course
				(account_id, game_id, course)
				VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeBinary(serialize($this->plottedCourse)) . ')');
		} elseif ($hadPlottedCourse) {
			$this->deletePlottedCourse();
		}
	}

	public function hasPlottedCourse() {
		return $this->getPlottedCourse() !== false;
	}

	public function isPartOfCourse($sectorOrSectorID) {
		if (!$this->hasPlottedCourse()) {
			return false;
		}
		if ($sectorOrSectorID instanceof SmrSector) {
			$sectorID = $sectorOrSectorID->getSectorID();
		} else {
			$sectorID = $sectorOrSectorID;
		}
		return $this->getPlottedCourse()->isInPath($sectorID);
	}

	public function deletePlottedCourse() {
		$this->plottedCourse = false;
		$this->db->query('DELETE FROM player_plotted_course WHERE ' . $this->SQL . ' LIMIT 1');
	}

	// Computes the turn cost and max misjump between current and target sector
	public function getJumpInfo(SmrSector $targetSector) {
		$path = Plotter::findDistanceToX($targetSector, $this->getSector(), true);
		if ($path === false) {
			create_error('Unable to plot from ' . $this->getSectorID() . ' to ' . $targetSector->getSectorID() . '.');
		}
		$distance = $path->getRelativeDistance();

		$turnCost = max(TURNS_JUMP_MINIMUM, IRound($distance * TURNS_PER_JUMP_DISTANCE));
		$maxMisjump = max(0, IRound(($distance - $turnCost) * MISJUMP_DISTANCE_DIFF_FACTOR / (1 + $this->getLevelID() * MISJUMP_LEVEL_FACTOR)));
		return array('turn_cost' => $turnCost, 'max_misjump' => $maxMisjump);
	}

	public function __sleep() {
		return array('accountID', 'gameID', 'sectorID', 'alignment', 'playerID', 'playerName');
	}

	public function &getStoredDestinations() {
		if (!isset($this->storedDestinations)) {
			$this->storedDestinations = array();
			$this->db->query('SELECT * FROM player_stored_sector WHERE ' . $this->SQL);
			while ($this->db->nextRecord()) {
				$this->storedDestinations[] = array(
					'Label' => $this->db->getField('label'),
					'SectorID' => $this->db->getInt('sector_id'),
					'OffsetTop' => $this->db->getInt('offset_top'),
					'OffsetLeft' => $this->db->getInt('offset_left')
				);
			}
		}
		return $this->storedDestinations;
	}

	public function moveDestinationButton($sectorID, $offsetTop, $offsetLeft) {

		if (!is_numeric($offsetLeft) || !is_numeric($offsetTop)) {
			create_error('The position of the saved sector must be numeric!.');
		}
		$offsetTop = round($offsetTop);
		$offsetLeft = round($offsetLeft);

		if ($offsetLeft < 0 || $offsetLeft > 500 || $offsetTop < 0 || $offsetTop > 300) {
			create_error('The saved sector must be in the box!');
		}

		$storedDestinations =& $this->getStoredDestinations();
		foreach ($storedDestinations as &$sd) {
			if ($sd['SectorID'] == $sectorID) {
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

	public function addDestinationButton($sectorID, $label) {

		if (!is_numeric($sectorID) || !SmrSector::sectorExists($this->getGameID(), $sectorID)) {
			create_error('You want to add a non-existent sector?');
		}

		// sector already stored ?
		foreach ($this->getStoredDestinations() as $sd) {
			if ($sd['SectorID'] == $sectorID) {
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

	public function deleteDestinationButton($sectorID) {
		if (!is_numeric($sectorID) || $sectorID < 1) {
			create_error('You want to remove a non-existent sector?');
		}

		foreach ($this->getStoredDestinations() as $key => $sd) {
			if ($sd['SectorID'] == $sectorID) {
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

	public function getTickers() {
		if (!isset($this->tickers)) {
			$this->tickers = array();
			//get ticker info
			$this->db->query('SELECT type,time,expires,recent FROM player_has_ticker WHERE ' . $this->SQL . ' AND expires > ' . $this->db->escapeNumber(TIME));
			while ($this->db->nextRecord()) {
				$this->tickers[$this->db->getField('type')] = [
					'Type' => $this->db->getField('type'),
					'Time' => $this->db->getInt('time'),
					'Expires' => $this->db->getInt('expires'),
					'Recent' => $this->db->getField('recent'),
				];
			}
		}
		return $this->tickers;
	}

	public function hasTickers() {
		return count($this->getTickers()) > 0;
	}

	public function getTicker($tickerType) {
		$tickers = $this->getTickers();
		if (isset($tickers[$tickerType])) {
			return $tickers[$tickerType];
		}
		return false;
	}

	public function hasTicker($tickerType) {
		return $this->getTicker($tickerType) !== false;
	}

	public function &shootPlayer(AbstractSmrPlayer $targetPlayer) {
		return $this->getShip()->shootPlayer($targetPlayer);
	}

	public function &shootForces(SmrForce $forces) {
		return $this->getShip()->shootForces($forces);
	}

	public function &shootPort(SmrPort $port) {
		return $this->getShip()->shootPort($port);
	}

	public function &shootPlanet(SmrPlanet $planet, $delayed) {
		return $this->getShip()->shootPlanet($planet, $delayed);
	}

	public function &shootPlayers(array $targetPlayers) {
		return $this->getShip()->shootPlayers($targetPlayers);
	}

	public function getMilitaryPayment() {
		return $this->militaryPayment;
	}

	public function hasMilitaryPayment() {
		return $this->getMilitaryPayment() > 0;
	}

	public function setMilitaryPayment($amount) {
		if ($this->militaryPayment == $amount) {
			return;
		}
		$this->militaryPayment = $amount;
		$this->hasChanged = true;
	}

	public function increaseMilitaryPayment($amount) {
		if ($amount < 0) {
			throw new Exception('Trying to increase negative military payment.');
		}
		$this->setMilitaryPayment($this->getMilitaryPayment() + $amount);
	}

	public function decreaseMilitaryPayment($amount) {
		if ($amount < 0) {
			throw new Exception('Trying to decrease negative military payment.');
		}
		$this->setMilitaryPayment($this->getMilitaryPayment() - $amount);
	}

	protected function getBountiesData() {
		if (!isset($this->bounties)) {
			$this->bounties = array();
			$this->db->query('SELECT * FROM bounty WHERE ' . $this->SQL);
			while ($this->db->nextRecord()) {
				$this->bounties[$this->db->getInt('bounty_id')] = array(
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

	public function getBounties() : array {
		$this->getBountiesData();
		return $this->bounties;
	}

	public function hasBounties() : bool {
		return count($this->getBounties()) > 0;
	}

	protected function getBounty(int $bountyID) : array {
		if (!$this->hasBounty($bountyID)) {
			throw new Exception('BountyID does not exist: ' . $bountyID);
		}
		return $this->bounties[$bountyID];
	}

	public function hasBounty(int $bountyID) : bool {
		$bounties = $this->getBounties();
		return isset($bounties[$bountyID]);
	}

	protected function getBountyAmount(int $bountyID) : int {
		$bounty = $this->getBounty($bountyID);
		return $bounty['Amount'];
	}

	protected function createBounty(string $type) : array {
		$bounty = array('Amount' => 0,
						'SmrCredits' => 0,
						'Type' => $type,
						'Claimer' => 0,
						'Time' => TIME,
						'ID' => $this->getNextBountyID(),
						'New' => true);
		$this->setBounty($bounty);
		return $bounty;
	}

	protected function getNextBountyID() : int {
		$keys = array_keys($this->getBounties());
		if (count($keys) > 0) {
			return max($keys) + 1;
		} else {
			return 0;
		}
	}

	protected function setBounty(array $bounty) : void {
		$this->bounties[$bounty['ID']] = $bounty;
		$this->hasBountyChanged[$bounty['ID']] = true;
	}

	protected function setBountyAmount(int $bountyID, int $amount) : void {
		$bounty = $this->getBounty($bountyID);
		$bounty['Amount'] = $amount;
		$this->setBounty($bounty);
	}

	public function getCurrentBounty(string $type) : array {
		$bounties = $this->getBounties();
		foreach ($bounties as $bounty) {
			if ($bounty['Claimer'] == 0 && $bounty['Type'] == $type) {
				return $bounty;
			}
		}
		return $this->createBounty($type);
	}

	public function hasCurrentBounty(string $type) : bool {
		$bounties = $this->getBounties();
		foreach ($bounties as $bounty) {
			if ($bounty['Claimer'] == 0 && $bounty['Type'] == $type) {
				return true;
			}
		}
		return false;
	}

	protected function getCurrentBountyAmount(string $type) : int {
		$bounty = $this->getCurrentBounty($type);
		return $bounty['Amount'];
	}

	protected function setCurrentBountyAmount(string $type, int $amount) : void {
		$bounty = $this->getCurrentBounty($type);
		if ($bounty['Amount'] == $amount) {
			return;
		}
		$bounty['Amount'] = $amount;
		$this->setBounty($bounty);
	}

	public function increaseCurrentBountyAmount(string $type, int $amount) : void {
		if ($amount < 0) {
			throw new Exception('Trying to increase negative current bounty.');
		}
		$this->setCurrentBountyAmount($type, $this->getCurrentBountyAmount($type) + $amount);
	}

	public function decreaseCurrentBountyAmount(string $type, int $amount) : void {
		if ($amount < 0) {
			throw new Exception('Trying to decrease negative current bounty.');
		}
		$this->setCurrentBountyAmount($type, $this->getCurrentBountyAmount($type) - $amount);
	}

	protected function getCurrentBountySmrCredits(string $type) : int {
		$bounty = $this->getCurrentBounty($type);
		return $bounty['SmrCredits'];
	}

	protected function setCurrentBountySmrCredits(string $type, int $credits) : void {
		$bounty = $this->getCurrentBounty($type);
		if ($bounty['SmrCredits'] == $credits) {
			return;
		}
		$bounty['SmrCredits'] = $credits;
		$this->setBounty($bounty);
	}

	public function increaseCurrentBountySmrCredits(string $type, int $credits) : void {
		if ($credits < 0) {
			throw new Exception('Trying to increase negative current bounty.');
		}
		$this->setCurrentBountySmrCredits($type, $this->getCurrentBountySmrCredits($type) + $credits);
	}

	public function decreaseCurrentBountySmrCredits(string $type, int $credits) : void {
		if ($credits < 0) {
			throw new Exception('Trying to decrease negative current bounty.');
		}
		$this->setCurrentBountySmrCredits($type, $this->getCurrentBountySmrCredits($type) - $credits);
	}

	public function setBountiesClaimable(AbstractSmrPlayer $claimer) : void {
		foreach ($this->getBounties() as $bounty) {
			if ($bounty['Claimer'] == 0) {
				$bounty['Claimer'] = $claimer->getAccountID();
				$this->setBounty($bounty);
			}
		}
	}

	protected function getHOFData() {
		if (!isset($this->HOF)) {
			//Get Player HOF
			$this->db->query('SELECT type,amount FROM player_hof WHERE ' . $this->SQL);
			$this->HOF = array();
			while ($this->db->nextRecord()) {
				$hof =& $this->HOF;
				$typeList = explode(':', $this->db->getField('type'));
				foreach ($typeList as $type) {
					if (!isset($hof[$type])) {
						$hof[$type] = array();
					}
					$hof =& $hof[$type];
				}
				$hof = $this->db->getFloat('amount');
			}
			self::getHOFVis();
		}
	}

	public static function getHOFVis() {
		if (!isset(self::$HOFVis)) {
			//Get Player HOF Vis
			$db = new SmrMySqlDatabase();
			$db->query('SELECT type,visibility FROM hof_visibility');
			self::$HOFVis = array();
			while ($db->nextRecord()) {
				self::$HOFVis[$db->getField('type')] = $db->getField('visibility');
			}
		}
	}

	public function getHOF(array $typeList = null) {
		$this->getHOFData();
		if ($typeList == null) {
			return $this->HOF;
		}
		$hof = $this->HOF;
		foreach ($typeList as $type) {
			if (!isset($hof[$type])) {
				return 0;
			}
			$hof = $hof[$type];
		}
		return $hof;
	}

	public function increaseHOF($amount, array $typeList, $visibility) {
		if ($amount < 0) {
			throw new Exception('Trying to increase negative HOF: ' . implode(':', $typeList));
		}
		if ($amount == 0) {
			return;
		}
		$this->setHOF($this->getHOF($typeList) + $amount, $typeList, $visibility);
	}

	public function decreaseHOF($amount, array $typeList, $visibility) {
		if ($amount < 0) {
			throw new Exception('Trying to decrease negative HOF: ' . implode(':', $typeList));
		}
		if ($amount == 0) {
			return;
		}
		$this->setHOF($this->getHOF($typeList) - $amount, $typeList, $visibility);
	}

	public function setHOF($amount, array $typeList, $visibility) {
		if (is_array($this->getHOF($typeList))) {
			throw new Exception('Trying to overwrite a HOF type: ' . implode(':', $typeList));
		}
		if ($this->isNPC()) {
			// Don't store HOF for NPCs.
			return;
		}
		if ($this->getHOF($typeList) == $amount) {
			return;
		}
		if ($amount < 0) {
			$amount = 0;
		}
		$this->getHOF();

		$hofType = implode(':', $typeList);
		if (!isset(self::$HOFVis[$hofType])) {
			self::$hasHOFVisChanged[$hofType] = self::HOF_NEW;
		} elseif (self::$HOFVis[$hofType] != $visibility) {
			self::$hasHOFVisChanged[$hofType] = self::HOF_CHANGED;
		}
		self::$HOFVis[$hofType] = $visibility;

		$hof =& $this->HOF;
		$hofChanged =& $this->hasHOFChanged;
		$new = false;
		foreach ($typeList as $type) {
			if (!isset($hofChanged[$type])) {
				$hofChanged[$type] = array();
			}
			if (!isset($hof[$type])) {
				$hof[$type] = array();
				$new = true;
			}
			$hof =& $hof[$type];
			$hofChanged =& $hofChanged[$type];
		}
		if ($hofChanged == null) {
			$hofChanged = self::HOF_CHANGED;
			if ($new) {
				$hofChanged = self::HOF_NEW;
			}
		}
		$hof = $amount;
	}

	public function getExperienceRank() {
		return $this->computeRanking('experience', $this->getExperience());
	}

	public function getKillsRank() {
		return $this->computeRanking('kills', $this->getKills());
	}

	public function getDeathsRank() {
		return $this->computeRanking('deaths', $this->getDeaths());
	}

	public function getAssistsRank() {
		return $this->computeRanking('assists', $this->getAssists());
	}

	private function computeRanking($dbField, $playerAmount) {
		$this->db->query('SELECT count(*) FROM player
			WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()) . '
			AND (
				'.$dbField . ' > ' . $this->db->escapeNumber($playerAmount) . '
				OR (
					'.$dbField . ' = ' . $this->db->escapeNumber($playerAmount) . '
					AND player_name <= ' . $this->db->escapeString($this->getPlayerName()) . '
				)
			)');
		$this->db->nextRecord();
		$rank = $this->db->getInt('count(*)');
		return $rank;
	}

	public function killPlayer($sectorID) {
		$sector = SmrSector::getSector($this->getGameID(), $sectorID);
		//msg taken care of in trader_att_proc.php
		// forget plotted course
		$this->deletePlottedCourse();

		$sector->diedHere($this);

		// if we are in an alliance we increase their deaths
		if ($this->hasAlliance()) {
			$this->db->query('UPDATE alliance SET alliance_deaths = alliance_deaths + 1
							WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()) . ' AND alliance_id = ' . $this->db->escapeNumber($this->getAllianceID()) . ' LIMIT 1');
		}

		// record death stat
		$this->increaseHOF(1, array('Dying', 'Deaths'), HOF_PUBLIC);
		//record cost of ship lost
		$this->increaseHOF($this->getShip()->getCost(), array('Dying', 'Money', 'Cost Of Ships Lost'), HOF_PUBLIC);
		// reset turns since last death
		$this->setHOF(0, array('Movement', 'Turns Used', 'Since Last Death'), HOF_ALLIANCE);

		// 1/4 of ship value -> insurance
		$newCredits = IRound($this->getShip()->getCost() / 4);
		if ($newCredits < 100000) {
			$newCredits = 100000;
		}
		$this->setCredits($newCredits);

		$this->setSectorID($this->getHome());
		$this->increaseDeaths(1);
		$this->setLandedOnPlanet(false);
		$this->setDead(true);
		$this->setNewbieWarning(true);
		$this->getShip()->getPod($this->hasNewbieStatus());
		$this->setNewbieTurns(100);
	}

	public function &killPlayerByPlayer(AbstractSmrPlayer $killer) {
		$return = array();
		$msg = $this->getBBLink();

		if ($this->hasCustomShipName()) {
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$msg .= ' flying <span class="yellow">' . $named_ship . '</span>';
		}
		$msg .= ' was destroyed by ' . $killer->getBBLink();
		if ($killer->hasCustomShipName()) {
			$named_ship = strip_tags($killer->getCustomShipName(), '<font><span><img>');
			$msg .= ' flying <span class="yellow">' . $named_ship . '</span>';
		}
		$msg .= ' in Sector&nbsp;' . Globals::getSectorBBLink($this->getSectorID());
		$this->getSector()->increaseBattles(1);
		$this->db->query('INSERT INTO news (game_id,time,news_message,type,killer_id,killer_alliance,dead_id,dead_alliance) VALUES (' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeNumber(TIME) . ',' . $this->db->escapeString($msg, true) . ',\'regular\',' . $this->db->escapeNumber($killer->getAccountID()) . ',' . $this->db->escapeNumber($killer->getAllianceID()) . ',' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ')');

		self::sendMessageFromFedClerk($this->getGameID(), $this->getAccountID(), 'You were <span class="red">DESTROYED</span> by ' . $killer->getBBLink() . ' in sector ' . Globals::getSectorBBLink($this->getSectorID()));
		self::sendMessageFromFedClerk($this->getGameID(), $killer->getAccountID(), 'You <span class="red">DESTROYED</span>&nbsp;' . $this->getBBLink() . ' in sector ' . Globals::getSectorBBLink($this->getSectorID()));

		// Dead player loses between 5% and 25% experience
		$expLossPercentage = 0.15 + 0.10 * ($this->getLevelID() - $killer->getLevelID()) / $this->getMaxLevel();
		$return['DeadExp'] = max(0, IFloor($this->getExperience() * $expLossPercentage));
		$this->decreaseExperience($return['DeadExp']);

		// Killer gains 50% of the lost exp
		$return['KillerExp'] = max(0, ICeil(0.5 * $return['DeadExp']));
		$killer->increaseExperience($return['KillerExp']);

		$return['KillerCredits'] = $this->getCredits();
		$killer->increaseCredits($return['KillerCredits']);

		// The killer may change alignment
		$relations = Globals::getRaceRelations($this->getGameID(), $this->getRaceID());
		$relation = $relations[$killer->getRaceID()];

		$alignChangePerRelation = 0.1;
		if ($relation >= RELATIONS_PEACE || $relation <= RELATIONS_WAR) {
			$alignChangePerRelation = 0.04;
		}

		$return['KillerAlign'] = -$relation * $alignChangePerRelation; //Lose relations when killing a peaceful race
		if ($return['KillerAlign'] > 0) {
			$killer->increaseAlignment($return['KillerAlign']);
		} else {
			$killer->decreaseAlignment(-$return['KillerAlign']);
		}
		// War setting gives them military pay
		if ($relation <= RELATIONS_WAR) {
			$killer->increaseMilitaryPayment(-IFloor($relation * 100 * pow($return['KillerExp'] / 2, 0.25)));
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
			$bounty = IFloor(DEFEND_PORT_BOUNTY_PER_LEVEL * $this->getLevelID());
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
		if ($alignmentDiff >= 200) {
			// If the podded players alignment makes them deputy or member then set bounty
			if ($this->getAlignment() >= 100) {
				$return['BountyGained']['Type'] = 'HQ';
			} elseif ($this->getAlignment() <= 100) {
				$return['BountyGained']['Type'] = 'UG';
			}

			if ($return['BountyGained']['Type'] != 'None') {
				$return['BountyGained']['Amount'] = IFloor(pow($alignmentDiff, 2.56));
				$killer->increaseCurrentBountyAmount($return['BountyGained']['Type'], $return['BountyGained']['Amount']);
			}
		}

		if ($this->isNPC()) {
			$killer->increaseHOF($return['KillerExp'], array('Killing', 'NPC', 'Experience', 'Gained'), HOF_PUBLIC);
			$killer->increaseHOF($this->getExperience(), array('Killing', 'NPC', 'Experience', 'Of Traders Killed'), HOF_PUBLIC);

			$killer->increaseHOF($return['DeadExp'], array('Killing', 'Experience', 'Lost By NPCs Killed'), HOF_PUBLIC);

			$killer->increaseHOF($return['KillerCredits'], array('Killing', 'NPC', 'Money', 'Lost By Traders Killed'), HOF_PUBLIC);
			$killer->increaseHOF($return['KillerCredits'], array('Killing', 'NPC', 'Money', 'Gain'), HOF_PUBLIC);
			$killer->increaseHOF($this->getShip()->getCost(), array('Killing', 'NPC', 'Money', 'Cost Of Ships Killed'), HOF_PUBLIC);

			if ($return['KillerAlign'] > 0) {
				$killer->increaseHOF($return['KillerAlign'], array('Killing', 'NPC', 'Alignment', 'Gain'), HOF_PUBLIC);
			} else {
				$killer->increaseHOF(-$return['KillerAlign'], array('Killing', 'NPC', 'Alignment', 'Loss'), HOF_PUBLIC);
			}

			$killer->increaseHOF($return['BountyGained']['Amount'], array('Killing', 'NPC', 'Money', 'Bounty Gained'), HOF_PUBLIC);

			$killer->increaseHOF(1, array('Killing', 'NPC Kills'), HOF_PUBLIC);
		} else {
			$killer->increaseHOF($return['KillerExp'], array('Killing', 'Experience', 'Gained'), HOF_PUBLIC);
			$killer->increaseHOF($this->getExperience(), array('Killing', 'Experience', 'Of Traders Killed'), HOF_PUBLIC);

			$killer->increaseHOF($return['DeadExp'], array('Killing', 'Experience', 'Lost By Traders Killed'), HOF_PUBLIC);

			$killer->increaseHOF($return['KillerCredits'], array('Killing', 'Money', 'Lost By Traders Killed'), HOF_PUBLIC);
			$killer->increaseHOF($return['KillerCredits'], array('Killing', 'Money', 'Gain'), HOF_PUBLIC);
			$killer->increaseHOF($this->getShip()->getCost(), array('Killing', 'Money', 'Cost Of Ships Killed'), HOF_PUBLIC);

			if ($return['KillerAlign'] > 0) {
				$killer->increaseHOF($return['KillerAlign'], array('Killing', 'Alignment', 'Gain'), HOF_PUBLIC);
			} else {
				$killer->increaseHOF(-$return['KillerAlign'], array('Killing', 'Alignment', 'Loss'), HOF_PUBLIC);
			}

			$killer->increaseHOF($return['BountyGained']['Amount'], array('Killing', 'Money', 'Bounty Gained'), HOF_PUBLIC);

			if ($this->getShip()->getAttackRatingWithMaxCDs() <= MAX_ATTACK_RATING_NEWBIE && $this->hasNewbieStatus() && !$killer->hasNewbieStatus()) { //Newbie kill
				$killer->increaseHOF(1, array('Killing', 'Newbie Kills'), HOF_PUBLIC);
			} else {
				$killer->increaseKills(1);
				$killer->increaseHOF(1, array('Killing', 'Kills'), HOF_PUBLIC);

				if ($killer->hasAlliance()) {
					$this->db->query('UPDATE alliance SET alliance_kills=alliance_kills+1 WHERE alliance_id=' . $this->db->escapeNumber($killer->getAllianceID()) . ' AND game_id=' . $this->db->escapeNumber($killer->getGameID()) . ' LIMIT 1');
				}

				// alliance vs. alliance stats
				$this->incrementAllianceVsDeaths($killer->getAllianceID());
			}
		}

		$this->increaseHOF($return['BountyGained']['Amount'], array('Dying', 'Players', 'Money', 'Bounty Gained By Killer'), HOF_PUBLIC);
		$this->increaseHOF($return['KillerExp'], array('Dying', 'Players', 'Experience', 'Gained By Killer'), HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'], array('Dying', 'Experience', 'Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'], array('Dying', 'Players', 'Experience', 'Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['KillerCredits'], array('Dying', 'Players', 'Money Lost'), HOF_PUBLIC);
		$this->increaseHOF($this->getShip()->getCost(), array('Dying', 'Players', 'Money', 'Cost Of Ships Lost'), HOF_PUBLIC);
		$this->increaseHOF(1, array('Dying', 'Players', 'Deaths'), HOF_PUBLIC);

		$this->killPlayer($this->getSectorID());
		return $return;
	}

	public function &killPlayerByForces(SmrForce $forces) {
		$return = array();
		$owner = $forces->getOwner();
		// send a message to the person who died
		self::sendMessageFromFedClerk($this->getGameID(), $owner->getAccountID(), 'Your forces <span class="red">DESTROYED </span>' . $this->getBBLink() . ' in sector ' . Globals::getSectorBBLink($forces->getSectorID()));
		self::sendMessageFromFedClerk($this->getGameID(), $this->getAccountID(), 'You were <span class="red">DESTROYED</span> by ' . $owner->getBBLink() . '\'s forces in sector ' . Globals::getSectorBBLink($this->getSectorID()));

		$news_message = $this->getBBLink();
		if ($this->hasCustomShipName()) {
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$news_message .= ' flying <span class="yellow">' . $named_ship . '</span>';
		}
		$news_message .= ' was destroyed by ' . $owner->getBBLink() . '\'s forces in sector ' . Globals::getSectorBBLink($forces->getSectorID());
		// insert the news entry
		$this->db->query('INSERT INTO news (game_id, time, news_message,killer_id,killer_alliance,dead_id,dead_alliance)
						VALUES(' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber(TIME) . ', ' . $this->db->escapeString($news_message) . ',' . $this->db->escapeNumber($owner->getAccountID()) . ',' . $this->db->escapeNumber($owner->getAllianceID()) . ',' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ')');

		// Player loses 15% experience
		$expLossPercentage = .15;
		$return['DeadExp'] = IFloor($this->getExperience() * $expLossPercentage);
		$this->decreaseExperience($return['DeadExp']);

		$return['LostCredits'] = $this->getCredits();

		// alliance vs. alliance stats
		$this->incrementAllianceVsDeaths(ALLIANCE_VS_FORCES);
		$owner->incrementAllianceVsKills(ALLIANCE_VS_FORCES);

		$this->increaseHOF($return['DeadExp'], array('Dying', 'Experience', 'Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'], array('Dying', 'Forces', 'Experience Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['LostCredits'], array('Dying', 'Forces', 'Money Lost'), HOF_PUBLIC);
		$this->increaseHOF($this->getShip()->getCost(), array('Dying', 'Forces', 'Cost Of Ships Lost'), HOF_PUBLIC);
		$this->increaseHOF(1, array('Dying', 'Forces', 'Deaths'), HOF_PUBLIC);

		$this->killPlayer($forces->getSectorID());
		return $return;
	}

	public function &killPlayerByPort(SmrPort $port) {
		$return = array();
		// send a message to the person who died
		self::sendMessageFromFedClerk($this->getGameID(), $this->getAccountID(), 'You were <span class="red">DESTROYED</span> by the defenses of ' . $port->getDisplayName());

		$news_message = $this->getBBLink();
		if ($this->hasCustomShipName()) {
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$news_message .= ' flying <span class="yellow">' . $named_ship . '</span>';
		}
		$news_message .= ' was destroyed while invading ' . $port->getDisplayName() . '.';
		// insert the news entry
		$this->db->query('INSERT INTO news (game_id, time, news_message,killer_id,dead_id,dead_alliance)
						VALUES(' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber(TIME) . ', ' . $this->db->escapeString($news_message) . ',' . $this->db->escapeNumber(ACCOUNT_ID_PORT) . ',' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ')');

		// Player loses between 15% and 20% experience
		$expLossPercentage = .20 - .05 * ($port->getLevel() - 1) / ($port->getMaxLevel() - 1);
		$return['DeadExp'] = max(0, IFloor($this->getExperience() * $expLossPercentage));
		$this->decreaseExperience($return['DeadExp']);

		$return['LostCredits'] = $this->getCredits();

		// alliance vs. alliance stats
		$this->incrementAllianceVsDeaths(ALLIANCE_VS_PORTS);

		$this->increaseHOF($return['DeadExp'], array('Dying', 'Experience', 'Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'], array('Dying', 'Ports', 'Experience Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['LostCredits'], array('Dying', 'Ports', 'Money Lost'), HOF_PUBLIC);
		$this->increaseHOF($this->getShip()->getCost(), array('Dying', 'Ports', 'Cost Of Ships Lost'), HOF_PUBLIC);
		$this->increaseHOF(1, array('Dying', 'Ports', 'Deaths'), HOF_PUBLIC);

		$this->killPlayer($port->getSectorID());
		return $return;
	}

	public function &killPlayerByPlanet(SmrPlanet $planet) {
		$return = array();
		// send a message to the person who died
		$planetOwner = $planet->getOwner();
		self::sendMessageFromFedClerk($this->getGameID(), $planetOwner->getAccountID(), 'Your planet <span class="red">DESTROYED</span>&nbsp;' . $this->getBBLink() . ' in sector ' . Globals::getSectorBBLink($planet->getSectorID()));
		self::sendMessageFromFedClerk($this->getGameID(), $this->getAccountID(), 'You were <span class="red">DESTROYED</span> by the planetary defenses of ' . $planet->getCombatName());

		$news_message = $this->getBBLink();
		if ($this->hasCustomShipName()) {
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$news_message .= ' flying <span class="yellow">' . $named_ship . '</span>';
		}
		$news_message .= ' was destroyed by ' . $planet->getCombatName() . '\'s planetary defenses in sector ' . Globals::getSectorBBLink($planet->getSectorID()) . '.';
		// insert the news entry
		$this->db->query('INSERT INTO news (game_id, time, news_message,killer_id,killer_alliance,dead_id,dead_alliance)
						VALUES(' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber(TIME) . ', ' . $this->db->escapeString($news_message) . ',' . $this->db->escapeNumber($planetOwner->getAccountID()) . ',' . $this->db->escapeNumber($planetOwner->getAllianceID()) . ',' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ')');

		// Player loses between 15% and 20% experience
		$expLossPercentage = .20 - .05 * $planet->getLevel() / $planet->getMaxLevel();
		$return['DeadExp'] = max(0, IFloor($this->getExperience() * $expLossPercentage));
		$this->decreaseExperience($return['DeadExp']);

		$return['LostCredits'] = $this->getCredits();

		// alliance vs. alliance stats
		$this->incrementAllianceVsDeaths(ALLIANCE_VS_PLANETS);
		$planetOwner->incrementAllianceVsKills(ALLIANCE_VS_PLANETS);

		$this->increaseHOF($return['DeadExp'], array('Dying', 'Experience', 'Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'], array('Dying', 'Planets', 'Experience Lost'), HOF_PUBLIC);
		$this->increaseHOF($return['LostCredits'], array('Dying', 'Planets', 'Money Lost'), HOF_PUBLIC);
		$this->increaseHOF($this->getShip()->getCost(), array('Dying', 'Planets', 'Cost Of Ships Lost'), HOF_PUBLIC);
		$this->increaseHOF(1, array('Dying', 'Planets', 'Deaths'), HOF_PUBLIC);

		$this->killPlayer($planet->getSectorID());
		return $return;
	}

	public function incrementAllianceVsKills($otherID) {
		$values = [$this->getGameID(), $this->getAllianceID(), $otherID, 1];
		$this->db->query('INSERT INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) VALUES (' . $this->db->escapeArray($values) . ') ON DUPLICATE KEY UPDATE kills = kills + 1');
	}

	public function incrementAllianceVsDeaths($otherID) {
		$values = [$this->getGameID(), $otherID, $this->getAllianceID(), 1];
		$this->db->query('INSERT INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) VALUES (' . $this->db->escapeArray($values) . ') ON DUPLICATE KEY UPDATE kills = kills + 1');
	}

	public function getTurnsLevel() {
		if (!$this->hasTurns()) {
			return 'NONE';
		}
		if ($this->getTurns() <= 25) {
			return 'LOW';
		}
		if ($this->getTurns() <= 75) {
			return 'MEDIUM';
		}
		return 'HIGH';
	}

	/**
	 * Returns the CSS class color to use when displaying the player's turns
	 */
	public function getTurnsColor() {
		switch ($this->getTurnsLevel()) {
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

	public function getTurns() {
		return $this->turns;
	}

	public function hasTurns() {
		return $this->turns > 0;
	}

	public function getMaxTurns() {
		return $this->getGame()->getMaxTurns();
	}

	public function setTurns($turns) {
		if ($this->turns == $turns) {
			return;
		}
		// Make sure turns are in range [0, MaxTurns]
		$this->turns = max(0, min($turns, $this->getMaxTurns()));
		$this->hasChanged = true;
	}

	public function takeTurns($take, $takeNewbie = 0) {
		if ($take < 0 || $takeNewbie < 0) {
			throw new Exception('Trying to take negative turns.');
		}
		$take = ICeil($take);
		// Only take up to as many newbie turns as we have remaining
		$takeNewbie = min($this->getNewbieTurns(), $takeNewbie);

		$this->setTurns($this->getTurns() - $take);
		$this->setNewbieTurns($this->getNewbieTurns() - $takeNewbie);
		$this->increaseHOF($take, array('Movement', 'Turns Used', 'Since Last Death'), HOF_ALLIANCE);
		$this->increaseHOF($take, array('Movement', 'Turns Used', 'Total'), HOF_ALLIANCE);
		$this->increaseHOF($takeNewbie, array('Movement', 'Turns Used', 'Newbie'), HOF_ALLIANCE);

		// Player has taken an action
		$this->setLastActive(TIME);
		$this->updateLastCPLAction();
	}

	public function giveTurns(int $give, $giveNewbie = 0) {
		if ($give < 0 || $giveNewbie < 0) {
			throw new Exception('Trying to give negative turns.');
		}
		$this->setTurns($this->getTurns() + $give);
		$this->setNewbieTurns($this->getNewbieTurns() + $giveNewbie);
	}

	/**
	 * Calculate the time in seconds between the given time and when the
	 * player will be at max turns.
	 */
	public function getTimeUntilMaxTurns($time, $forceUpdate = false) {
		$timeDiff = $time - $this->getLastTurnUpdate();
		$turnsDiff = $this->getMaxTurns() - $this->getTurns();
		$ship = $this->getShip($forceUpdate);
		$maxTurnsTime = ICeil(($turnsDiff * 3600 / $ship->getRealSpeed())) - $timeDiff;
		// If already at max turns, return 0
		return max(0, $maxTurnsTime);
	}

	/**
	 * Grant the player their starting turns.
	 */
	public function giveStartingTurns() {
		$startTurns = IFloor($this->getShip()->getRealSpeed() * $this->getGame()->getStartTurnHours());
		$this->giveTurns($startTurns);
		$this->setLastTurnUpdate($this->getGame()->getStartTime());
	}

	// Turns only update when player is active.
	// Calculate turns gained between given time and the last turn update
	public function getTurnsGained($time, $forceUpdate = false) : int {
		$timeDiff = $time - $this->getLastTurnUpdate();
		$ship = $this->getShip($forceUpdate);
		$extraTurns = IFloor($timeDiff * $ship->getRealSpeed() / 3600);
		return $extraTurns;
	}

	public function updateTurns() {
		// is account validated?
		if (!$this->getAccount()->isValidated()) {
			return;
		}

		// how many turns would he get right now?
		$extraTurns = $this->getTurnsGained(TIME);

		// do we have at least one turn to give?
		if ($extraTurns > 0) {
			// recalc the time to avoid rounding errors
			$newLastTurnUpdate = $this->getLastTurnUpdate() + ICeil($extraTurns * 3600 / $this->getShip()->getRealSpeed());
			$this->setLastTurnUpdate($newLastTurnUpdate);
			$this->giveTurns($extraTurns);
		}
	}

	public function getLastTurnUpdate() {
		return $this->lastTurnUpdate;
	}

	public function setLastTurnUpdate($time) {
		if ($this->lastTurnUpdate == $time) {
			return;
		}
		$this->lastTurnUpdate = $time;
		$this->hasChanged = true;
	}

	public function getLastActive() {
		return $this->lastActive;
	}

	public function setLastActive($lastActive) {
		if ($this->lastActive == $lastActive) {
			return;
		}
		$this->lastActive = $lastActive;
		$this->hasChanged = true;
	}

	public function getLastCPLAction() {
		return $this->lastCPLAction;
	}

	public function setLastCPLAction($time) {
		if ($this->lastCPLAction == $time) {
			return;
		}
		$this->lastCPLAction = $time;
		$this->hasChanged = true;
	}

	public function updateLastCPLAction() {
		$this->setLastCPLAction(TIME);
	}

	public function setNewbieWarning($bool) {
		if ($this->newbieWarning == $bool) {
			return;
		}
		$this->newbieWarning = $bool;
		$this->hasChanged = true;
	}

	public function getNewbieWarning() {
		return $this->newbieWarning;
	}

	public function isDisplayMissions() {
		return $this->displayMissions;
	}

	public function setDisplayMissions($bool) {
		if ($this->displayMissions == $bool) {
			return;
		}
		$this->displayMissions = $bool;
		$this->hasChanged = true;
	}

	public function getMissions() {
		if (!isset($this->missions)) {
			$this->db->query('SELECT * FROM player_has_mission WHERE ' . $this->SQL);
			$this->missions = array();
			while ($this->db->nextRecord()) {
				$missionID = $this->db->getInt('mission_id');
				$this->missions[$missionID] = array(
					'On Step' => $this->db->getInt('on_step'),
					'Progress' => $this->db->getInt('progress'),
					'Unread' => $this->db->getBoolean('unread'),
					'Expires' => $this->db->getInt('step_fails'),
					'Sector' => $this->db->getInt('mission_sector'),
					'Starting Sector' => $this->db->getInt('starting_sector')
				);
				$this->rebuildMission($missionID);
			}
		}
		return $this->missions;
	}

	public function getActiveMissions() {
		$missions = $this->getMissions();
		foreach ($missions as $missionID => $mission) {
			if ($mission['On Step'] >= count(MISSIONS[$missionID]['Steps'])) {
				unset($missions[$missionID]);
			}
		}
		return $missions;
	}

	protected function getMission($missionID) {
		$missions = $this->getMissions();
		if (isset($missions[$missionID])) {
			return $missions[$missionID];
		}
		return false;
	}

	protected function hasMission($missionID) {
		return $this->getMission($missionID) !== false;
	}

	protected function updateMission($missionID) {
		$this->getMissions();
		if (isset($this->missions[$missionID])) {
			$mission = $this->missions[$missionID];
			$this->db->query('
				UPDATE player_has_mission
				SET on_step = ' . $this->db->escapeNumber($mission['On Step']) . ',
					progress = ' . $this->db->escapeNumber($mission['Progress']) . ',
					unread = ' . $this->db->escapeBoolean($mission['Unread']) . ',
					starting_sector = ' . $this->db->escapeNumber($mission['Starting Sector']) . ',
					mission_sector = ' . $this->db->escapeNumber($mission['Sector']) . ',
					step_fails = ' . $this->db->escapeNumber($mission['Expires']) . '
				WHERE ' . $this->SQL . ' AND mission_id = ' . $this->db->escapeNumber($missionID) . ' LIMIT 1'
			);
			return true;
		}
		return false;
	}

	private function setupMissionStep($missionID) {
		$mission =& $this->missions[$missionID];
		if ($mission['On Step'] >= count(MISSIONS[$missionID]['Steps'])) {
			// Nothing to do if this mission is already completed
			return;
		}
		$step = MISSIONS[$missionID]['Steps'][$mission['On Step']];
		if (isset($step['PickSector'])) {
			$realX = Plotter::getX($step['PickSector']['Type'], $step['PickSector']['X'], $this->getGameID());
			if ($realX === false) {
				throw new Exception('Invalid PickSector definition in mission: ' . $missionID);
			}
			$path = Plotter::findDistanceToX($realX, $this->getSector(), true, null, $this);
			if ($path === false) {
				// Abandon the mission if it cannot be completed due to a
				// sector that does not exist or cannot be reached.
				// (Probably shouldn't bestow this mission in the first place)
				$this->deleteMission($missionID);
				create_error('Cannot find a path to the destination!');
			}
			$mission['Sector'] = $path->getEndSectorID();
		}
	}

	/**
	 * Declining a mission will permanently hide it from the player
	 * by adding it in its completed state.
	 */
	public function declineMission($missionID) {
		$finishedStep = count(MISSIONS[$missionID]['Steps']);
		$this->addMission($missionID, $finishedStep);
	}

	public function addMission($missionID, $step = 0) {
		$this->getMissions();

		if (isset($this->missions[$missionID])) {
			return;
		}
		$sector = 0;

		$mission = array(
			'On Step' => $step,
			'Progress' => 0,
			'Unread' => true,
			'Expires' => (TIME + 86400),
			'Sector' => $sector,
			'Starting Sector' => $this->getSectorID()
		);

		$this->missions[$missionID] =& $mission;
		$this->setupMissionStep($missionID);
		$this->rebuildMission($missionID);

		$this->db->query('
			REPLACE INTO player_has_mission (game_id,account_id,mission_id,on_step,progress,unread,starting_sector,mission_sector,step_fails)
			VALUES ('.$this->db->escapeNumber($this->gameID) . ',' . $this->db->escapeNumber($this->accountID) . ',' . $this->db->escapeNumber($missionID) . ',' . $this->db->escapeNumber($mission['On Step']) . ',' . $this->db->escapeNumber($mission['Progress']) . ',' . $this->db->escapeBoolean($mission['Unread']) . ',' . $this->db->escapeNumber($mission['Starting Sector']) . ',' . $this->db->escapeNumber($mission['Sector']) . ',' . $this->db->escapeNumber($mission['Expires']) . ')'
		);
	}

	private function rebuildMission($missionID) {
		$mission = $this->missions[$missionID];
		$this->missions[$missionID]['Name'] = MISSIONS[$missionID]['Name'];

		if ($mission['On Step'] >= count(MISSIONS[$missionID]['Steps'])) {
			// If we have completed this mission just use false to indicate no current task.
			$currentStep = false;
		} else {
			$data = ['player' => $this, 'mission' => $mission];
			$currentStep = MISSIONS[$missionID]['Steps'][$mission['On Step']];
			array_walk_recursive($currentStep, 'replaceMissionTemplate', $data);
		}
		$this->missions[$missionID]['Task'] = $currentStep;
	}

	public function deleteMission($missionID) {
		$this->getMissions();
		if (isset($this->missions[$missionID])) {
			unset($this->missions[$missionID]);
			$this->db->query('DELETE FROM player_has_mission WHERE ' . $this->SQL . ' AND mission_id = ' . $this->db->escapeNumber($missionID) . ' LIMIT 1');
			return true;
		}
		return false;
	}

	public function markMissionsRead() {
		$this->getMissions();
		$unreadMissions = array();
		foreach ($this->missions as $missionID => &$mission) {
			if ($mission['Unread']) {
				$unreadMissions[] = $missionID;
				$mission['Unread'] = false;
				$this->updateMission($missionID);
			}
		}
		return $unreadMissions;
	}

	public function claimMissionReward($missionID) {
		$this->getMissions();
		$mission =& $this->missions[$missionID];
		if ($mission === false) {
			throw new Exception('Unknown mission: ' . $missionID);
		}
		if ($mission['Task'] === false || $mission['Task']['Step'] != 'Claim') {
			throw new Exception('Cannot claim mission: ' . $missionID . ', for step: ' . $mission['On Step']);
		}
		$mission['On Step']++;
		$mission['Unread'] = true;
		foreach ($mission['Task']['Rewards'] as $rewardItem => $amount) {
			switch ($rewardItem) {
				case 'Credits':
					$this->increaseCredits($amount);
				break;
				case 'Experience':
					$this->increaseExperience($amount);
				break;
			}
		}
		$rewardText = $mission['Task']['Rewards']['Text'];
		if ($mission['On Step'] < count(MISSIONS[$missionID]['Steps'])) {
			// If we haven't finished the mission yet then 
			$this->setupMissionStep($missionID);
		}
		$this->rebuildMission($missionID);
		$this->updateMission($missionID);
		return $rewardText;
	}

	public function getAvailableMissions() {
		$availableMissions = array();
		foreach (MISSIONS as $missionID => $mission) {
			if ($this->hasMission($missionID)) {
				continue;
			}
			$realX = Plotter::getX($mission['HasX']['Type'], $mission['HasX']['X'], $this->getGameID());
			if ($realX === false) {
				throw new Exception('Invalid HasX definition in mission: ' . $missionID);
			}
			if ($this->getSector()->hasX($realX)) {
				$availableMissions[$missionID] = $mission;
			}
		}
		return $availableMissions;
	}

	public function actionTaken($actionID, array $values) {
		if (!in_array($actionID, MISSION_ACTIONS)) {
			throw new Exception('Unknown action: ' . $actionID);
		}
// TODO: Reenable this once tested.		if($this->getAccount()->isLoggingEnabled())
			switch ($actionID) {
				case 'WalkSector':
					$this->getAccount()->log(LOG_TYPE_MOVEMENT, 'Walks to sector: ' . $values['Sector']->getSectorID(), $this->getSectorID());
				break;
				case 'JoinAlliance':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'joined alliance: ' . $values['Alliance']->getAllianceName(), $this->getSectorID());
				break;
				case 'LeaveAlliance':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'left alliance: ' . $values['Alliance']->getAllianceName(), $this->getSectorID());
				break;
				case 'DisbandAlliance':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'disbanded alliance ' . $values['Alliance']->getAllianceName(), $this->getSectorID());
				break;
				case 'KickPlayer':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'kicked ' . $values['Player']->getAccount()->getLogin() . ' (' . $values['Player']->getPlayerName() . ') from alliance ' . $values['Alliance']->getAllianceName(), 0);
				break;
				case 'PlayerKicked':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'was kicked from alliance ' . $values['Alliance']->getAllianceName() . ' by ' . $values['Player']->getAccount()->getLogin() . ' (' . $values['Player']->getPlayerName() . ')', 0);
				break;

			}
		$this->getMissions();
		foreach ($this->missions as $missionID => &$mission) {
			if ($mission['Task'] !== false && $mission['Task']['Step'] == $actionID) {
				$requirements = $mission['Task']['Detail'];
				if (checkMissionRequirements($values, $requirements) === true) {
					$mission['On Step']++;
					$mission['Unread'] = true;
					$this->setupMissionStep($missionID);
					$this->rebuildMission($missionID);
					$this->updateMission($missionID);
				}
			}
		}
	}

	public function canSeeAny(array $otherPlayerArray) {
		foreach ($otherPlayerArray as $otherPlayer) {
			if ($this->canSee($otherPlayer)) {
				return true;
			}
		}
		return false;
	}

	public function canSee(AbstractSmrPlayer $otherPlayer) {
		if (!$otherPlayer->getShip()->isCloaked()) {
			return true;
		}
		if ($this->sameAlliance($otherPlayer)) {
			return true;
		}
		if ($this->getExperience() >= $otherPlayer->getExperience()) {
			return true;
		}
		return false;
	}

	public function equals(AbstractSmrPlayer $otherPlayer = null) {
		return $otherPlayer !== null && $this->getAccountID() == $otherPlayer->getAccountID() && $this->getGameID() == $otherPlayer->getGameID();
	}

	public function sameAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->equals($otherPlayer) || (!is_null($otherPlayer) && $this->getGameID() == $otherPlayer->getGameID() && $this->hasAlliance() && $this->getAllianceID() == $otherPlayer->getAllianceID());
	}

	public function sharedForceAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function forceNAPAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function planetNAPAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderNAPAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderMAPAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->traderAttackTraderAlliance($otherPlayer) && $this->traderDefendTraderAlliance($otherPlayer);
	}

	public function traderAttackTraderAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderDefendTraderAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderAttackForceAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderAttackPortAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderAttackPlanetAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function meetsAlignmentRestriction($restriction) {
		if ($restriction < 0) {
			return $this->getAlignment() <= $restriction;
		}
		if ($restriction > 0) {
			return $this->getAlignment() >= $restriction;
		}
		return true;
	}

	// Get an array of goods that are visible to the player
	public function getVisibleGoods() {
		$goods = Globals::getGoods();
		$visibleGoods = array();
		foreach ($goods as $key => $good) {
			if ($this->meetsAlignmentRestriction($good['AlignRestriction'])) {
				$visibleGoods[$key] = $good;
			}
		}
		return $visibleGoods;
	}

	/**
	 * Will retrieve all visited sectors, use only when you are likely to check a large number of these
	 */
	public function hasVisitedSector($sectorID) {
		if (!isset($this->visitedSectors)) {
			$this->visitedSectors = array();
			$this->db->query('SELECT sector_id FROM player_visited_sector WHERE ' . $this->SQL);
			while ($this->db->nextRecord()) {
				$this->visitedSectors[$this->db->getInt('sector_id')] = false;
			}
		}
		return !isset($this->visitedSectors[$sectorID]);
	}

	public function getLeaveNewbieProtectionHREF() {
		return SmrSession::getNewHREF(create_container('leave_newbie_processing.php'));
	}

	public function getExamineTraderHREF() {
		$container = create_container('skeleton.php', 'trader_examine.php');
		$container['target'] = $this->getAccountID();
		return SmrSession::getNewHREF($container);
	}

	public function getAttackTraderHREF() {
		return Globals::getAttackTraderHREF($this->getAccountID());
	}

	public function getPlanetKickHREF() {
		$container = create_container('planet_kick_processing.php', 'trader_attack_processing.php');
		$container['account_id'] = $this->getAccountID();
		return SmrSession::getNewHREF($container);
	}

	public function getTraderSearchHREF() {
		$container = create_container('skeleton.php', 'trader_search_result.php');
		$container['player_id'] = $this->getPlayerID();
		return SmrSession::getNewHREF($container);
	}

	public function getAllianceRosterHREF() {
		return Globals::getAllianceRosterHREF($this->getAllianceID());
	}

	public function getToggleWeaponHidingHREF($ajax = false) {
		$container = create_container('toggle_processing.php');
		$container['toggle'] = 'WeaponHiding';
		$container['AJAX'] = $ajax;
		return SmrSession::getNewHREF($container);
	}

	public function isDisplayWeapons() {
		return $this->displayWeapons;
	}

	/**
	 * Should weapons be displayed in the right panel?
	 * This updates the player database directly because it is used with AJAX,
	 * which does not acquire a sector lock.
	 */
	public function setDisplayWeapons($bool) {
		if ($this->displayWeapons == $bool) {
			return;
		}
		$this->displayWeapons = $bool;
		$this->db->query('UPDATE player SET display_weapons=' . $this->db->escapeBoolean($this->displayWeapons) . ' WHERE ' . $this->SQL);
	}

	public function update() {
		$this->save();
	}

	public function save() {
		if ($this->hasChanged === true) {
			$this->db->query('UPDATE player SET player_name=' . $this->db->escapeString($this->playerName) .
				', player_id=' . $this->db->escapeNumber($this->playerID) .
				', sector_id=' . $this->db->escapeNumber($this->sectorID) .
				', last_sector_id=' . $this->db->escapeNumber($this->lastSectorID) .
				', turns=' . $this->db->escapeNumber($this->turns) .
				', last_turn_update=' . $this->db->escapeNumber($this->lastTurnUpdate) .
				', newbie_turns=' . $this->db->escapeNumber($this->newbieTurns) .
				', last_news_update=' . $this->db->escapeNumber($this->lastNewsUpdate) .
				', attack_warning=' . $this->db->escapeString($this->attackColour) .
				', dead=' . $this->db->escapeBoolean($this->dead) .
				', newbie_status=' . $this->db->escapeBoolean($this->newbieStatus) .
				', land_on_planet=' . $this->db->escapeBoolean($this->landedOnPlanet) .
				', last_active=' . $this->db->escapeNumber($this->lastActive) .
				', last_cpl_action=' . $this->db->escapeNumber($this->lastCPLAction) .
				', race_id=' . $this->db->escapeNumber($this->raceID) .
				', credits=' . $this->db->escapeNumber($this->credits) .
				', experience=' . $this->db->escapeNumber($this->experience) .
				', alignment=' . $this->db->escapeNumber($this->alignment) .
				', military_payment=' . $this->db->escapeNumber($this->militaryPayment) .
				', alliance_id=' . $this->db->escapeNumber($this->allianceID) .
				', alliance_join=' . $this->db->escapeNumber($this->allianceJoinable) .
				', ship_type_id=' . $this->db->escapeNumber($this->shipID) .
				', kills=' . $this->db->escapeNumber($this->kills) .
				', deaths=' . $this->db->escapeNumber($this->deaths) .
				', assists=' . $this->db->escapeNumber($this->assists) .
				', last_port=' . $this->db->escapeNumber($this->lastPort) .
				', bank=' . $this->db->escapeNumber($this->bank) .
				', zoom=' . $this->db->escapeNumber($this->zoom) .
				', display_missions=' . $this->db->escapeBoolean($this->displayMissions) .
				', force_drop_messages=' . $this->db->escapeBoolean($this->forceDropMessages) .
				', group_scout_messages=' . $this->db->escapeString($this->groupScoutMessages) .
				', ignore_globals=' . $this->db->escapeBoolean($this->ignoreGlobals) .
				', newbie_warning = ' . $this->db->escapeBoolean($this->newbieWarning) .
				', name_changed = ' . $this->db->escapeBoolean($this->nameChanged) .
				', race_changed = ' . $this->db->escapeBoolean($this->raceChanged) .
				', combat_drones_kamikaze_on_mines = ' . $this->db->escapeBoolean($this->combatDronesKamikazeOnMines) .
				' WHERE ' . $this->SQL . ' LIMIT 1');
			$this->hasChanged = false;
		}
		foreach ($this->hasBountyChanged as $key => &$bountyChanged) {
			if ($bountyChanged === true) {
				$bountyChanged = false;
				$bounty = $this->getBounty($key);
				if ($bounty['New'] === true) {
					if ($bounty['Amount'] > 0 || $bounty['SmrCredits'] > 0) {
						$this->db->query('INSERT INTO bounty (account_id,game_id,type,amount,smr_credits,claimer_id,time) VALUES (' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeString($bounty['Type']) . ',' . $this->db->escapeNumber($bounty['Amount']) . ',' . $this->db->escapeNumber($bounty['SmrCredits']) . ',' . $this->db->escapeNumber($bounty['Claimer']) . ',' . $this->db->escapeNumber($bounty['Time']) . ')');
					}
				} else {
					if ($bounty['Amount'] > 0 || $bounty['SmrCredits'] > 0) {
						$this->db->query('UPDATE bounty
							SET amount=' . $this->db->escapeNumber($bounty['Amount']) . ',
							smr_credits=' . $this->db->escapeNumber($bounty['SmrCredits']) . ',
							type=' . $this->db->escapeString($bounty['Type']) . ',
							claimer_id=' . $this->db->escapeNumber($bounty['Claimer']) . ',
							time=' . $this->db->escapeNumber($bounty['Time']) . '
							WHERE bounty_id=' . $this->db->escapeNumber($bounty['ID']) . ' AND ' . $this->SQL . ' LIMIT 1');
					} else {
						$this->db->query('DELETE FROM bounty WHERE bounty_id=' . $this->db->escapeNumber($bounty['ID']) . ' AND ' . $this->SQL . ' LIMIT 1');
					}
				}
			}
		}
		$this->saveHOF();
	}

	public function saveHOF() {
		if (count($this->hasHOFChanged) > 0) {
			$this->doHOFSave($this->hasHOFChanged);
			$this->hasHOFChanged = [];
		}
		if (!empty(self::$hasHOFVisChanged)) {
			foreach (self::$hasHOFVisChanged as $hofType => $changeType) {
				if ($changeType == self::HOF_NEW) {
					$this->db->query('INSERT INTO hof_visibility (type, visibility) VALUES (' . $this->db->escapeString($hofType) . ',' . $this->db->escapeString(self::$HOFVis[$hofType]) . ')');
				} else {
					$this->db->query('UPDATE hof_visibility SET visibility = ' . $this->db->escapeString(self::$HOFVis[$hofType]) . ' WHERE type = ' . $this->db->escapeString($hofType) . ' LIMIT 1');
				}
				unset(self::$hasHOFVisChanged[$hofType]);
			}
		}
	}

	/**
	 * This should only be called by `saveHOF` (and recursively) to
	 * ensure that the `hasHOFChanged` attribute is properly cleared.
	 */
	protected function doHOFSave(array $hasChangedList, array $typeList = array()) {
		foreach ($hasChangedList as $type => $hofChanged) {
			$tempTypeList = $typeList;
			$tempTypeList[] = $type;
			if (is_array($hofChanged)) {
				$this->doHOFSave($hofChanged, $tempTypeList);
			} else {
				$amount = $this->getHOF($tempTypeList);
				if ($hofChanged == self::HOF_NEW) {
					if ($amount > 0) {
						$this->db->query('INSERT INTO player_hof (account_id,game_id,type,amount) VALUES (' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeArray($tempTypeList, false, true, ':', false) . ',' . $this->db->escapeNumber($amount) . ')');
					}
				} elseif ($hofChanged == self::HOF_CHANGED) {
					$this->db->query('UPDATE player_hof
						SET amount=' . $this->db->escapeNumber($amount) . '
						WHERE ' . $this->SQL . ' AND type = ' . $this->db->escapeArray($tempTypeList, false, true, ':', false) . ' LIMIT 1');
				}
			}
		}
	}

}
