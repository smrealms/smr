<?php

$message = htmlentities(trim($_POST['message']),ENT_COMPAT,'utf-8');

if (empty($message))
	create_error('You have to enter a text to send!');

// send to all council members
$councilMembers = Council::getRaceCouncil($player->getGameID(), $var['race_id']);
foreach($councilMembers as $accountID) {
	$player->sendMessage($accountID, MSG_POLITICAL, $message, true, $player->getAccountID() != $accountID);
}

$container = create_container('skeleton.php', 'current_sector.php');
forward($container);
