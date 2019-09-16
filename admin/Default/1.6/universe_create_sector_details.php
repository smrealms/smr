<?php

$editSectorID = $_POST['sector_edit'] ?? $var['sector_id'];
$editSector = SmrSector::getSector($var['game_id'], $editSectorID);
$template->assign('PageTopic', 'Edit Sector #' . $editSector->getSectorID() . ' (' . $editSector->getGalaxyName() . ')');
$template->assign('EditSector', $editSector);

$container = $var;
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sector_details.php';
$template->assign('EditHREF', SmrSession::getNewHREF($container));

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
	$warpGal = $warpSector->getGalaxyName();
} else {
	$warpSectorID = 0;
	$warpGal = 'No Warp';
}
$template->assign('WarpGal', $warpGal);
$template->assign('WarpSectorID', $warpSectorID);

$container = $var;
$container['body'] = '1.6/universe_create_sectors.php';
$template->assign('CancelHREF', SmrSession::getNewHREF($container));

if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
}
