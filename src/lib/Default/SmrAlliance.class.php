<?php declare(strict_types=1);

class SmrAlliance {
	protected static array $CACHE_ALLIANCES = [];

	protected Smr\Database $db;
	protected string $SQL;

	protected int $gameID;
	protected int $allianceID;
	protected string $allianceName;
	protected ?string $description;
	protected string $password;
	protected bool $recruiting;
	protected int $leaderID;
	protected int $bank;
	protected int $kills;
	protected int $deaths;
	protected string $motd;
	protected string $imgSrc;
	protected ?string $discordServer;
	protected ?string $discordChannel;
	protected string $ircChannel;
	protected int $flagshipID;

	protected array $memberList;
	protected array $seedlist;

	// Recruit type constants
	const RECRUIT_OPEN = "open";
	const RECRUIT_CLOSED = "closed";
	const RECRUIT_PASSWORD = "password";

	public static function getAlliance(int $allianceID, int $gameID, bool $forceUpdate = false) : SmrAlliance {
		if ($forceUpdate || !isset(self::$CACHE_ALLIANCES[$gameID][$allianceID])) {
			self::$CACHE_ALLIANCES[$gameID][$allianceID] = new SmrAlliance($allianceID, $gameID);
		}
		return self::$CACHE_ALLIANCES[$gameID][$allianceID];
	}

