<?php

// update last login time
$account->update_last_login();

$container = array();
$container["url"] = "skeleton.php";
if (SmrSession::$game_id > 0) {

	if ($player->land_on_planet == "TRUE")
		$container["body"] = "planet_main.php";
	else
		$container["body"] = "current_sector.php";

} else
	$container["body"] = "game_play.php";

forward($container);

?>