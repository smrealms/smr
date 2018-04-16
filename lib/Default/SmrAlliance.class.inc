<?php
require_once(get_file_loc('SmrAccount.class.inc'));
require_once(get_file_loc('SmrPlayer.class.inc'));

class SmrAlliance {
	protected static $CACHE_ALLIANCES = array();

	protected $db;
	protected $SQL;

	protected $gameID;
	protected $allianceID;
	protected $allianceName;
	protected $description;
	protected $password;
	protected $leaderID;
	protected $account;
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

	public static function &getAlliance($allianceID,$gameID,$forceUpdate = false) {
		if($forceUpdate || !isset(self::$CACHE_ALLIANCES[$gameID][$allianceID])) {
			self::$CACHE_ALLIANCES[$gameID][$allianceID] = new SmrAlliance($allianceID, $gameID);
		}
		return self::$CACHE_ALLIANCES[$gameID][$allianceID];
	}

	public static function getAllianceByDiscordChannel($channel, $forceUpdate=false) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT alliance_id, game_id FROM alliance JOIN game USING(game_id) WHERE discord_channel = '.$db->escapeString($channel).' AND game.end_date > '.$db->escapeNumber(time()).' ORDER BY game_id DESC LIMIT 1');
		if ($db->nextRecord()) {
			return self::getAlliance($db->getInt('alliance_id'), $db->getInt('game_id'), $forceUpdate);
		} else {
			return null;
		}
	}

	public static function &getAllianceByIrcChannel($channel,$forceUpdate = false) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT alliance_id, game_id FROM irc_alliance_has_channel WHERE channel = '.$db->escapeString($channel).' LIMIT 1');
		if($db->nextRecord()) {
			return self::getAlliance($db->getInt('alliance_id'), $db->getInt('game_id'), $forceUpdate);
		}
		$return = null;
		return $return;
	}

	protected function __construct($allianceID, $gameID) {
		$this->db = new SmrMySqlDatabase();

		$this->allianceID = $allianceID;
		$this->gameID = $gameID;
		$this->SQL = 'alliance_id=' . $this->db->escapeNumber($allianceID) . ' AND game_id=' . $this->db->escapeNumber($gameID);

		if ($allianceID != 0) {
			$this->db->query('SELECT * FROM alliance WHERE ' . $this->SQL);
			$this->db->nextRecord();
			$this->allianceName		= stripslashes($this->db->getField('alliance_name'));
			$this->password			= stripslashes($this->db->getField('alliance_password'));
			$this->description		= strip_tags(stripslashes($this->db->getField('alliance_description')));
			$this->leaderID			= $this->db->getInt('leader_id');
			$this->account			= $this->db->getInt('alliance_account');
			$this->kills			= $this->db->getInt('alliance_kills');
			$this->deaths			= $this->db->getInt('alliance_deaths');
			$this->motd				= strip_tags(stripslashes($this->db->getField('mod')));
			$this->imgSrc			= $this->db->getField('img_src');
			$this->discordServer	= $this->db->getField('discord_server');
			$this->discordChannel	= $this->db->getField('discord_channel');
			$this->flagshipID = $this->db->getInt('flagship_id');

			if (empty($this->kills)) {
				$this->kills = 0;
			}
			if (empty($this->deaths)) {
				$this->deaths = 0;
			}
		}
	}

	public function getAllianceID() {
		return $this->allianceID;
	}

	public function getAllianceName($linked=false, $includeAllianceID=false) {
		$name = $this->allianceName;
		if ($includeAllianceID) {
			$name .= ' (' . $this->allianceID . ')';
		}
		if($linked === true && !$this->hasDisbanded()) {
			return create_link(Globals::getAllianceRosterHREF($this->getAllianceID()), $name);
		}
		return $name;
	}

	public function getGameID() {
		return $this->gameID;
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

	public function &getLeader() {
		return SmrPlayer::getPlayer($this->getLeaderID(),$this->getGameID());
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
		if(!isset($this->ircChannel)) {
			$this->db->query('SELECT channel FROM irc_alliance_has_channel WHERE ' . $this->SQL);
			if($this->db->nextRecord()) {
				$this->ircChannel = $this->db->getField('channel');
			}
			else {
				$this->ircChannel = '';
			}
		}
		return $this->ircChannel;
	}

	public function setIrcChannel($ircChannel) {
		if($this->ircChannel == $ircChannel) {
			return;
		}
		if(strlen($ircChannel)>0 && $ircChannel != '#') {
			if($ircChannel[0] != '#') {
				$ircChannel = '#' . $ircChannel;
			}
			if ($ircChannel == '#smr' || $ircChannel == '#smr-bar') {
				create_error('Please enter a valid irc channel for your alliance.');
			}

			$this->db->query('REPLACE INTO irc_alliance_has_channel (channel,alliance_id,game_id) values (' . $this->db->escapeString($ircChannel) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ',' . $this->db->escapeNumber($this->getGameID()) . ');');
		}
		else {
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
		if(preg_match('/"/',$url)) {
			throw new Exception('Tried to set an image url with ": ' . $url);
		}
		$this->imgSrc = htmlspecialchars($url);
	}

	public function getAccount() {
		return $this->account;
	}

	public function setAccount($credits) {
		$this->account = $credits;
	}

	public function getMotD() {
		return $this->motd;
	}

	public function setMotD($motd) {
		$this->motd = nl2br(htmlspecialchars($motd));
	}

	public function getPassword() {
		return $this->password;
	}

	public function setPassword($password) {
		$this->password = $password;
	}

	public function getKills() {
		return $this->kills;
	}

	public function getDeaths() {
		return $this->deaths;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setAllianceDescription($description) {
		$description = word_filter($description);
		if($description==$this->description) {
			return;
		}
		global $player,$account;
		$boxDescription = 'Alliance '.$this->getAllianceName(false, true).' had their description changed to:'.EOL.EOL.$description;
		if(is_object($player)) {
			$player->sendMessageToBox(BOX_ALLIANCE_DESCRIPTIONS,$boxDescription);
		}
		else {
			$account->sendMessageToBox(BOX_ALLIANCE_DESCRIPTIONS,$boxDescription);
		}
		$this->description=nl2br(htmlspecialchars($description));
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

	public function canJoinAlliance(SmrPlayer $player) {
		if($player->hasAlliance()) {
			return false;
		}
		if($this->getPassword()=='*') {
			return 'This alliance is not currently accepting new recruits.';
		}
		if($player->getAllianceJoinable() > TIME) {
			return 'You cannot join another alliance for '.format_time($player->getAllianceJoinable()-TIME).'.';
		}
		if($this->getNumMembers()<Globals::getAllianceMaxPlayers($this->getGameID())) {
			if(!$player->getAccount()->isVeteran()) {
				return true;
			}
			$maxVets = Globals::getAllianceMaxVets($this->getGameID());
			if($this->getNumMembers()<$maxVets) {
				return true;
			}
			$this->db->query('SELECT status FROM player_joined_alliance WHERE account_id='.$this->db->escapeNumber($player->getAccountID()).' AND '.$this->SQL);
			if($this->db->nextRecord()) {
				if($this->db->getField('status')=='NEWBIE') {
					return true;
				}
			}
			$this->db->query('SELECT COUNT(*) AS num_orig_vets
							FROM player_joined_alliance
							JOIN player USING (account_id, alliance_id, game_id)
							WHERE ' . $this->SQL . ' AND status=\'VETERAN\'');
			if(!$this->db->nextRecord() || $this->db->getInt('num_orig_vets') < $maxVets) {
				return true;
			}
		}
		return 'There is not currently enough room for you in this alliance.';
	}

	public function getNumVeterans() {
		$numVeterans=0;
		foreach($this->getMemberIDs() as $memberID) {
			if(SmrAccount::getAccount($memberID)->isVeteran()) {
				$numVeterans++;
			}
		}
		return $numVeterans;
	}

	public function getNumMembers() {
		return count($this->getMemberIDs());
	}

	function update() {
		$this->db->query('UPDATE alliance SET alliance_password = ' . $this->db->escapeString($this->password) . ',
								alliance_account = '.$this->db->escapeNumber($this->account).',
								alliance_description = ' . $this->db->escapeString($this->description) . ',
								`mod` = ' . $this->db->escapeString($this->motd) . ',
								img_src = ' . $this->db->escapeString($this->imgSrc) . ',
								alliance_kills = '.$this->db->escapeNumber($this->kills).',
								alliance_deaths = '.$this->db->escapeNumber($this->deaths).',
								discord_server = '.$this->db->escapeString($this->discordServer).',
								discord_channel = '.$this->db->escapeString($this->discordChannel).',
								flagship_id = '.$this->db->escapeNumber($this->flagshipID).',
								leader_id = '.$this->db->escapeNumber($this->leaderID).'
							WHERE ' . $this->SQL);
	}

	/**
	 * Returns the members of this alliance as an array of SmrPlayer objects.
	 */
	function getMembers() {
		return SmrPlayer::getAlliancePlayers($this->getGameID(), $this->getAllianceID());
	}

	function getMemberIDs() {
		if(!isset($this->memberList)) {
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
						WHERE '.$this->SQL.' AND last_accessed >= ' . $this->db->escapeNumber(TIME - 600));
		
		while($this->db->nextRecord()) {
			$activeIDs[] = $this->db->getInt('account_id');
		}
		
		return $activeIDs;
	}

	/**
	 * Return all planets owned by members of this alliance.
	 */
	public function getPlanets() {
		$this->db->query('SELECT planet.sector_id
			FROM player
			JOIN planet ON player.game_id = planet.game_id AND player.account_id = planet.owner_id
			WHERE player.game_id=' . $this->db->escapeNumber($this->gameID) . '
			AND player.alliance_id=' . $this->db->escapeNumber($this->allianceID) . '
			ORDER BY planet.sector_id
		');
		$planets = array();
		while ($this->db->nextRecord()) {
			$planets[] = SmrPlanet::getPlanet($this->gameID, $this->db->getInt('sector_id'));
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

}
