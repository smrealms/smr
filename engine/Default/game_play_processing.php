<?php

// register game_id
SmrSession::$game_id = $var['game_id'];

$player =& SmrPlayer::getPlayer(SmrSession::$account_id, $var['game_id']);
$player->updateLastCPLAction();

// get rid of old plotted course
$player->deletePlottedCourse();
$player->update();

// log
$account->log(LOG_TYPE_GAME_ENTERING, 'Player entered game '.SmrSession::$game_id, $player->getSectorID());

$container = create_container('skeleton.php');
if ($player->isLandedOnPlanet())
	$container['body'] = 'planet_main.php';
else
	$container['body'] = 'current_sector.php';

forward($container);
?>