<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$action = Smr\Request::get('action');
if ($action == 'Yes!') {
	$player->setNewbieTurns(0);
	$player->setNewbieWarning(false);
}

$player->log(LOG_TYPE_MOVEMENT, 'Player drops newbie turns.');
Page::create('current_sector.php')->go();
