<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$session->getRequestVarInt('game_id');
$session->getRequestVarInt('gal_on', 1);
$focusSector = $session->getRequestVarInt('focus_sector_id', 0);

$galaxies = SmrGalaxy::getGameGalaxies($var['game_id']);
if (empty($galaxies)) {
	// Game was created, but no galaxies exist, so go back to
	// the galaxy generation page
	$container = Page::create('admin/unigen/universe_create_galaxies.php');
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
	unset($var['message']); // Only show message once
}

$container = Page::create('admin/unigen/universe_create_sectors.php');
$container->addVar('game_id');
$template->assign('JumpGalaxyHREF', $container->href());

$container->addVar('gal_on');
$template->assign('RecenterHREF', $container->href());

$container = Page::create('admin/unigen/universe_create_save_processing.php');
$container['forward_to'] = 'admin/unigen/universe_create_sectors.php';
$container->addVar('game_id');
$container->addVar('gal_on');
$template->assign('SubmitChangesHREF', $container->href());

$container['submit'] = 'Toggle Link';
$container['AJAX'] = true;
$template->assign('ToggleLink', $container);

$container = Page::create('admin/unigen/drag_location.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$container['AJAX'] = true;
$template->assign('DragLocationHREF', $container->href());

$container = Page::create('admin/unigen/drag_planet.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$container['AJAX'] = true;
$template->assign('DragPlanetHREF', $container->href());

$container = Page::create('admin/unigen/drag_warp.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$container['AJAX'] = true;
$template->assign('DragWarpHREF', $container->href());

$container = Page::create('admin/unigen/universe_create_sector_details.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$template->assign('UniGen', $container);

$container = Page::create('admin/unigen/universe_create_locations.php', $container);
$template->assign('ModifyLocationsHREF', $container->href());

$container = Page::create('admin/unigen/universe_create_planets.php', $container);
$template->assign('ModifyPlanetsHREF', $container->href());

$container = Page::create('admin/unigen/universe_create_ports.php', $container);
$template->assign('ModifyPortsHREF', $container->href());

$container = Page::create('admin/unigen/universe_create_warps.php', $container);
$template->assign('ModifyWarpsHREF', $container->href());

$container = Page::create('admin/unigen/universe_create_sector_details.php', $container);
$template->assign('ModifySectorHREF', $container->href());

$template->assign('SMRFileHREF', Globals::getSmrFileCreateHREF($var['game_id']));

$container = Page::create('admin/unigen/game_edit.php', $container);
$template->assign('EditGameDetailsHREF', $container->href());

$container = Page::create('admin/unigen/check_map.php', $container);
$template->assign('CheckMapHREF', $container->href());

$container = Page::create('admin/unigen/galaxies_edit.php', $container);
$template->assign('EditGalaxyDetailsHREF', $container->href());

$container = Page::create('admin/unigen/galaxy_reset_processing.php', $container);
$template->assign('ResetGalaxyHREF', $container->href());
