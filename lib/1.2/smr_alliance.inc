<?php

// include file with database class
require_once(get_file_loc('SmrMySqlDatabase.class.inc'));

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

			$this->query("SELECT * FROM alliance WHERE game_id = $this->game_id AND " .
													  "alliance_id = $this->alliance_id");
			$this->next_record();
			$this->alliance_name	= stripslashes($this->f("alliance_name"));
			$this->password			= stripslashes($this->f("alliance_password"));
			$this->description		= strip_tags(stripslashes($this->f("alliance_description")));
			$this->leader_id		= $this->f("leader_id");
			$this->account			= $this->f("alliance_account");
			$this->kills			= $this->f("alliance_kills");
			$this->deaths			= $this->f("alliance_deaths");
			$this->mod				= strip_tags(stripslashes($this->f("mod")));
			$this->img_src			= $this->f("img_src");

			if (empty($this->kills))
				$this->kills = 0;
			if (empty($this->deaths))
				$this->deaths = 0;

		}

	}

	function update() {

		$this->query("UPDATE alliance SET alliance_password = " . format_string($this->password, false) . ", " .
										 "alliance_account = $this->account, " .
										 "alliance_description = " . nl2br(format_string($this->description, true)) . ", " .
										 "`mod` = " . nl2br(format_string($this->mod, true)) . ", " .
										 "img_src = " . format_string($this->img_src, False) . ", " .
										 "alliance_kills = $this->kills, " .
										 "alliance_deaths = $this->deaths, " .
										 "leader_id = $this->leader_id " .
									 "WHERE alliance_id = $this->alliance_id AND " .
									 	   "game_id = $this->game_id");
	}

	function return_member_ids() {

		$this->query("SELECT * FROM player WHERE alliance_id = $this->alliance_id AND game_id = $this->game_id");

		//we have the list of players put them in an array now
		$member_list = array();
		while ($this->next_record())
			$member_list[] = $this->f("account_id");

		return $member_list;

	}

}

?>