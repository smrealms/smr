<?php declare(strict_types=1);

$message = htmlentities(trim(Request::get('message')), ENT_COMPAT, 'utf-8');

if (empty($message)) {
	create_error('You have to enter text to send a message!');
}

// send to all council members
$councilMembers = Council::getRaceCouncil($player->getGameID(), $var['race_id']);
foreach ($councilMembers as $playerID) {
	$player->sendMessage($playerID, MSG_POLITICAL, $message, true, $player->getPlayerID() != $playerID);
}

$container = create_container('skeleton.php', 'current_sector.php');
forward($container);
