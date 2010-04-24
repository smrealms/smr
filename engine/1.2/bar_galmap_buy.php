<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

$num_creds = $account->get_credits();
if ($num_creds < 2) {
	
	print_error("You don't have enough SMR Credits.  Donate money to SMR to gain SMR Credits!");
	return;
	
}

//gal map buy
if (isset($var["process"])) {
	
	$gal_id = $_REQUEST["gal_id"];
	if ($gal_id == 0) {
		
		print_error("You must select a galaxy to buy the map of!");
		return;
		
	}
	//take money
	$account->set_credits($num_creds - 2);
	//now give maps
	$account_id = $player->account_id;
	$game_id = $player->game_id;
	//get start sector
	$db->query("SELECT * FROM sector WHERE galaxy_id = $gal_id AND game_id = $player->game_id ORDER BY sector_id LIMIT 1");
	$db->next_record();
	$low = $db->f("sector_id");
	//get end sector
	$db->query("SELECT * FROM sector WHERE galaxy_id = $gal_id AND game_id = $player->game_id ORDER BY sector_id DESC LIMIT 1");
	$db->next_record();
	$high = $db->f("sector_id");

	// Have they already got this map? (Are there any unexplored sectors?
	$db->query("SELECT * FROM player_visited_sector WHERE sector_id >= $low AND sector_id <= $high AND account_id = $account_id AND game_id = $game_id LIMIT 1");
	if(!$db->next_record()) {
		print_error("You already have maps of this galaxy!");
		return;
	}
	
	// delete all entries from the player_visited_sector/port table
	$db->query("DELETE FROM player_visited_sector WHERE sector_id >= $low AND sector_id <= $high AND account_id = $account_id AND game_id = $game_id");
	$db->query("DELETE FROM player_visited_port WHERE sector_id >= $low AND sector_id <= $high AND account_id = $account_id AND game_id = $game_id");
	//start section
	$current_sector_id = $low - 1;
	$current_time = time();
	$db2 = new SmrMySqlDatabase();
	// add port infos
	$db->query("SELECT * FROM port_has_goods WHERE game_id = $game_id AND sector_id <= $high AND sector_id >= $low ORDER BY sector_id, good_id");
	while ($db->next_record()) {

		$sector_id = $db->f("sector_id");
		$good_id = $db->f("good_id");
		$transaction = $db->f("transaction");

		if ($sector_id != $current_sector_id) {

			// save to db (not the inital value)
			if ($current_sector_id != $low - 1)
				$db2->query("REPLACE INTO player_visited_port (account_id, game_id, sector_id, visited, port_info) " .
							"VALUES($account_id, $game_id, $current_sector_id, $current_time, '" . addslashes(serialize($port_info)). "')");

			// reset variables
			$current_sector_id = $sector_id;
			$port_info = array();

		}

		// add to port info array
		$port_info[$good_id] = $transaction;

	}
	//insert the last port
	$db2->query("REPLACE INTO player_visited_port (account_id, game_id, sector_id, visited, port_info) " .
							"VALUES($account_id, $game_id, $current_sector_id, $current_time, '" . addslashes(serialize($port_info)). "')");
	//offer another drink and such
	print("<div align=center>Galaxy Info has been added.  Enjoy!</div><br>");
	include(get_file_loc("bar_opening.php"));
	
} else {
	
	//find what gal they want
	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "bar_main.php";
	$container["script"] = "bar_galmap_buy.php";
	$container["process"] = "yes";
	print("<div align=center>What galaxy do you want info on?<br>");
	print_form($container);
	print("<select type=select name=gal_id>");
	print("<option value=0>[Select a galaxy]</option>");
	$db->query("SELECT galaxy_id FROM sector WHERE game_id = $player->game_id GROUP BY galaxy_id ORDER BY galaxy_id ASC");
	$db2 = new SmrMySqlDatabase();
	while ($db->next_record()) {
		
		$gal_id = $db->f("galaxy_id");
		$db2->query("SELECT * FROM galaxy WHERE galaxy_id = $gal_id");
		if ($db2->next_record()) print("<option value=$gal_id>" . $db2->f("galaxy_name") . "</option>");
		
	}
	print("</select><br>");
	print_submit("Buy the map");
	print("</form></div>");
	
}

?>
