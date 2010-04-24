<?php
$action = $_REQUEST['action'];
$race_id = $var["race_id"];
$type = strtoupper($action);
$time = 259200;
//adjust for game speed
//adjust it correctly now
$time = $time / $player->game_speed;
$time = time() + $time;

$db->query("SELECT * FROM race_has_voting " .
		   "WHERE game_id = $player->game_id AND " .
				 "race_id_1 = $player->race_id");
if ($db->nf() > 2)
	create_error("You can't initiate more than 3 votes at a time!");

$db->query("REPLACE INTO race_has_voting " .
		   "(game_id, race_id_1, race_id_2, type, end_time) " .
		   "VALUES($player->game_id, $player->race_id, $race_id, '$type', $time)");

if ($type == "PEACE")
	$db->query("REPLACE INTO race_has_voting " .
			   "(game_id, race_id_1, race_id_2, type, end_time) " .
			   "VALUES($player->game_id, $race_id, $player->race_id, '$type', $time)");

forward(create_container("skeleton.php", "council_embassy.php"));

?>