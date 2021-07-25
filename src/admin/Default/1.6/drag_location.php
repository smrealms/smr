<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

// Move a location from one sector to another
$targetSectorID = Smr\Request::getInt('TargetSectorID');
$origSectorID = Smr\Request::getInt('OrigSectorID');
$locationTypeID = Smr\Request::getInt('LocationTypeID');
$targetSector = SmrSector::getSector($var['game_id'], $targetSectorID);

// Skip if target sector already has the same location
if (!$targetSector->hasLocation($locationTypeID)) {
	$location = SmrLocation::getLocation($locationTypeID);
	SmrLocation::moveSectorLocation($var['game_id'], $origSectorID, $targetSectorID, $location);
}

$container = Page::create('skeleton.php', '1.6/universe_create_sectors.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$container->go();
