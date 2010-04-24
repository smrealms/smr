<?php

$steps = $_REQUEST['steps'];
$subject = $_REQUEST['subject'];
$error_msg = $_REQUEST['error_msg'];
$login = $_REQUEST['login'];
$account_id = $_REQUEST['account_id'];
$description = $_REQUEST['description'];
$new_sub = "[Bug] $subject";
mail("bugs@smrealms.de",
	 $new_sub,
	 "Login:\n------\n$login\n\n" .
	 "Account ID:\n-----------\n$account_id\n\n" .
	 "Description:\n------------\n$description\n\n" .
	 "Steps to repeat:\n----------------\n$steps\n\n" .
	 "Error Message:\n--------------\n$error_msg",
	 "From: $account->email");

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