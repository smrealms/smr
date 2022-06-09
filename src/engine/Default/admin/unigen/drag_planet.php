<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

// Move a planet from one sector to another (note that this will
// currently only retain the planet type and inhabitable time).
$targetSectorID = Smr\Request::getInt('TargetSectorID');
$origSectorID = Smr\Request::getInt('OrigSectorID');
$origPlanet = SmrPlanet::getPlanet($var['game_id'], $origSectorID);
$targetSector = SmrSector::getSector($var['game_id'], $targetSectorID);

// Skip if target sector already has a planet
if (!$targetSector->hasPlanet()) {
	// Create first so that if there is an error the planet doesn't disappear
	SmrPlanet::createPlanet($var['game_id'], $targetSectorID, $origPlanet->getTypeID(), $origPlanet->getInhabitableTime());
	SmrPlanet::removePlanet($var['game_id'], $origSectorID);
}

$container = Page::create('admin/unigen/universe_create_sectors.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$container->go();
