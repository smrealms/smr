<?php

// delete their entry in kills table
//$db->query("DELETE FROM kills WHERE dead_id = $player->account_id AND game_id = $player->game_id");

print_topic("DEATH");

print("<p>As the hull of your ship collapses, you quickly launch out in your escape pod. ");
print("Activating the emergency warp system, your stomach turns as you are hurled through hyperspace back to a safe destination.</p>");

print("<p><img src=\"images/escape_pod.jpg\"></p>");

/*
if ($player->newbie_turns < 100)
	$player->newbie_turns = 100;

if ($player->sector_id != $player->get_home())
	$player->sector_id = $player->get_home();

if ($player->credits < 5000)
	$player->credits = 5000;
*/
//$player->dead = "FALSE";

//$player->delete_plotted_course();

//$player->update();

$db->query('UPDATE player SET dead="FALSE" WHERE account_id=' . SmrSession::$old_account_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');

$account->log(8, "Player sees death screen", $player->sector_id);

?>