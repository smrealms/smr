<?php

$receiver = $_REQUEST['receiver'];
$subject = $_REQUEST['subject'];
$msg = $_REQUEST['msg'];

mail($receiver,
	 $subject,
	 "Login:\n------\n$account->login\n\n" .
	 "Account ID:\n-----------\n$account->account_id\n\n" .
	 "Message:\n------------\n$msg",
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