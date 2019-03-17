<?php

if(!$sector->isLinked($var['target_sector']) && $sector->getSectorID() != $var['target_sector']) {
	create_error('You cannot scan a sector you are not linked to.');
}

// initialize vars
$scanSector = SmrSector::getSector($player->getGameID(), $var['target_sector']);

$template->assign('PageTopic','Sector Scan of #'.$scanSector->getSectorID().' ('.$scanSector->getGalaxyName().')');
Menu::navigation($template, $player);

$friendly_forces = 0;
$enemy_forces = 0;
$friendly_vessel = 0;
$enemy_vessel = 0;

// iterate over all forces in the target sector
foreach ($scanSector->getForces() as $scanSectorForces) {
	// decide if it's a friendly or enemy stack
	if ($player->sameAlliance($scanSectorForces->getOwner()))
		$friendly_forces += $scanSectorForces->getMines() * 3 + $scanSectorForces->getCDs() * 2 + $scanSectorForces->getSDs();
	else
		$enemy_forces += $scanSectorForces->getMines() * 3 + $scanSectorForces->getCDs() * 2 + $scanSectorForces->getSDs();
}

foreach ($scanSector->getOtherTraders($player) as $scanSectorPlayer) {
	$scanSectorShip = $scanSectorPlayer->getShip();

	// he's a friend if he's in our alliance (and we are not in a 0 alliance
	if ($player->traderMAPAlliance($scanSectorPlayer))
		$friendly_vessel += $scanSectorShip->getAttackRating();
	else
		$enemy_vessel += $scanSectorShip->getDefenseRating() * 10;
}

$template->assign('FriendlyVessel', $friendly_vessel);
$template->assign('FriendlyForces', $friendly_forces);
$template->assign('EnemyVessel', $enemy_vessel);
$template->assign('EnemyForces', $enemy_forces);

// is it a warp or a normal move?
if ($sector->getWarp() == $var['target_sector'])
	$turns = TURNS_PER_WARP;
else
	$turns = TURNS_PER_SECTOR;

$template->assign('ScanSector', $scanSector);
$template->assign('Turns', $turns);
