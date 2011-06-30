<?php
require_once(get_file_loc('SmrAccount.class.inc'));
require_once(get_file_loc('SmrPlayer.class.inc'));

class SmrAlliance
{
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
	
	protected $memberList;

	public static function &getAlliance($allianceID,$gameID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_ALLIANCES[$gameID][$allianceID]))
		{
			self::$CACHE_ALLIANCES[$gameID][$allianceID] = new SmrAlliance($gameID,$allianceID);
		}
		return self::$CACHE_ALLIANCES[$gameID][$allianceID];
	}

	function __construct($gameID, $allianceID)
	{
		$this->db = new SmrMySqlDatabase();
		
		$this->allianceID = $allianceID;
		$this->gameID = $gameID;

		if ($allianceID != 0)
		{
			$this->db->query('SELECT * FROM alliance WHERE game_id = '.$this->gameID.' AND ' .
													  'alliance_id = '.$this->allianceID);
			$this->db->nextRecord();
			$this->allianceName		= stripslashes($this->db->getField('alliance_name'));
			$this->password			= stripslashes($this->db->getField('alliance_password'));
			$this->description		= strip_tags(stripslashes($this->db->getField('alliance_description')));
			$this->leaderID			= $this->db->getField('leader_id');
			$this->account			= $this->db->getField('alliance_account');
			$this->kills			= $this->db->getField('alliance_kills');
			$this->deaths			= $this->db->getField('alliance_deaths');
			$this->motd				= strip_tags(stripslashes($this->db->getField('mod')));
			$this->imgSrc			= $this->db->getField('img_src');

			if (empty($this->kills))
				$this->kills = 0;
			if (empty($this->deaths))
				$this->deaths = 0;
		}
	}
	
	public function getAllianceID()
	{
		return $this->allianceID;
	}
	
	public function getAllianceName()
	{
		return $this->allianceName;
	}
	
	public function getGameID()
	{
		return $this->gameID;
	}
	
	public function getLeaderID()
	{
		return $this->leaderID;
	}
	
	public function &getLeader()
	{
		return SmrPlayer::getPlayer($this->getLeaderID(),$this->getGameID());
	}
	
	public function getDescription()
	{
		return $this->description;
	}
	
	public function getImageURL()
	{
		return $this->imgSrc;
	}
	
	public function setImageURL($url)
	{
		$this->imgSrc = $url;
	}
	
	public function getAccount()
	{
		return $this->account;
	}
	
	public function setAccount($credits)
	{
		$this->account = $credits;
	}
	
	public function getMotD()
	{
		return $this->motd;
	}
	
	public function setMotD($motd)
	{
		$this->motd = $motd;
	}
	
	public function getPassword()
	{
		return $this->password;
	}
	
	public function setPassword($password)
	{
		$this->password = $password;
	}
	
	public function setAllianceDescription($description)
	{
		$description = word_filter($description);
		if($description==$this->description)
			return;
		global $player,$account;
		$boxDescription = 'Alliance '.$this->getAllianceName().'('.$this->getAllianceID().') had their description changed to:'.EOL.EOL.$description;
		if(is_object($player))
			$player->sendMessageToBox(BOX_ALLIANCE_DESCRIPTIONS,$boxDescription);
		else
			$account->sendMessageToBox(BOX_ALLIANCE_DESCRIPTIONS,$boxDescription);
		$this->description=$description;
	}
	
	public function canJoinAlliance(SmrPlayer &$player)
	{
		if($player->hasAlliance())
			return false;
		if($this->getPassword()=='*')
			return 'This alliance is not currently accepting new recruits.';
		if($player->getAllianceJoinable() > TIME)
			return 'You cannot join another alliance for '.format_time($player->getAllianceJoinable()-TIME).'.';
		if($this->getNumMembers()<Globals::getAllianceMaxPlayers($this->getGameID()))
		{
			if(!$player->getAccount()->isVeteran())
				return true;
			$maxVets = Globals::getAllianceMaxVets($this->getGameID());
			if($this->getNumMembers()<$maxVets)
				return true;
			$this->db->query('SELECT status FROM player_joined_alliance WHERE account_id='.$this->db->escapeNumber($player->getAccountID()).' AND game_id='.$this->db->escapeNumber($player->getGameID()).' AND alliance_id='.$this->db->escapeNumber($this->getAllianceID()));
			if($this->db->nextRecord())
			{
				if($this->db->getField('status')=='NEWBIE')
					return true;
			}
			$this->db->query('SELECT COUNT(pja.account_id) AS num_orig_vets
							FROM player_joined_alliance pja
							WHERE account_id IN (
								SELECT account_id
								FROM player p
								WHERE p.alliance_id = pja.alliance_id
								AND p.game_id = pja.game_id
							)
							AND game_id='.$this->db->escapeNumber($player->getGameID()).'
							AND alliance_id='.$this->db->escapeNumber($this->getAllianceID()).'
							AND status=\'VETERAN\'');
			if(!$this->db->nextRecord() || $this->db->getField('num_orig_vets') < $maxVets)
				return true;
			
		}
		return 'There is not currently enough room for you in this alliance.';
	}
	
	public function getNumVeterans()
	{
		$numVeterans=0;
		foreach($this->return_member_ids() as $memberID)
		{
			if(SmrAccount::getAccount($memberID)->isVeteran())
				$numVeterans++;
		}
		return $numVeterans;
	}
	
	public function getNumMembers()
	{
		return count($this->return_member_ids());
	}

	function update()
	{

		$this->db->query('UPDATE alliance SET alliance_password = ' . $this->db->escapeString($this->password) . ', ' .
										 'alliance_account = '.$this->account.', ' .
										 'alliance_description = ' . $this->db->escapeString(nl2br($this->description)) . ', ' .
										 '`mod` = ' . $this->db->escapeString(nl2br($this->motd)) . ', ' .
										 'img_src = ' . $this->db->escapeString($this->imgSrc) . ', ' .
										 'alliance_kills = '.$this->kills.', ' .
										 'alliance_deaths = '.$this->deaths.', ' .
										 'leader_id = '.$this->leaderID.' ' .
									 'WHERE alliance_id = '.$this->allianceID.' AND ' .
									 	   'game_id = '.$this->gameID);
	}

	function return_member_ids()
	{
		if(!isset($this->memberList))
		{
			$this->db->query('SELECT * FROM player WHERE alliance_id = '.$this->allianceID.' AND game_id = '.$this->gameID);
	
			//we have the list of players put them in an array now
			$this->memberList = array();
			while ($this->db->nextRecord())
				$this->memberList[] = $this->db->getField('account_id');
		}
		return $this->memberList;
	}
}

?>