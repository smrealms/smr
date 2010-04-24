<?php

$message = nl2br(format_string($_POST["message"], true));

if (empty($message))
	create_error("You have to enter a text to send!");

// send to all council members
$db->query("SELECT * FROM player " .
		   "WHERE game_id = $player->game_id AND " .
				 "race_id = $var[race_id] " .
		   "ORDER by experience DESC " .
		   "LIMIT 20");

while ($db->next_record()) {
	$player->send_message($db->f("account_id"), MSG_POLITICAL, $message);
}

$container = array();
$container["url"] = "skeleton.php";
if ($player->land_on_planet == "TRUE")
	$container["body"] = "planet_main.php";
else
	$container["body"] = "current_sector.php";

forward($container);

?>