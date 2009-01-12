<?php

$new_sub = "Beta Application";
mail("beta@smrealms.de",
	 $new_sub,
	 "Login:\n------\n$login\n\n" .
	 "Webboard Name:\n------\n$webboard\n\n" .
	 "IRC Nick:\n------\n$ircnick\n\n" .
	 "Account ID:\n-----------\n$account_id\n\n" .
	 "Start Time:\n-----------\n$started\n\n" .
	 "Reasons:\n------------\n$reasons\n\n" .
	 "Time spent on beta:\n----------------\n$time\n\n" .
	 "Online time:\n--------------\n$online",
	 "From: $account->email");

$container = array();
$container["url"] = "skeleton.php";
if ($session->game_id > 0) {

	if ($player->land_on_planet == "TRUE")
		$container["body"] = "planet_main.php";
	else
		$container["body"] = "current_sector.php";

} else
	$container["body"] = "game_play.php";

forward($container);

?>