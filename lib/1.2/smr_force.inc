<?php

// include file with database class
//require_once(LIB . "SmrMySqlDatabase.inc");
require_once(get_file_loc('SmrMySqlDatabase.class.inc'));

class SMR_FORCE extends SmrMySqlDatabase {

	var $owner_id;
	var $sector_id;
	var $game_id;
	var $combat_drones;
	var $scout_drones;
	var $mines;
	var $expire;

	function SMR_FORCE($owner_id, $sector_id, $game_id) {

		$this->owner_id		= $owner_id;
		$this->sector_id	= $sector_id;
		$this->game_id		= $game_id;

		// get forces from db
		$this->query("SELECT * FROM sector_has_forces WHERE game_id = $this->game_id AND " .
														   "sector_id = $this->sector_id AND " .
														   "owner_id = $this->owner_id AND " .
														   "expire_time >= '" . time() . "'");
		$this->next_record();
		$this->combat_drones	= $this->f("combat_drones");
		$this->scout_drones		= $this->f("scout_drones");
		$this->mines			= $this->f("mines");
		$this->expire			= $this->f("expire_time");

		// we need to initialize empty variables
		if (empty($this->combat_drones)) $this->combat_drones = 0;
		if (empty($this->scout_drones)) $this->scout_drones = 0;
		if (empty($this->mines)) $this->mines = 0;
		if (empty($this->expire)) $this->expire = 0;

		// let forces expire (simply let all expire) (update refreshing first)
		$db = new SmrMySqlDatabase();
		$db2 = new SmrMySqlDatabase();
		$time = time();
		$db->query("SELECT * FROM force_refresh WHERE refresh_at <= $time");
		while ($db->next_record()) {
			
			$total = $db->f("num_forces");
			$days = ceil($total / 10);
			if ($days > 5) $days = 5;
			$ex_time = $db->f("refresh_at") + ($days * 86400);
			$db2->query("UPDATE sector_has_forces SET expire_time = $ex_time WHERE game_id = $this->game_id AND sector_id = " . $db->f("sector_id") . " AND owner_id = " . $db->f("owner_id"));
			$db2->query("DELETE FROM force_refresh WHERE game_id = $this->game_id " .
					"AND sector_id = " . $db->f("sector_id") . " AND owner_id = " . $db->f("owner_id"));
						
		}
		$this->query("DELETE FROM sector_has_forces WHERE expire_time < '" . time() . "'");
	}

	function update() {

		if ($this->mines == 0 && $this->combat_drones == 0 && $this->scout_drones == 0) {
			$this->query("DELETE FROM sector_has_forces " .
						 "WHERE game_id = $this->game_id AND " .
							   "sector_id = $this->sector_id AND " .
							   "owner_id = $this->owner_id");
		} else {
			$this->query("REPLACE INTO sector_has_forces (game_id, sector_id, owner_id, combat_drones, scout_drones, mines, expire_time) " .
												  "VALUES($this->game_id, $this->sector_id, $this->owner_id, $this->combat_drones, $this->scout_drones, $this->mines, $this->expire)");
		}

	}

}

?>