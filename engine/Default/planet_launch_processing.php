<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

$player->setLandedOnPlanet(false);
$player->update();
$account->log(LOG_TYPE_MOVEMENT, 'Player launches from planet', $player->getSectorID());
forward(create_container('skeleton.php', 'current_sector.php'));

?>