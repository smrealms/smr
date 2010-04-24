<?php
$db->query("SELECT timeout FROM vote_links WHERE account_id=" . SmrSession::$old_account_id . " AND link_id=" . $var["link_id"] . " LIMIT 1");

// They get to vote once every 24 hours
if(!$db->next_record()) {
	$valid=true;
}
else if($db->f("timeout") < time() - 86400) {
	$valid = true;
}
else {
	$valid = false;
}

// Sanity checking
if($var["link_id"] > 3 || $var["link_id"] < 1 ) {
	$valid = false;
}


if($valid == true) {
	// Allow vote
	$db->query("REPLACE INTO vote_links (account_id,link_id,timeout) VALUES(" . SmrSession::$old_account_id . "," . $var["link_id"] . "," . time() . ")");
	// They get 1/3 of their hourly turns for a valid click (They CAN go above max turns for the game this way)
	if(($player->turns + ($ship->speed*$player->game_speed/3)) < (400 * $player->game_speed))
		$db->query("UPDATE player SET turns=turns+" . ($ship->speed*$player->game_speed/3) . " WHERE game_id=" . SmrSession::$game_id . " AND account_id=" . SmrSession::$old_account_id . " LIMIT 1");
	else
		$db->query("UPDATE player SET turns=" . (400 * $player->game_speed) . " WHERE game_id=" . SmrSession::$game_id . " AND account_id=" . SmrSession::$old_account_id . " LIMIT 1");
}

$container = array();
$container["url"] = "skeleton.php";

// Return them to the appropriate screen
if ($player->land_on_planet == "FALSE")
	$container["body"] = "current_sector.php";
else 
	$container["body"] = "planet_main.php";

$container["voted"] = true;
forward($container);

?>
