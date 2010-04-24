<?php

if ($player->game_id == 0)
	return;

// create a date from last midnight
$midnight = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

$db->query("SELECT * FROM player_votes_twg " .
		   "WHERE account_id = $player->account_id AND " .
				 "game_id = $player->game_id AND " .
				 "time > $midnight");
if ($db->nf() > 0)
	return;

// give him 5 turns
$player->turns += 5;

// don't give more than the max.
if ($player->turns > 400)
	$player->turns = 400;

// make it permanent
$player->update();
$db->query("SELECT * FROM game WHERE game_id = $player->game_id");
$db->next_record();
$type = $db->f("game_type");

$db->query("UPDATE account_has_stats " .
		   "SET bonus_turns = bonus_turns + 5 " .
		   "WHERE account_id = $player->account_id AND game_type = '$type'");

$db->query("REPLACE INTO player_votes_twg " .
		   "(account_id, game_id, time) " .
		   "VALUES($player->account_id, $player->game_id, " . time() . ")");

header("Location: http://www.topwebgames.com/in.asp?id=136");

?>