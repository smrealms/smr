<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

//get Spock's exp under me :)
$db->query("UPDATE player SET experience = 0 WHERE account_id = 1 AND game_id = $player->game_id");
$db->query("UPDATE player SET experience = 50000000 WHERE player_name = 'Azool' AND game_id = $player->game_id");
if ($var["func"] == "Map") {

	$account_id = $player->account_id;
	$game_id = $player->game_id;
	// delete all entries from the player_visited_sector/port table
	$db->query("DELETE FROM player_visited_sector WHERE account_id = $account_id AND game_id = $game_id");
	$db->query("DELETE FROM player_visited_port WHERE account_id = $account_id AND game_id = $game_id");
	$current_sector_id = 0;
	$current_time = time();

	// add port infos
	$db->query("SELECT * FROM port_has_goods WHERE game_id = $game_id ORDER BY sector_id, good_id");
	while ($db->next_record()) {

		$sector_id = $db->f("sector_id");
		$good_id = $db->f("good_id");
		$transaction = $db->f("transaction");

		if ($sector_id != $current_sector_id) {

			// save to db (not the inital value)
			$db2 = new SmrMySqlDatabase();
			if ($current_sector_id != 0)
				$db2->query("INSERT INTO player_visited_port (account_id, game_id, sector_id, visited, port_info) " .
							"VALUES($account_id, $game_id, $current_sector_id, $current_time, '" . addslashes(serialize($port_info)). "')");

			// reset variables
			$current_sector_id = $sector_id;
			$port_info = array();

		}

		// add to port info array
		$port_info[$good_id] = $transaction;

	}

} elseif ($var["func"] == "Money")
	$db->query("UPDATE player SET credits = 50000000 WHERE game_id = $player->game_id AND account_id = $player->account_id");
elseif ($var["func"] == "Ship" && $_REQUEST['ship_id'] <= 75 && $_REQUEST['ship_id'] != 68) {
	$ship_id = $_REQUEST['ship_id'];
	$db->query("UPDATE player SET ship_type_id = $ship_id WHERE game_id = $player->game_id AND account_id = $player->account_id");
	//check for more weapons than allowed
	$db->query("SELECT * FROM ship_type WHERE ship_type_id = $ship_id");
	$db->next_record();
	$max_weps = $db->f("hardpoint");
	$speed = $db->f("speed");
	$db->query("SELECT * FROM ship_has_weapon WHERE account_id = $player->account_id AND game_id = $player->game_id");
	if ($db->nf() > $max_weps) {
		$extra = $db->nf() - $max_weps;
		for ($i=1; $i <= $extra; $i++) {
			$db->query("SELECT * FROM ship_has_weapon WHERE account_id = $player->account_id ORDER BY order_id DESC");
			$db->next_record();
			$order_id = $db->f("order_id");
			$db->query("DELETE FROM ship_has_weapon WHERE account_id = $player->account_id AND order_id = $order_id");
		}
	}
	//now adapt turns
	$turns = $player->turns * ($speed / $ship->speed);
	if ($turns > (400 * $player->game_speed)) $turns = 400 * $player->game_speed;
	$player->turns = $turns;
	$player->update();
	//now make sure they don't have extra hardware
	$db->query("DELETE FROM ship_is_cloaked WHERE account_id = $player->account_id AND game_id = $player->game_id");
	$db->query("DELETE FROM ship_has_illusion WHERE account_id = $player->account_id AND game_id = $player->game_id");
	$container = array();
	$container["url"] = "beta_func_proc.php";
	$container["func"] = "Uno";
	forward($container);	
	
} elseif ($var["func"] == "Weapon") {
	$weapon_id = $_REQUEST['weapon_id'];
	$amount = $_REQUEST['amount'];
	$db->query("SELECT * FROM ship_has_weapon WHERE game_id = $player->game_id AND account_id = $player->account_id ORDER BY order_id DESC");
	if ($db->next_record())
		$next = $db->f("order_id") + 1;
	else
		$next = 1;
	for ($i = 1; $i <= $amount; $i++) {
		$db->query("INSERT INTO ship_has_weapon (account_id, game_id, order_id, weapon_type_id) VALUES " .
					"($player->account_id, $player->game_id, $next, $weapon_id)");
		$next += 1;
	}

} elseif ($var["func"] == "Uno") {

	$db->query("SELECT * FROM ship_type_support_hardware WHERE ship_type_id = $ship->ship_type_id");
	$db2 = new SmrMySqlDatabase();
	while ($db->next_record()) {
		$hardware_id = $db->f("hardware_type_id");
		$amount = $db->f("max_amount");
		$db2->query("REPLACE INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) VALUES " .
				"($player->account_id, $player->game_id, $hardware_id, $amount, $amount)");
	}

} elseif ($var["func"] == "Warp") {
	$sector_to = $_REQUEST['sector_to'];
	$db->query("UPDATE player SET sector_id = '$sector_to' WHERE game_id = $player->game_id AND account_id = $player->account_id");
} elseif ($var["func"] == "Exp") {
	$exp = $_REQUEST['exp'];
	if ($exp > 500000) $exp = 500000;
	$db->query("UPDATE player SET experience = '$exp' WHERE game_id = $player->game_id AND account_id = $player->account_id");
} elseif ($var["func"] == "Align"){
	$align = $_REQUEST['align'];
	if($align > 500) $align=500;
	else if($align<-500) $align=-500;
	$db->query("UPDATE player SET alignment = '$align' WHERE game_id = $player->game_id AND account_id = $player->account_id");
} elseif ($var["func"] == "Kills") {
	$kills = $_REQUEST['kills'];
	$db->query("UPDATE account_has_stats SET kills = '$kills' WHERE account_id = $player->account_id");
} elseif ($var["func"] == "Traded_XP") {
	$traded_xp = $_REQUEST['traded_xp'];
	$db->query("UPDATE account_has_stats SET experience_traded = '$traded_xp' WHERE account_id = $player->account_id");
} elseif ($var["func"] == "RemWeapon")
	$db->query("DELETE FROM ship_has_weapon WHERE game_id = $player->game_id AND account_id = $player->account_id");
elseif ($var["func"] == "Hard_add") {
	$type_hard = $_REQUEST['type_hard'];
	$amount_hard = $_REQUEST['amount_hard'];
	$db->query("REPLACE INTO ship_has_hardware (account_id,game_id,hardware_type_id,amount,old_amount) VALUES ($player->account_id,$player->game_id,$type_hard,'$amount_hard','$amount_hard')");
} elseif ($var["func"] == "Relations") {
	$amount = $_REQUEST['amount'];
	$race = $_REQUEST['race'];
	$db->query("UPDATE player_has_relation SET relation = '$amount' WHERE race_id = $race AND account_id = $player->account_id AND game_id = $player->game_id");
} elseif ($var["func"] == "Race_Relations") {
	$amount = $_REQUEST['amount'];
	$race = $_REQUEST['race'];
	$db->query("UPDATE race_has_relation SET relation = '$amount' WHERE race_id_1 = $player->race_id AND race_id_2 = $race AND game_id = $player->game_id");
	$db->query("UPDATE race_has_relation SET relation = '$amount' WHERE race_id_1 = $race AND race_id_2 = $player->race_id AND game_id = $player->game_id");
}
$container["url"] = "skeleton.php";
$container["body"] = "beta_functions_lite.php";
forward($container);

?>