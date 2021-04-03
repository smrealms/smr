<?php declare(strict_types=1);

SmrSession::getRequestVarInt('game_id');
SmrSession::getRequestVarInt('gal_on', 1);
$focusSector = SmrSession::getRequestVarInt('focus_sector_id', 0);

$galaxies = SmrGalaxy::getGameGalaxies($var['game_id']);
if (empty($galaxies)) {
	// Game was created, but no galaxies exist, so go back to
	// the galaxy generation page
	$container = Page::create('skeleton.php', '1.6/universe_create_galaxies.php');
	$container->addVar('game_id');
	$container->go();
}

$galaxy = SmrGalaxy::getGalaxy($var['game_id'], $var['gal_on']);

// Efficiently construct the caches before proceeding
$galaxy->getSectors();
$galaxy->getPorts();
$galaxy->getLocations();
$galaxy->getPlanets();

$connectivity = round($galaxy->getConnectivity());
$template->assign('ActualConnectivity', $connectivity);

// Call this after all sectors have been cached in an efficient way.
if ($focusSector == 0) {
	$mapSectors = $galaxy->getMapSectors();
} else {
	$mapSectors = $galaxy->getMapSectors($focusSector);
	$template->assign('FocusSector', $focusSector);
}

$template->assign('Galaxy', $galaxy);
$template->assign('Galaxies', $galaxies);
$template->assign('MapSectors', $mapSectors);

$lastSector = end($galaxies)->getEndSector();
$template->assign('LastSector', $lastSector);

if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
	SmrSession::updateVar('message', null); // Only show message once
}

$container = Page::create('skeleton.php', '1.6/universe_create_sectors.php');
$container->addVar('game_id');
$template->assign('JumpGalaxyHREF', $container->href());

$container->addVar('gal_on');
$template->assign('RecenterHREF', $container->href());

$container['url'] = '1.6/universe_create_save_processing.php';
$template->assign('SubmitChangesHREF', $container->href());

$container['submit'] = 'Toggle Link';
$container['AJAX'] = true;
$template->assign('ToggleLink', $container);

$container = Page::create('skeleton.php', '1.6/universe_create_sector_details.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$template->assign('UniGen', $container);

$container = Page::create('skeleton.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$container['body'] = '1.6/universe_create_locations.php';
$template->assign('ModifyLocationsHREF', $container->href());

$container['body'] = '1.6/universe_create_planets.php';
$template->assign('ModifyPlanetsHREF', $container->href());

$container['body'] = '1.6/universe_create_ports.php';
$template->assign('ModifyPortsHREF', $container->href());

$container['body'] = '1.6/universe_create_warps.php';
$template->assign('ModifyWarpsHREF', $container->href());

$container['body'] = '1.6/universe_create_sector_details.php';
$template->assign('ModifySectorHREF', $container->href());

$template->assign('SMRFileHREF', Globals::getSmrFileCreateHREF($var['game_id']));

$container = Page::create('skeleton.php', '1.6/game_edit.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$template->assign('EditGameDetailsHREF', $container->href());

$container = Page::create('skeleton.php', '1.6/check_map.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$template->assign('CheckMapHREF', $container->href());

$container = Page::create('skeleton.php', '1.6/galaxies_edit.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$template->assign('EditGalaxyDetailsHREF', $container->href());

$container = Page::create('1.6/galaxy_reset_processing.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$template->assign('ResetGalaxyHREF', $container->href());
