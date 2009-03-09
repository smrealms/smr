<?

// include file with database class
require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');

class SMR_ALLIANCE extends SmrMySqlDatabase {

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

	function SMR_ALLIANCE($alliance_id, $game_id) {

		$this->alliance_id = $alliance_id;
		$this->game_id = $game_id;

		if ($alliance_id != 0) {

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
	
	public function setAllianceDescription($description)
	{
		$description = word_filter($description);
		global $player,$account;
		$boxDescription = 'Alliance '.$this->getAllianceName().'('.$this->getAllianceID().') had their description changed to:<br /><br />'.$description;
		if(is_object($player))
			$player->sendMessageToBox(BOX_ALLIANCE_DESCRIPTIONS,$boxDescription);
		else
			$account->sendMessageToBox(BOX_ALLIANCE_DESCRIPTIONS,$boxDescription);
		$this->description=$description;
	}

	function update()
	{

		$this->query('UPDATE alliance SET alliance_password = ' . $this->escape_string($this->password, false) . ', ' .
										 'alliance_account = '.$this->account.', ' .
										 'alliance_description = ' . nl2br($this->escape_string($this->description, true)) . ', ' .
										 '`mod` = ' . nl2br($this->escape_string($this->mod, true)) . ', ' .
										 'img_src = ' . $this->escape_string($this->img_src, False) . ', ' .
										 'alliance_kills = '.$this->kills.', ' .
										 'alliance_deaths = '.$this->deaths.', ' .
										 'leader_id = '.$this->leader_id.' ' .
									 'WHERE alliance_id = '.$this->alliance_id.' AND ' .
									 	   'game_id = '.$this->game_id);
	}

	function return_member_ids() {

		$this->query('SELECT * FROM player WHERE alliance_id = '.$this->alliance_id.' AND game_id = '.$this->game_id);

		//we have the list of players put them in an array now
		$member_list = array();
		while ($this->nextRecord())
			$member_list[] = $this->getField('account_id');

		return $member_list;

	}

}

?>