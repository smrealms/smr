<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if (!$player->isLandedOnPlanet()) {
	create_error('You are not on a planet!');
}

$player->setLandedOnPlanet(false);
$player->update();
$player->log(LOG_TYPE_MOVEMENT, 'Player launches from planet');
Page::create('current_sector.php')->go();
