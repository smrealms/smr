<?php
require_once(get_file_loc('SmrAccount.class.inc'));
require_once(get_file_loc('SmrPlayer.class.inc'));

class SmrAlliance {
	protected static $CACHE_ALLIANCES = array();

	protected $db;

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
	protected $ircChannel;

	protected $memberList;

	public static function &getAlliance($allianceID,$gameID,$forceUpdate = false) {
		if($forceUpdate || !isset(self::$CACHE_ALLIANCES[$gameID][$allianceID])) {
			self::$CACHE_ALLIANCES[$gameID][$allianceID] = new SmrAlliance($gameID,$allianceID);
		}
		return self::$CACHE_ALLIANCES[$gameID][$allianceID];
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

	function __construct($gameID, $allianceID) {
		$this->db = new SmrMySqlDatabase();

		$this->allianceID = $allianceID;
		$this->gameID = $gameID;

		if ($allianceID != 0) {
			$this->db->query('SELECT * FROM alliance
								WHERE game_id = '.$this->db->escapeNumber($this->gameID).'
								AND alliance_id = '.$this->db->escapeNumber($this->allianceID));
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

	public function getAllianceName() {
		return $this->allianceName;
	}

	public function getGameID() {
		return $this->gameID;
	}

	public function getLeaderID() {
		return $this->leaderID;
	}

	public function &getLeader() {
		return SmrPlayer::getPlayer($this->getLeaderID(),$this->getGameID());
	}

	public function getDescription() {
		return $this->description;
	}

	public function getIrcChannel() {
		if(!isset($this->ircChannel)) {
			$this->db->query('SELECT channel FROM irc_alliance_has_channel WHERE alliance_id = ' . $this->db->escapeNumber($this->getAllianceID()) .' AND game_id = ' . $this->db->escapeNumber($this->getGameID()) . ' LIMIT 1;');
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
			$this->db->query('DELETE FROM irc_alliance_has_channel WHERE alliance_id = ' . $this->db->escapeNumber($this->getAllianceID()) . ' AND game_id = ' . $this->db->escapeNumber($this->getGameID()) . ';');
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

	public function setAllianceDescription($description) {
		$description = word_filter($description);
		if($description==$this->description) {
			return;
		}
		global $player,$account;
		$boxDescription = 'Alliance '.$this->getAllianceName().'('.$this->getAllianceID().') had their description changed to:'.EOL.EOL.$description;
		if(is_object($player)) {
			$player->sendMessageToBox(BOX_ALLIANCE_DESCRIPTIONS,$boxDescription);
		}
		else {
			$account->sendMessageToBox(BOX_ALLIANCE_DESCRIPTIONS,$boxDescription);
		}
		$this->description=nl2br(htmlspecialchars($description));
	}

	public function canJoinAlliance(SmrPlayer &$player) {
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
			$this->db->query('SELECT status FROM player_joined_alliance WHERE account_id='.$this->db->escapeNumber($player->getAccountID()).' AND game_id='.$this->db->escapeNumber($player->getGameID()).' AND alliance_id='.$this->db->escapeNumber($this->getAllianceID()));
			if($this->db->nextRecord()) {
				if($this->db->getField('status')=='NEWBIE') {
					return true;
				}
			}
			$this->db->query('SELECT COUNT(*) AS num_orig_vets
							FROM player_joined_alliance
							JOIN player USING (alliance_id, game_id)
							AND game_id='.$this->db->escapeNumber($player->getGameID()).'
							AND alliance_id='.$this->db->escapeNumber($this->getAllianceID()).'
							AND status=\'VETERAN\'');
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
								leader_id = '.$this->db->escapeNumber($this->leaderID).'
							WHERE alliance_id = '.$this->db->escapeNumber($this->allianceID).'
								AND game_id = '.$this->db->escapeNumber($this->gameID));
	}

	function getMemberIDs() {
		if(!isset($this->memberList)) {
			$this->db->query('SELECT * FROM player WHERE alliance_id = '.$this->db->escapeNumber($this->allianceID).' AND game_id = '.$this->db->escapeNumber($this->gameID));

			//we have the list of players put them in an array now
			$this->memberList = array();
			while ($this->db->nextRecord()) {
				$this->memberList[] = $this->db->getInt('account_id');
			}
		}
		return $this->memberList;
	}
}

?>