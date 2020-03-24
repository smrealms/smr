<?php declare(strict_types=1);

SmrSession::getRequestVarInt('game_id');
SmrSession::getRequestVarInt('gal_on', 1);

$galaxies = SmrGalaxy::getGameGalaxies($var['game_id']);
if (empty($galaxies)) {
	// Game was created, but no galaxies exist, so go back to
	// the galaxy generation page
	$container = create_container('skeleton.php', '1.6/universe_create_galaxies.php');
	transfer('game_id');
	forward($container);
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
$mapSectors = $galaxy->getMapSectors();

$template->assign('Galaxy', $galaxy);
$template->assign('Galaxies', $galaxies);
$template->assign('MapSectors', $mapSectors);

$lastSector = end($galaxies)->getEndSector();
$template->assign('LastSector', $lastSector);

if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
	SmrSession::updateVar('message', null); // Only show message once
}

$container = create_container('skeleton.php', '1.6/universe_create_sectors.php');
transfer('game_id');
$template->assign('JumpGalaxyHREF', SmrSession::getNewHref($container));

$container['url'] = '1.6/universe_create_save_processing.php';
transfer('gal_on');
$template->assign('SubmitChangesHREF', SmrSession::getNewHref($container));

$container['submit'] = 'Toggle Link';
$container['AJAX'] = true;
$template->assign('ToggleLink', $container);

$container = create_container('skeleton.php', '1.6/universe_create_sector_details.php');
transfer('game_id');
transfer('gal_on');
$template->assign('UniGen', $container);

$container = create_container('skeleton.php');
transfer('game_id');
transfer('gal_on');
$container['body'] = '1.6/universe_create_locations.php';
$template->assign('ModifyLocationsHREF', SmrSession::getNewHREF($container));

$container['body'] = '1.6/universe_create_planets.php';
$template->assign('ModifyPlanetsHREF', SmrSession::getNewHREF($container));

$container['body'] = '1.6/universe_create_ports.php';
$template->assign('ModifyPortsHREF', SmrSession::getNewHREF($container));

$container['body'] = '1.6/universe_create_warps.php';
$template->assign('ModifyWarpsHREF', SmrSession::getNewHREF($container));

$container['body'] = '1.6/universe_create_sector_details.php';
$template->assign('ModifySectorHREF', SmrSession::getNewHREF($container));

$template->assign('SMRFileHREF', Globals::getSmrFileCreateHREF($var['game_id']));

$container = create_container('skeleton.php', '1.6/game_edit.php');
transfer('game_id');
transfer('gal_on');
$template->assign('EditGameDetailsHREF', SmrSession::getNewHREF($container));

$container = create_container('skeleton.php', '1.6/check_map.php');
transfer('game_id');
transfer('gal_on');
$template->assign('CheckMapHREF', SmrSession::getNewHREF($container));

$container = create_container('skeleton.php', '1.6/galaxies_edit.php');
transfer('game_id');
transfer('gal_on');
$template->assign('EditGalaxyDetailsHREF', SmrSession::getNewHREF($container));

$container = create_container('1.6/galaxy_reset_processing.php');
transfer('game_id');
transfer('gal_on');
$template->assign('ResetGalaxyHREF', SmrSession::getNewHREF($container));
