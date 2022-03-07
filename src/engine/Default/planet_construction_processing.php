<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

if (!$player->isLandedOnPlanet()) {
	create_error('You are not on a planet!');
}
$planet = $player->getSectorPlanet();
$action = $var['action'];
if ($action == 'Build') {
	// now start the construction
	$planet->startBuilding($player, $var['construction_id']);
	$player->increaseHOF(1, ['Planet', 'Buildings', 'Started'], HOF_ALLIANCE);

	$player->log(LOG_TYPE_PLANETS, 'Player starts a ' . $planet->getStructureTypes($var['construction_id'])->name() . ' on planet.');

} elseif ($action == 'Cancel') {
	$planet->stopBuilding($var['construction_id']);
	$player->increaseHOF(1, ['Planet', 'Buildings', 'Stopped'], HOF_ALLIANCE);
	$player->log(LOG_TYPE_PLANETS, 'Player cancels planet construction');
}

Page::create('skeleton.php', 'planet_construction.php')->go();
