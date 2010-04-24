<?php

$message = nl2br(format_string($_POST["message"], true));

if (empty($message))
	create_error("You have to enter a text to send!");

if (empty($var["receiver"])) {

	// send to all online player
	$allowed = time() - 600;
	$db->query("SELECT * FROM player WHERE game_id = $player->game_id AND last_active >= $allowed AND ignore_global = 'NO'");

	while ($db->next_record()) {
		$player->send_message($db->f("account_id"), MSG_GLOBAL, $message);
	}

} else {
	$player->send_message($var["receiver"], MSG_PLAYER, $message);
}

// get rid of all old scout messages (>24h)
$old = time() - 86400;
$db->query("DELETE FROM message WHERE send_time < $old AND message_type_id = 4");

$container = array();
$container["url"] = "skeleton.php";
if ($player->land_on_planet == "TRUE")
	$container["body"] = "planet_main.php";
else
	$container["body"] = "current_sector.php";

forward($container);

?>