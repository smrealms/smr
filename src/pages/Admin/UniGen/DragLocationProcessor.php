<?php declare(strict_types=1);

use Smr\Request;

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

// Move a location from one sector to another
$targetSectorID = Request::getInt('TargetSectorID');
$origSectorID = Request::getInt('OrigSectorID');
$locationTypeID = Request::getInt('LocationTypeID');
$targetSector = SmrSector::getSector($var['game_id'], $targetSectorID);

// Skip if target sector already has the same location
if (!$targetSector->hasLocation($locationTypeID)) {
	$location = SmrLocation::getLocation($var['game_id'], $locationTypeID);
	SmrLocation::moveSectorLocation($var['game_id'], $origSectorID, $targetSectorID, $location);
}

$container = Page::create('admin/unigen/universe_create_sectors.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$container->go();
