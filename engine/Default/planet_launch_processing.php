<?php declare(strict_types=1);
if (!$player->isLandedOnPlanet()) {
	create_error('You are not on a planet!');
}

$player->setLandedOnPlanet(false);
$player->update();
$player->log(LOG_TYPE_MOVEMENT, 'Player launches from planet');
forward(create_container('skeleton.php', 'current_sector.php'));
