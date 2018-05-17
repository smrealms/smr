<?php

// register game_id
SmrSession::updateGame($var['game_id']);

$player = SmrPlayer::getPlayer($account->getAccountID(), $var['game_id']);
$player->updateLastCPLAction();

// Check to see if newbie status has changed
$player->updateNewbieStatus();

// get rid of old plotted course
$player->deletePlottedCourse();
$player->update();

// log
$account->log(LOG_TYPE_GAME_ENTERING, 'Player entered game '.SmrSession::$game_id, $player->getSectorID());

$container = create_container('skeleton.php', 'current_sector.php');
forward($container);
