<?php declare(strict_types=1);

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
$player->log(LOG_TYPE_GAME_ENTERING, 'Player entered game ' . SmrSession::getGameID());

$container = create_container('skeleton.php', 'current_sector.php');
forward($container);
