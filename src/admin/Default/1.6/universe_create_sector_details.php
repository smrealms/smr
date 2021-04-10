<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();

$editSectorID = $session->getRequestVarInt('sector_edit');
$editSector = SmrSector::getSector($var['game_id'], $editSectorID);
$template->assign('PageTopic', 'Edit Sector #' . $editSector->getSectorID() . ' (' . $editSector->getGalaxy()->getDisplayName() . ')');
$template->assign('EditSector', $editSector);

$container = Page::copy($var);
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sector_details.php';
$template->assign('EditHREF', $container->href());

$selectedPlanetType = 0;
if ($editSector->hasPlanet()) {
	$selectedPlanetType = $editSector->getPlanet()->getTypeID();
	$template->assign('Planet', $editSector->getPlanet());
}
$template->assign('SelectedPlanetType', $selectedPlanetType);

$selectedPortLevel = null;
$selectedPortRaceID = null;
if ($editSector->hasPort()) {
	$selectedPortLevel = $editSector->getPort()->getLevel();
	$selectedPortRaceID = $editSector->getPort()->getRaceID();
	$template->assign('Port', $editSector->getPort());
}
$template->assign('SelectedPortLevel', $selectedPortLevel);
$template->assign('SelectedPortRaceID', $selectedPortRaceID);

$sectorLocationIDs = array_pad(array_keys($editSector->getLocations()),
                               UNI_GEN_LOCATION_SLOTS, 0);
$template->assign('SectorLocationIDs', $sectorLocationIDs);

if ($editSector->hasWarp()) {
	$warpSector = $editSector->getWarpSector();
	$warpSectorID = $warpSector->getSectorID();
	$warpGal = $warpSector->getGalaxy()->getDisplayName();
} else {
	$warpSectorID = 0;
	$warpGal = 'No Warp';
}
$template->assign('WarpGal', $warpGal);
$template->assign('WarpSectorID', $warpSectorID);

$container = Page::copy($var);
$container['body'] = '1.6/universe_create_sectors.php';
$template->assign('CancelHREF', $container->href());

if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
}
