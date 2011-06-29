<?php
require_once(get_file_loc('SmrAccount.class.inc'));
require_once(get_file_loc('SmrPlayer.class.inc'));

class SMR_ALLIANCE extends SmrMySqlDatabase
{
	var $game_id;
	var $alliance_id;
	var $alliance_name;
	var $description;
	var $password;
	var $leader_id;
	var $account;
	var $kills;
	var $deaths;
	var $mod;
	var $img_src;
	
	protected $memberList;

	function SMR_ALLIANCE($alliance_id, $game_id)
	{
		$this->alliance_id = $alliance_id;
		$this->game_id = $game_id;

		if ($alliance_id != 0)
		{
			$this->query('SELECT * FROM alliance WHERE game_id = '.$this->game_id.' AND ' .
													  'alliance_id = '.$this->alliance_id);
			$this->nextRecord();
			$this->alliance_name	= stripslashes($this->getField('alliance_name'));
			$this->password			= stripslashes($this->getField('alliance_password'));
			$this->description		= strip_tags(stripslashes($this->getField('alliance_description')));
			$this->leader_id		= $this->getField('leader_id');
			$this->account			= $this->getField('alliance_account');
			$this->kills			= $this->getField('alliance_kills');
			$this->deaths			= $this->getField('alliance_deaths');
			$this->mod				= strip_tags(stripslashes($this->getField('mod')));
			$this->img_src			= $this->getField('img_src');

			if (empty($this->kills))
				$this->kills = 0;
			if (empty($this->deaths))
				$this->deaths = 0;
		}
	}
	
	public function getAllianceID()
	{
		return $this->alliance_id;
	}
	
	public function getAllianceName()
	{
		return $this->alliance_name;
	}
	
	public function getGameID()
	{
		return $this->game_id;
	}
	
	public function getLeaderID()
	{
		return $this->leader_id;
	}
	
	public function &getLeader()
	{
		return SmrPlayer::getPlayer($this->getLeaderID(),$this->getGameID());
	}
	
	public function getDescription()
	{
		return $this->description;
	}
	
	public function getPassword()
	{
		return $this->password;
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
			$this->query('SELECT status FROM player_joined_alliance WHERE account_id='.$this->escapeNumber($player->getAccountID()).' AND game_id='.$this->escapeNumber($player->getGameID()).' AND alliance_id='.$this->escapeNumber($this->getAllianceID()));
			if($this->nextRecord())
			{
				if($this->getField('status')=='NEWBIE')
					return true;
			}
			$this->query('SELECT COUNT(pja.account_id) AS num_orig_vets
							FROM player_joined_alliance pja
							WHERE account_id IN (
								SELECT account_id
								FROM player p
								WHERE p.alliance_id = pja.alliance_id
								AND p.game_id = pja.game_id
							)
							AND game_id='.$this->escapeNumber($player->getGameID()).'
							AND alliance_id='.$this->escapeNumber($this->getAllianceID()).'
							AND status=\'VETERAN\'');
			if(!$this->nextRecord() || $this->getField('num_orig_vets') < $maxVets)
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

		$this->query('UPDATE alliance SET alliance_password = ' . $this->escapeString($this->password) . ', ' .
										 'alliance_account = '.$this->account.', ' .
										 'alliance_description = ' . $this->escapeString(nl2br($this->description)) . ', ' .
										 '`mod` = ' . $this->escapeString(nl2br($this->mod)) . ', ' .
										 'img_src = ' . $this->escapeString($this->img_src) . ', ' .
										 'alliance_kills = '.$this->kills.', ' .
										 'alliance_deaths = '.$this->deaths.', ' .
										 'leader_id = '.$this->leader_id.' ' .
									 'WHERE alliance_id = '.$this->alliance_id.' AND ' .
									 	   'game_id = '.$this->game_id);
	}

	function return_member_ids()
	{
		if(!isset($this->memberList))
		{
			$this->query('SELECT * FROM player WHERE alliance_id = '.$this->alliance_id.' AND game_id = '.$this->game_id);
	
			//we have the list of players put them in an array now
			$this->memberList = array();
			while ($this->nextRecord())
				$this->memberList[] = $this->getField('account_id');
		}
		return $this->memberList;
	}
}

?>