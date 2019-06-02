<?php
if (!$player->isLandedOnPlanet()) {
	create_error('You are not on a planet!');
}
$planet = $player->getSectorPlanet();
$action = $var['action'];
if ($action == 'Build') {
	if (($message = $planet->canBuild($player, $var['construction_id'])) !== true) {
		create_error($message);
	}

	// now start the construction
	$planet->startBuilding($player, $var['construction_id']);
	$player->increaseHOF(1, array('Planet', 'Buildings', 'Started'), HOF_ALLIANCE);

	$account->log(LOG_TYPE_PLANETS, 'Player starts a ' . $planet->getStructureTypes($var['construction_id'])->name() . ' on planet.', $player->getSectorID());

} elseif ($action == 'Cancel') {
	$planet->stopBuilding($var['construction_id']);
	$player->increaseHOF(1, array('Planet', 'Buildings', 'Stopped'), HOF_ALLIANCE);
	$account->log(LOG_TYPE_PLANETS, 'Player cancels planet construction', $player->getSectorID());
}

forward(create_container('skeleton.php', 'planet_construction.php'));