	public static function getAllianceByDiscordChannel(string $channel, bool $forceUpdate = false) : ?SmrAlliance {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT alliance_id, game_id FROM alliance JOIN game USING(game_id) WHERE discord_channel = ' . $db->escapeString($channel) . ' AND game.end_time > ' . $db->escapeNumber(time()) . ' ORDER BY game_id DESC LIMIT 1');
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			return self::getAlliance($dbRecord->getInt('alliance_id'), $dbRecord->getInt('game_id'), $forceUpdate);
		} else {
			return null;
		}
	}

	public static function getAllianceByIrcChannel(string $channel, bool $forceUpdate = false) : ?SmrAlliance {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT alliance_id, game_id FROM irc_alliance_has_channel WHERE channel = ' . $db->escapeString($channel) . ' LIMIT 1');
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			return self::getAlliance($dbRecord->getInt('alliance_id'), $dbRecord->getInt('game_id'), $forceUpdate);
		} else {
			return null;
		}
	}

	public static function getAllianceByName(string $name, int $gameID, bool $forceUpdate = false) : ?SmrAlliance {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT alliance_id FROM alliance WHERE alliance_name = ' . $db->escapeString($name) . ' AND game_id = ' . $db->escapeNumber($gameID) . ' LIMIT 1');
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			return self::getAlliance($dbRecord->getInt('alliance_id'), $gameID, $forceUpdate);
		} else {
			return null;
		}
	}

	protected function __construct(int $allianceID, int $gameID) {
		$this->db = Smr\Database::getInstance();

		$this->allianceID = $allianceID;
		$this->gameID = $gameID;
		$this->SQL = 'alliance_id=' . $this->db->escapeNumber($allianceID) . ' AND game_id=' . $this->db->escapeNumber($gameID);

		if ($allianceID != 0) {
			$dbResult = $this->db->read('SELECT * FROM alliance WHERE ' . $this->SQL);
			$dbRecord = $dbResult->record();
			$this->allianceName = $dbRecord->getString('alliance_name');
			$this->password = $dbRecord->getField('alliance_password');
			$this->recruiting = $dbRecord->getBoolean('recruiting');
			$this->description = $dbRecord->getField('alliance_description');
			$this->leaderID = $dbRecord->getInt('leader_id');
			$this->bank = $dbRecord->getInt('alliance_account');
			$this->kills = $dbRecord->getInt('alliance_kills');
			$this->deaths = $dbRecord->getInt('alliance_deaths');
			$this->motd = $dbRecord->getField('mod');
			$this->imgSrc = $dbRecord->getField('img_src');
			$this->discordServer = $dbRecord->getField('discord_server');
			$this->discordChannel = $dbRecord->getField('discord_channel');
			$this->flagshipID = $dbRecord->getInt('flagship_id');

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
	public static function createAlliance(int $gameID, string $name) : SmrAlliance {
		$db = Smr\Database::getInstance();

		// check if the alliance name already exists
		$dbResult = $db->read('SELECT 1 FROM alliance WHERE alliance_name = ' . $db->escapeString($name) . ' AND game_id = ' . $db->escapeNumber($gameID) . ' LIMIT 1');
		if ($dbResult->hasRecord()) {
			create_error('That alliance name already exists!');
		}

		// get the next alliance id (ignoring reserved ID's)
		$dbResult = $db->read('SELECT max(alliance_id) FROM alliance WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND (alliance_id < ' . $db->escapeNumber(NHA_ID) . ' OR alliance_id > ' . $db->escapeNumber(NHA_ID + 7) . ') LIMIT 1');
		$allianceID = $dbResult->record()->getInt('max(alliance_id)') + 1;
		if ($allianceID >= NHA_ID && $allianceID <= NHA_ID + 7) {
			$allianceID = NHA_ID + 8;
		}

		// actually create the alliance here
		$db->write('INSERT INTO alliance (alliance_id, game_id, alliance_name, alliance_password, recruiting) VALUES(' . $db->escapeNumber($allianceID) . ', ' . $db->escapeNumber($gameID) . ', ' . $db->escapeString($name) . ', \'\', \'FALSE\')');

		return self::getAlliance($allianceID, $gameID);
	}

	/**
	 * Returns true if the alliance ID is associated with allianceless players.
	 */
	public function isNone() : bool {
		return $this->allianceID == 0;
	}

	public function getAllianceID() : int {
		return $this->allianceID;
	}

	public function getAllianceBBLink() : string {
		return '[alliance=' . $this->allianceID . ']';
	}

	public function getAllianceDisplayName(bool $linked = false, bool $includeAllianceID = false) : string {
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
	public function getAllianceName() : string {
		return $this->allianceName;
	}

	public function getGameID() : int {
		return $this->gameID;
	}

	public function getGame() : SmrGame {
		return SmrGame::getGame($this->gameID);
	}

	public function hasDisbanded() : bool {
		return !$this->hasLeader();
	}

	public function hasLeader() : bool {
		return $this->getLeaderID() != 0;
	}

	public function getLeaderID() : int {
		return $this->leaderID;
	}

	public function getLeader() : SmrPlayer {
		return SmrPlayer::getPlayer($this->getLeaderID(), $this->getGameID());
	}

	public function setLeaderID(int $leaderID) : void {
		$this->leaderID = $leaderID;
	}

	public function getDiscordServer() : ?string {
		return $this->discordServer;
	}

	public function setDiscordServer(string $serverId) : void {
		$this->discordServer = $serverId;
	}

	public function getDiscordChannel() : ?string {
		return $this->discordChannel;
	}

	public function setDiscordChannel(?string $channelId) : void {
		$this->discordChannel = $channelId;
	}

	public function getIrcChannel() : string {
		if (!isset($this->ircChannel)) {
			$dbResult = $this->db->read('SELECT channel FROM irc_alliance_has_channel WHERE ' . $this->SQL);
			if ($dbResult->hasRecord()) {
				$this->ircChannel = $dbResult->record()->getField('channel');
			} else {
				$this->ircChannel = '';
			}
		}
		return $this->ircChannel;
	}

	public function setIrcChannel(string $ircChannel) : void {
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

			$this->db->write('REPLACE INTO irc_alliance_has_channel (channel,alliance_id,game_id) values (' . $this->db->escapeString($ircChannel) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ',' . $this->db->escapeNumber($this->getGameID()) . ');');
		} else {
			$this->db->write('DELETE FROM irc_alliance_has_channel WHERE ' . $this->SQL);
		}
		$this->ircChannel = $ircChannel;
	}

	public function hasImageURL() : bool {
		return strlen($this->imgSrc) && $this->imgSrc != 'http://';
	}

	public function getImageURL() : string {
		return $this->imgSrc;
	}

	public function setImageURL(string $url) : void {
		if (preg_match('/"/', $url)) {
			throw new Exception('Tried to set an image url with ": ' . $url);
		}
		$this->imgSrc = htmlspecialchars($url);
	}

	/**
	 * Get the total credits in the alliance bank account.
	 */
	public function getBank() : int {
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
	public function getMotD() : string {
		return htmlentities($this->motd);
	}

	public function setMotD(string $motd) : void {
		$this->motd = $motd;
	}

	public function getPassword() : string {
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

	public function getKills() : int {
		return $this->kills;
	}

	public function getDeaths() : int {
		return $this->deaths;
	}

	/**
	 * Get (HTML-safe) alliance description for display.
	 */
	public function getDescription() : string {
		if (empty($this->description)) {
			return '';
		} else {
			return htmlentities($this->description);
		}
	}

	public function setAllianceDescription(string $description, AbstractSmrPlayer $player) : void {
		$description = word_filter($description);
		if ($description == $this->description) {
			return;
		}
		$boxDescription = 'Alliance ' . $this->getAllianceBBLink() . ' had their description changed to:' . EOL . EOL . $description;
		$player->sendMessageToBox(BOX_ALLIANCE_DESCRIPTIONS, $boxDescription);
		$this->description = $description;
	}

	public function hasFlagship() : bool {
		return $this->flagshipID != 0;
	}

	/**
	 * Get account ID of the player designated as the alliance flagship.
	 * Returns 0 if no flagship.
	 */
	public function getFlagshipID() : int {
		return $this->flagshipID;
	}

	/**
	 * Designate a player as the alliance flagship by their account ID.
	 */
	public function setFlagshipID(int $accountID) : void {
		if ($this->flagshipID == $accountID) {
			return;
		}
		$this->flagshipID = $accountID;
	}

	public function getJoinRestriction(SmrPlayer $player, bool $doAllianceCheck = true) : string|false {
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
		if ($player->getAllianceJoinable() > Smr\Epoch::time()) {
			return 'You cannot join another alliance for ' . format_time($player->getAllianceJoinable() - Smr\Epoch::time()) . '.';
		}
		if ($this->getNumMembers() < $this->getGame()->getAllianceMaxPlayers()) {
			if ($player->hasNewbieStatus()) {
				return false;
			}
			$maxVets = $this->getGame()->getAllianceMaxVets();
			if ($this->getNumMembers() < $maxVets) {
				return false;
			}
			$dbResult = $this->db->read('SELECT status FROM player_joined_alliance WHERE account_id=' . $this->db->escapeNumber($player->getAccountID()) . ' AND ' . $this->SQL);
			if ($dbResult->hasRecord()) {
				if ($dbResult->record()->getField('status') == 'NEWBIE') {
					return false;
				}
			}
			$dbResult = $this->db->read('SELECT COUNT(*) AS num_orig_vets
							FROM player_joined_alliance
							JOIN player USING (account_id, alliance_id, game_id)
							WHERE ' . $this->SQL . ' AND status=\'VETERAN\'');
			if (!$dbResult->hasRecord() || $dbResult->record()->getInt('num_orig_vets') < $maxVets) {
				return false;
			}
		}
		return 'There is not currently enough room for you in this alliance.';
	}

	public function getNumVeterans() : int {
		$numVeterans = 0;
		foreach ($this->getMembers() as $player) {
			if (!$player->hasNewbieStatus()) {
				$numVeterans++;
			}
		}
		return $numVeterans;
	}

	public function getNumMembers() : int {
		return count($this->getMemberIDs());
	}

	public function update() : void {
		$this->db->write('UPDATE alliance SET
								alliance_password = ' . $this->db->escapeString($this->password) . ',
								recruiting = ' . $this->db->escapeBoolean($this->recruiting) . ',
								alliance_account = ' . $this->db->escapeNumber($this->bank) . ',
								alliance_description = ' . $this->db->escapeString($this->description, true) . ',
								`mod` = ' . $this->db->escapeString($this->motd) . ',
								img_src = ' . $this->db->escapeString($this->imgSrc) . ',
								alliance_kills = ' . $this->db->escapeNumber($this->kills) . ',
								alliance_deaths = ' . $this->db->escapeNumber($this->deaths) . ',
								discord_server = ' . $this->db->escapeString($this->discordServer, true) . ',
								discord_channel = ' . $this->db->escapeString($this->discordChannel, true) . ',
								flagship_id = ' . $this->db->escapeNumber($this->flagshipID) . ',
								leader_id = ' . $this->db->escapeNumber($this->leaderID) . '
							WHERE ' . $this->SQL);
	}

	/**
	 * Returns the members of this alliance as an array of SmrPlayer objects.
	 */
	public function getMembers() : array {
		return SmrPlayer::getAlliancePlayers($this->getGameID(), $this->getAllianceID());
	}

	public function getMemberIDs() : array {
		if (!isset($this->memberList)) {
			$dbResult = $this->db->read('SELECT account_id FROM player WHERE ' . $this->SQL);

			//we have the list of players put them in an array now
			$this->memberList = array();
			foreach ($dbResult->records() as $dbRecord) {
				$this->memberList[] = $dbRecord->getInt('account_id');
			}
		}
		return $this->memberList;
	}

	public function getActiveIDs() : array {
		$activeIDs = array();

		$dbResult = $this->db->read('SELECT account_id
						FROM active_session
						JOIN player USING(account_id, game_id)
						WHERE '.$this->SQL . ' AND last_accessed >= ' . $this->db->escapeNumber(Smr\Epoch::time() - 600));

		foreach ($dbResult->records() as $dbRecord) {
			$activeIDs[] = $dbRecord->getInt('account_id');
		}

		return $activeIDs;
	}

	/**
	 * Return all planets owned by members of this alliance.
	 */
	public function getPlanets() : array {
		$dbResult = $this->db->read('SELECT planet.*
			FROM player
			JOIN planet ON player.game_id = planet.game_id AND player.account_id = planet.owner_id
			WHERE player.game_id=' . $this->db->escapeNumber($this->gameID) . '
			AND player.alliance_id=' . $this->db->escapeNumber($this->allianceID) . '
			ORDER BY planet.sector_id
		');
		$planets = array();
		foreach ($dbResult->records() as $dbRecord) {
			$planets[] = SmrPlanet::getPlanet($this->gameID, $dbRecord->getInt('sector_id'), false, $dbRecord);
		}
		return $planets;
	}

	/**
	 * Return array of sector_id for sectors in the alliance seedlist.
	 */
	public function getSeedlist() : array {
		if (!isset($this->seedlist)) {
			$dbResult = $this->db->read('SELECT sector_id FROM alliance_has_seedlist WHERE ' . $this->SQL);
			$this->seedlist = array();
			foreach ($dbResult->records() as $dbRecord) {
				$this->seedlist[] = $dbRecord->getInt('sector_id');
			}
		}
		return $this->seedlist;
	}

	/**
	 * Is the given sector in the alliance seedlist?
	 */
	public function isInSeedlist(SmrSector $sector) : bool {
		return in_array($sector->getSectorID(), $this->getSeedlist());
	}

	/**
	 * Create the default roles for this alliance.
	 * This should only be called once after the alliance is created.
	 */
	public function createDefaultRoles(string $newMemberPermission = 'basic') : void {
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
		$db->write('INSERT INTO alliance_has_roles (alliance_id, game_id, role_id, role, with_per_day, remove_member, change_pass, change_mod, change_roles, planet_access, exempt_with, mb_messages, send_alliance_msg, op_leader, view_bonds) ' .
			'VALUES (' . $db->escapeNumber($this->getAllianceID()) . ', ' . $db->escapeNumber($this->getGameID()) . ', ' . $db->escapeNumber(ALLIANCE_ROLE_LEADER) . ', \'Leader\', ' . $db->escapeNumber($withPerDay) . ', ' . $db->escapeBoolean($removeMember) . ', ' . $db->escapeBoolean($changePass) . ', ' . $db->escapeBoolean($changeMOD) . ', ' . $db->escapeBoolean($changeRoles) . ', ' . $db->escapeBoolean($planetAccess) . ', ' . $db->escapeBoolean($exemptWith) . ', ' . $db->escapeBoolean($mbMessages) . ', ' . $db->escapeBoolean($sendAllMsg) . ', ' . $db->escapeBoolean($opLeader) . ', ' . $db->escapeBoolean($viewBonds) . ')');

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
		$db->write('INSERT INTO alliance_has_roles (alliance_id, game_id, role_id, role, with_per_day, remove_member, change_pass, change_mod, change_roles, planet_access, exempt_with, mb_messages, send_alliance_msg, op_leader, view_bonds) ' .
					'VALUES (' . $db->escapeNumber($this->getAllianceID()) . ', ' . $db->escapeNumber($this->getGameID()) . ', ' . $db->escapeNumber(ALLIANCE_ROLE_NEW_MEMBER) . ', \'New Member\', ' . $db->escapeNumber($withPerDay) . ', ' . $db->escapeBoolean($removeMember) . ', ' . $db->escapeBoolean($changePass) . ', ' . $db->escapeBoolean($changeMOD) . ', ' . $db->escapeBoolean($changeRoles) . ', ' . $db->escapeBoolean($planetAccess) . ', ' . $db->escapeBoolean($exemptWith) . ', ' . $db->escapeBoolean($mbMessages) . ', ' . $db->escapeBoolean($sendAllMsg) . ', ' . $db->escapeBoolean($opLeader) . ', ' . $db->escapeBoolean($viewBonds) . ')');

	}

}
