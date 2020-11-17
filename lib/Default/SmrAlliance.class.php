<?php declare(strict_types=1);

class SmrAlliance {
	protected static $CACHE_ALLIANCES = array();

	protected $db;
	protected $SQL;

	protected $gameID;
	protected $allianceID;
	protected $allianceName;
	protected $description;
	protected $password;
	protected $recruiting;
	protected $leaderID;
	protected $bank;
	protected $kills;
	protected $deaths;
	protected $motd;
	protected $imgSrc;
	protected $discordServer;
	protected $discordChannel;
	protected $ircChannel;
	protected $flagshipID;

	protected $memberList;
	protected $seedlist;

	// Recruit type constants
	const RECRUIT_OPEN = "open";
	const RECRUIT_CLOSED = "closed";
	const RECRUIT_PASSWORD = "password";

	public static function getAlliance($allianceID, $gameID, $forceUpdate = false) {
		if ($forceUpdate || !isset(self::$CACHE_ALLIANCES[$gameID][$allianceID])) {
			self::$CACHE_ALLIANCES[$gameID][$allianceID] = new SmrAlliance($allianceID, $gameID);
		}
		return self::$CACHE_ALLIANCES[$gameID][$allianceID];
	}

	public static function getAllianceByDiscordChannel($channel, $forceUpdate = false) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT alliance_id, game_id FROM alliance JOIN game USING(game_id) WHERE discord_channel = ' . $db->escapeString($channel) . ' AND game.end_time > ' . $db->escapeNumber(time()) . ' ORDER BY game_id DESC LIMIT 1');
		if ($db->nextRecord()) {
			return self::getAlliance($db->getInt('alliance_id'), $db->getInt('game_id'), $forceUpdate);
		} else {
			return null;
		}
	}

	public static function getAllianceByIrcChannel($channel, $forceUpdate = false) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT alliance_id, game_id FROM irc_alliance_has_channel WHERE channel = ' . $db->escapeString($channel) . ' LIMIT 1');
		if ($db->nextRecord()) {
			return self::getAlliance($db->getInt('alliance_id'), $db->getInt('game_id'), $forceUpdate);
		}
		$return = null;
		return $return;
	}

	public static function getAllianceByName($name, $gameID, $forceUpdate = false) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT alliance_id FROM alliance WHERE alliance_name = ' . $db->escapeString($name) . ' AND game_id = ' . $db->escapeNumber($gameID) . ' LIMIT 1');
		if ($db->nextRecord()) {
			return self::getAlliance($db->getInt('alliance_id'), $gameID, $forceUpdate);
		} else {
			return null;
		}
	}

	protected function __construct($allianceID, $gameID) {
		$this->db = new SmrMySqlDatabase();

		$this->allianceID = $allianceID;
		$this->gameID = $gameID;
		$this->SQL = 'alliance_id=' . $this->db->escapeNumber($allianceID) . ' AND game_id=' . $this->db->escapeNumber($gameID);

		if ($allianceID != 0) {
			$this->db->query('SELECT * FROM alliance WHERE ' . $this->SQL);
			$this->db->nextRecord();
			$this->allianceName = $this->db->getField('alliance_name');
			$this->password = stripslashes($this->db->getField('alliance_password'));
			$this->recruiting = $this->db->getBoolean('recruiting');
			$this->description = $this->db->getField('alliance_description');
			$this->leaderID = $this->db->getInt('leader_id');
			$this->bank = $this->db->getInt('alliance_account');
			$this->kills = $this->db->getInt('alliance_kills');
			$this->deaths = $this->db->getInt('alliance_deaths');
			$this->motd = $this->db->getField('mod');
			$this->imgSrc = $this->db->getField('img_src');
			$this->discordServer = $this->db->getField('discord_server');
			$this->discordChannel = $this->db->getField('discord_channel');
			$this->flagshipID = $this->db->getInt('flagship_id');

			if (empty($this->kills)) {
				$this->kills = 0;
			}
			if (empty($this->deaths)) {
				$this->deaths = 0;
			}
		}
	}

	/**
	 * Create an alliance and return the new object.
	 * Starts alliance with "closed" recruitment (for safety).
	 */
	public static function createAlliance($gameID, $name) {
		$db = new SmrMySqlDatabase();

		// check if the alliance name already exists
		$db->query('SELECT 1 FROM alliance WHERE alliance_name = ' . $db->escapeString($name) . ' AND game_id = ' . $db->escapeNumber($gameID) . ' LIMIT 1');
		if ($db->getNumRows() > 0) {
			create_error('That alliance name already exists!');
		}

		// get the next alliance id (ignoring reserved ID's)
		$db->query('SELECT max(alliance_id) FROM alliance WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND (alliance_id < ' . $db->escapeNumber(NHA_ID) . ' OR alliance_id > ' . $db->escapeNumber(NHA_ID + 7) . ') LIMIT 1');
		$db->requireRecord();
		$allianceID = $db->getInt('max(alliance_id)') + 1;
		if ($allianceID >= NHA_ID && $allianceID <= NHA_ID + 7) {
			$allianceID = NHA_ID + 8;
		}

		// actually create the alliance here
		$db->query('INSERT INTO alliance (alliance_id, game_id, alliance_name, alliance_password, recruiting) VALUES(' . $db->escapeNumber($allianceID) . ', ' . $db->escapeNumber($gameID) . ', ' . $db->escapeString($name) . ', \'\', \'FALSE\')');

		return self::getAlliance($allianceID, $gameID);
	}

	/**
	 * Returns true if the alliance ID is associated with allianceless players.
	 */
	public function isNone() {
		return $this->allianceID == 0;
	}

	public function getAllianceID() {
		return $this->allianceID;
	}

	public function getAllianceBBLink() {
		return '[alliance=' . $this->allianceID . ']';
	}

	public function getAllianceDisplayName($linked = false, $includeAllianceID = false) {
		$name = htmlentities($this->allianceName);
		if ($includeAllianceID) {
			$name .= ' (' . $this->allianceID . ')';
		}
		if ($linked === true && !$this->hasDisbanded()) {
			return create_link(Globals::getAllianceRosterHREF($this->getAllianceID()), $name);
		}
		return $name;
	}

	/**
	 * Returns the alliance name.
	 * Use getAllianceDisplayName for an HTML-safe version.
	 */
	public function getAllianceName() {
		return $this->allianceName;
	}

	public function getGameID() {
		return $this->gameID;
	}

	public function getGame() {
		return SmrGame::getGame($this->gameID);
	}

	public function hasDisbanded() {
		return !$this->hasLeader();
	}

	public function hasLeader() {
		return $this->getLeaderID() != 0;
	}

	public function getLeaderID() {
		return $this->leaderID;
	}

	public function getLeader() {
		return SmrPlayer::getPlayer($this->getLeaderID(), $this->getGameID());
	}

	public function setLeaderID($leaderID) {
		$this->leaderID = $leaderID;
	}

	public function getDiscordServer() {
		return $this->discordServer;
	}

	public function setDiscordServer($serverId) {
		$this->discordServer = $serverId;
	}

	public function getDiscordChannel() {
		return $this->discordChannel;
	}

	public function setDiscordChannel($channelId) {
		$this->discordChannel = $channelId;
	}

	public function getIrcChannel() {
		if (!isset($this->ircChannel)) {
			$this->db->query('SELECT channel FROM irc_alliance_has_channel WHERE ' . $this->SQL);
			if ($this->db->nextRecord()) {
				$this->ircChannel = $this->db->getField('channel');
			} else {
				$this->ircChannel = '';
			}
		}
		return $this->ircChannel;
	}

	public function setIrcChannel($ircChannel) {
		$this->getIrcChannel(); // to populate the class attribute
		if ($this->ircChannel == $ircChannel) {
			return;
		}
		if (strlen($ircChannel) > 0 && $ircChannel != '#') {
			if ($ircChannel[0] != '#') {
				$ircChannel = '#' . $ircChannel;
			}
			if ($ircChannel == '#smr' || $ircChannel == '#smr-bar') {
				create_error('Please enter a valid irc channel for your alliance.');
			}

			$this->db->query('REPLACE INTO irc_alliance_has_channel (channel,alliance_id,game_id) values (' . $this->db->escapeString($ircChannel) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ',' . $this->db->escapeNumber($this->getGameID()) . ');');
		} else {
			$this->db->query('DELETE FROM irc_alliance_has_channel WHERE ' . $this->SQL);
		}
		$this->ircChannel = $ircChannel;
	}

	public function hasImageURL() {
		return strlen($this->imgSrc) && $this->imgSrc != 'http://';
	}

	public function getImageURL() {
		return $this->imgSrc;
	}

	public function setImageURL($url) {
		if (preg_match('/"/', $url)) {
			throw new Exception('Tried to set an image url with ": ' . $url);
		}
		$this->imgSrc = htmlspecialchars($url);
	}

	/**
	 * Get the total credits in the alliance bank account.
	 */
	public function getBank() {
		return $this->bank;
	}

	/**
	 * Increases alliance bank account up to the maximum allowed credits.
	 * Returns the amount that was actually added to handle overflow.
	 */
	public function increaseBank(int $credits) : int {
		$newTotal = min($this->bank + $credits, MAX_MONEY);
		$actualAdded = $newTotal - $this->bank;
		$this->setBank($newTotal);
		return $actualAdded;
	}

	public function decreaseBank(int $credits) : void {
		$newTotal = $this->bank - $credits;
		$this->setBank($newTotal);
	}

	public function setBank(int $credits) : void {
		$this->bank = $credits;
	}

	/**
	 * Get (HTML-safe) alliance Message of the Day for display.
	 */
	public function getMotD() {
		return htmlentities($this->motd);
	}

	public function setMotD($motd) {
		$this->motd = $motd;
	}

	public function getPassword() {
		return $this->password;
	}

	public function isRecruiting() : bool {
		return $this->recruiting;
	}

	/**
	 * Set the password and recruiting attributes.
	 * The input $password is ignored except for the "password" $type.
	 */
	public function setRecruitType(string $type, string $password) : void {
		if ($type == self::RECRUIT_CLOSED) {
			$this->recruiting = false;
			$this->password = '';
		} elseif ($type == self::RECRUIT_OPEN) {
			$this->recruiting = true;
			$this->password = '';
		} elseif ($type == self::RECRUIT_PASSWORD) {
			if (empty($password)) {
				throw new Exception('Password must not be empty here');
			}
			$this->recruiting = true;
			$this->password = $password;
		} else {
			throw new Exception('Unknown recruit type: ' . $type);
		}
	}

	public function getRecruitType() : string {
		if (!$this->isRecruiting()) {
			return self::RECRUIT_CLOSED;
		} elseif (empty($this->getPassword())) {
			return self::RECRUIT_OPEN;
		} else {
			return self::RECRUIT_PASSWORD;
		}
	}

	/**
	 * List of all recruitment types and their descriptions.
	 * Do not change the order of elements in the list!
	 */
	public static function allRecruitTypes() : array {
		// The first type is the default option when creating new alliances
		return [
			self::RECRUIT_PASSWORD => "Players can join by password or invitation",
			self::RECRUIT_CLOSED => "Players can join by invitation only",
			self::RECRUIT_OPEN => "Anyone can join (no password needed)",
		];
	}

	public function getKills() {
		return $this->kills;
	}

	public function getDeaths() {
		return $this->deaths;
	}

	/**
	 * Get (HTML-safe) alliance description for display.
	 */
	public function getDescription() {
		if (empty($this->description)) {
			return '';
		} else {
			return htmlentities($this->description);
		}
	}

	public function setAllianceDescription($description) {
		$description = word_filter($description);
		if ($description == $this->description) {
			return;
		}
		global $player, $account;
		$boxDescription = 'Alliance ' . $this->getAllianceBBLink() . ' had their description changed to:' . EOL . EOL . $description;
		if (is_object($player)) {
			$player->sendMessageToBox(BOX_ALLIANCE_DESCRIPTIONS, $boxDescription);
		} else {
			$account->sendMessageToBox(BOX_ALLIANCE_DESCRIPTIONS, $boxDescription);
		}
		$this->description = $description;
	}

	public function hasFlagship() {
		return $this->flagshipID != 0;
	}

	/**
	 * Get account ID of the player designated as the alliance flagship.
	 * Returns 0 if no flagship.
	 */
	public function getFlagshipID() {
		return $this->flagshipID;
	}

	/**
	 * Designate a player as the alliance flagship by their account ID.
	 */
	public function setFlagshipID($accountID) {
		if ($this->flagshipID == $accountID) {
			return;
		}
		$this->flagshipID = $accountID;
	}

	public function canJoinAlliance(SmrPlayer $player, $doAllianceCheck = true) {
		if (!$player->getAccount()->isValidated()) {
			return 'You cannot join an alliance until you validate your account.';
		}
		if ($this->hasDisbanded()) {
			return 'This alliance has disbanded!';
		}
		if ($doAllianceCheck && $player->hasAlliance()) {
			return 'You are already in an alliance!';
		}
		if (!$this->isRecruiting()) {
			return 'This alliance is not currently accepting new recruits.';
		}
		if ($player->getAllianceJoinable() > TIME) {
			return 'You cannot join another alliance for ' . format_time($player->getAllianceJoinable() - TIME) . '.';
		}
		if ($this->getNumMembers() < $this->getGame()->getAllianceMaxPlayers()) {
			if ($player->hasNewbieStatus()) {
				return true;
			}
			$maxVets = $this->getGame()->getAllianceMaxVets();
			if ($this->getNumMembers() < $maxVets) {
				return true;
			}
			$this->db->query('SELECT status FROM player_joined_alliance WHERE account_id=' . $this->db->escapeNumber($player->getAccountID()) . ' AND ' . $this->SQL);
			if ($this->db->nextRecord()) {
				if ($this->db->getField('status') == 'NEWBIE') {
					return true;
				}
			}
			$this->db->query('SELECT COUNT(*) AS num_orig_vets
							FROM player_joined_alliance
							JOIN player USING (account_id, alliance_id, game_id)
							WHERE ' . $this->SQL . ' AND status=\'VETERAN\'');
			if (!$this->db->nextRecord() || $this->db->getInt('num_orig_vets') < $maxVets) {
				return true;
			}
		}
		return 'There is not currently enough room for you in this alliance.';
	}

	public function getNumVeterans() {
		$numVeterans = 0;
		foreach ($this->getMembers() as $player) {
			if (!$player->hasNewbieStatus()) {
				$numVeterans++;
			}
		}
		return $numVeterans;
	}

	public function getNumMembers() {
		return count($this->getMemberIDs());
	}

	public function update() {
		$this->db->query('UPDATE alliance SET
								alliance_password = ' . $this->db->escapeString($this->password) . ',
								recruiting = ' . $this->db->escapeBoolean($this->recruiting) . ',
								alliance_account = ' . $this->db->escapeNumber($this->bank) . ',
								alliance_description = ' . $this->db->escapeString($this->description, true, true) . ',
								`mod` = ' . $this->db->escapeString($this->motd) . ',
								img_src = ' . $this->db->escapeString($this->imgSrc) . ',
								alliance_kills = ' . $this->db->escapeNumber($this->kills) . ',
								alliance_deaths = ' . $this->db->escapeNumber($this->deaths) . ',
								discord_server = ' . $this->db->escapeString($this->discordServer, true, true) . ',
								discord_channel = ' . $this->db->escapeString($this->discordChannel, true, true) . ',
								flagship_id = ' . $this->db->escapeNumber($this->flagshipID) . ',
								leader_id = ' . $this->db->escapeNumber($this->leaderID) . '
							WHERE ' . $this->SQL);
	}

	/**
	 * Returns the members of this alliance as an array of SmrPlayer objects.
	 */
	public function getMembers() {
		return SmrPlayer::getAlliancePlayers($this->getGameID(), $this->getAllianceID());
	}

	public function getMemberIDs() {
		if (!isset($this->memberList)) {
			$this->db->query('SELECT account_id FROM player WHERE ' . $this->SQL);

			//we have the list of players put them in an array now
			$this->memberList = array();
			while ($this->db->nextRecord()) {
				$this->memberList[] = $this->db->getInt('account_id');
			}
		}
		return $this->memberList;
	}
	
	public function getActiveIDs() {
		$activeIDs = array();
		
		$this->db->query('SELECT account_id
						FROM active_session
						JOIN player USING(account_id, game_id)
						WHERE '.$this->SQL . ' AND last_accessed >= ' . $this->db->escapeNumber(TIME - 600));
		
		while ($this->db->nextRecord()) {
			$activeIDs[] = $this->db->getInt('account_id');
		}
		
		return $activeIDs;
	}

	/**
	 * Return all planets owned by members of this alliance.
	 */
	public function getPlanets() {
		$this->db->query('SELECT planet.*
			FROM player
			JOIN planet ON player.game_id = planet.game_id AND player.account_id = planet.owner_id
			WHERE player.game_id=' . $this->db->escapeNumber($this->gameID) . '
			AND player.alliance_id=' . $this->db->escapeNumber($this->allianceID) . '
			ORDER BY planet.sector_id
		');
		$planets = array();
		while ($this->db->nextRecord()) {
			$planets[] = SmrPlanet::getPlanet($this->gameID, $this->db->getInt('sector_id'), false, $this->db);
		}
		return $planets;
	}

	/**
	 * Return array of sector_id for sectors in the alliance seedlist.
	 */
	public function getSeedlist() {
		if (!isset($this->seedlist)) {
			$this->db->query('SELECT sector_id FROM alliance_has_seedlist WHERE ' . $this->SQL);
			$this->seedlist = array();
			while ($this->db->nextRecord()) {
				$this->seedlist[] = $this->db->getInt('sector_id');
			}
		}
		return $this->seedlist;
	}

	/**
	 * Is the given sector in the alliance seedlist?
	 */
	public function isInSeedlist(SmrSector $sector) {
		return in_array($sector->getSectorID(), $this->getSeedlist());
	}

	/**
	 * Create the default roles for this alliance.
	 * This should only be called once after the alliance is created.
	 */
	public function createDefaultRoles($newMemberPermission = 'basic') {
		$db = $this->db; //for convenience

		$withPerDay = ALLIANCE_BANK_UNLIMITED;
		$removeMember = TRUE;
		$changePass = TRUE;
		$changeMOD = TRUE;
		$changeRoles = TRUE;
		$planetAccess = TRUE;
		$exemptWith = TRUE;
		$mbMessages = TRUE;
		$sendAllMsg = TRUE;
		$opLeader = TRUE;
		$viewBonds = TRUE;
		$db->query('INSERT INTO alliance_has_roles (alliance_id, game_id, role_id, role, with_per_day, remove_member, change_pass, change_mod, change_roles, planet_access, exempt_with, mb_messages, send_alliance_msg, op_leader, view_bonds) ' .
			'VALUES (' . $db->escapeNumber($this->getAllianceID()) . ', ' . $db->escapeNumber($this->getGameID()) . ', ' . $db->escapeNumber(ALLIANCE_ROLE_LEADER) . ', \'Leader\', ' . $db->escapeNumber($withPerDay) . ', ' . $db->escapeBoolean($removeMember) . ', ' . $db->escapeBoolean($changePass) . ', ' . $db->escapeBoolean($changeMOD) . ', ' . $db->escapeBoolean($changeRoles) . ', ' . $db->escapeBoolean($planetAccess) . ', ' . $db->escapeBoolean($exemptWith) . ', ' . $db->escapeBoolean($mbMessages) . ', ' . $db->escapeString($sendAllMsg) . ', ' . $db->escapeBoolean($opLeader) . ', ' . $db->escapeBoolean($viewBonds) . ')');

		switch ($newMemberPermission) {
			case 'full':
				//do nothing, perms already set above.
			break;
			case 'none':
				$withPerDay = 0;
				$removeMember = FALSE;
				$changePass = FALSE;
				$changeMOD = FALSE;
				$changeRoles = FALSE;
				$planetAccess = FALSE;
				$exemptWith = FALSE;
				$mbMessages = FALSE;
				$sendAllMsg = FALSE;
				$opLeader = FALSE;
				$viewBonds = FALSE;
			break;
			case 'basic':
				$withPerDay = ALLIANCE_BANK_UNLIMITED;
				$removeMember = FALSE;
				$changePass = FALSE;
				$changeMOD = FALSE;
				$changeRoles = FALSE;
				$planetAccess = TRUE;
				$exemptWith = FALSE;
				$mbMessages = FALSE;
				$sendAllMsg = FALSE;
				$opLeader = FALSE;
				$viewBonds = FALSE;
			break;
		}
		$db->query('INSERT INTO alliance_has_roles (alliance_id, game_id, role_id, role, with_per_day, remove_member, change_pass, change_mod, change_roles, planet_access, exempt_with, mb_messages, send_alliance_msg, op_leader, view_bonds) ' .
					'VALUES (' . $db->escapeNumber($this->getAllianceID()) . ', ' . $db->escapeNumber($this->getGameID()) . ', ' . $db->escapeNumber(ALLIANCE_ROLE_NEW_MEMBER) . ', \'New Member\', ' . $db->escapeNumber($withPerDay) . ', ' . $db->escapeBoolean($removeMember) . ', ' . $db->escapeBoolean($changePass) . ', ' . $db->escapeBoolean($changeMOD) . ', ' . $db->escapeBoolean($changeRoles) . ', ' . $db->escapeBoolean($planetAccess) . ', ' . $db->escapeBoolean($exemptWith) . ', ' . $db->escapeBoolean($mbMessages) . ', ' . $db->escapeString($sendAllMsg) . ', ' . $db->escapeBoolean($opLeader) . ', ' . $db->escapeBoolean($viewBonds) . ')');

	}

}
