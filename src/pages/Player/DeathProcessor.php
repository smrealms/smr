<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$player->setDead(false);

$player->log(LOG_TYPE_TRADER_COMBAT, 'Player sees death screen');
Page::create('death.php')->go();
