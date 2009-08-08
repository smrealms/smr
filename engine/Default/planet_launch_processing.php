<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

$player->setLandedOnPlanet(false);
$player->update();
$account->log(11, 'Player launches from planet', $player->getSectorID());
forward(create_container('skeleton.php', 'current_sector.php'));

?>