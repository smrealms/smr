<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

// Move a warp from one sector to another
$targetSectorID = Smr\Request::getInt('TargetSectorID');
$origSectorID = Smr\Request::getInt('OrigSectorID');

$origSector = SmrSector::getSector($var['game_id'], $origSectorID);
$warpSector = $origSector->getWarpSector();
$targetSector = SmrSector::getSector($var['game_id'], $targetSectorID);

// Skip if target sector already has a warp
if (!$targetSector->hasWarp()) {
	$origSector->removeWarp();
	$targetSector->setWarp($warpSector);
	SmrSector::saveSectors();
}

$container = Page::create('admin/unigen/universe_create_sectors.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$container->go();
