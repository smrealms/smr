<?php

//variables
$db2 = new SmrMySqlDatabase();
$time = time();
//get treaties
$db->query("SELECT * FROM alliance_treaties WHERE (alliance_id_1 = $player->alliance_id OR alliance_id_2 = $player->alliance_id)
			AND game_id = $player->game_id
			AND forces_nap = 1 AND official = 'TRUE'");
$allied[] = $player->alliance_id;
while ($db->next_record()) {
	if ($db->f("alliance_id_1") == $player->alliance_id) $allied[] = $db->f("alliance_id_2");
	else $allied[] = $db->f("alliance_id_1");
}
//populate alliance list
$db->query("SELECT account_id FROM player, sector_has_forces
		WHERE sector_has_forces.sector_id = $player->sector_id
		AND alliance_id IN (" . implode(',',$allied) . ") 
		AND sector_has_forces.game_id = player.game_id
		AND sector_has_forces.owner_id = player.account_id
		AND player.game_id = $player->game_id");	
$list = "(";
while ($db->next_record()) $list .= $db->f("account_id") . ",";
$list .= "0)";
$db->query("SELECT * FROM sector_has_forces WHERE game_id = $player->game_id AND sector_id = " .
		"$player->sector_id AND owner_id IN $list");
while ($db->next_record()) {
	$owner = $db->f("owner_id");
	$total = $db->f("mines") + $db->f("scout_drones") + $db->f("combat_drones");
	//hackish, updated refresh allows single scouts to go 2 days.
	if ($db->f("scout_drones") == 1 && $db->f("combat_drones") == 0 && $db->f("mines") == 0) $total += 10;
	//insert into that force table
	$db2->query("REPLACE INTO force_refresh (game_id, owner_id, sector_id, num_forces, " .
			"refresh_at) VALUES ($player->game_id, $owner, $player->sector_id, " .
			"$total, $time)");
	$time += 2;
}
$message = "[Force Check]"; //this notifies the CS to look for info.
/*$db->query("REPLACE INTO sector_message (account_id, game_id, message) VALUES " .
			"($player->account_id, $player->game_id, '$message')");*/
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "current_sector.php";
$container["msg"] = $message;
forward($container);

?>