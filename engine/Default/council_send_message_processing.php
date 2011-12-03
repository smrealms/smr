<?php

$message = htmlentities(trim($_POST['message']),ENT_COMPAT,'utf-8');

if (empty($message))
	create_error('You have to enter a text to send!');

// send to all council members
require_once(get_file_loc('council.inc'));
$councilMembers = Council::getRaceCouncil($player->getGameID(), $var['race_id']);
foreach($councilMembers as $accountID)
{
	$player->sendMessage($accountID, MSG_POLITICAL, $message, true, $player->getAccountID() != $accountID);
}

$container = array();
$container['url'] = 'skeleton.php';
if ($player->isLandedOnPlanet())
	$container['body'] = 'planet_main.php';
else
	$container['body'] = 'current_sector.php';

forward($container);

?>