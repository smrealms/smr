<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

if ($player->isLandedOnPlanet()) {
	create_error('You are on a planet!');
}

// Create a session to store temporary display options
// Do not garbage collect here for best performance (see map_galaxy.php).
if (!session_start(['gc_probability' => 0, 'gc_maxlifetime' => 86400])) {
	throw new Exception('Failed to start session');
}

// Set temporary options
if ($player->hasAlliance()) {
	if (Request::has('change_settings')) {
		$_SESSION['show_seedlist_sectors'] = Request::has('show_seedlist_sectors');
		$_SESSION['hide_allied_forces'] = Request::has('hide_allied_forces');
	}
	$showSeedlistSectors = $_SESSION['show_seedlist_sectors'] ?? false;
	$hideAlliedForces = $_SESSION['hide_allied_forces'] ?? false;
	$template->assign('ShowSeedlistSectors', $showSeedlistSectors);
	$template->assign('HideAlliedForces', $hideAlliedForces);
	$template->assign('CheckboxFormHREF', ''); // Submit to same page
}

$template->assign('SpaceView', true);

if (isset($var['ZoomDir'])) {
	if ($var['ZoomDir'] == 'Shrink') {
		$player->decreaseZoom(1);
	} elseif ($var['ZoomDir'] == 'Expand') {
		$player->increaseZoom(1);
	}
	// Unset so that refreshing doesn't zoom again
	$session->updateVar('ZoomDir', null);
}

$container = Page::create('skeleton.php', 'map_local.php');
$container['ZoomDir'] = 'Expand';
$template->assign('MapExpandHREF', $container->href());
$container['ZoomDir'] = 'Shrink';
$template->assign('MapShrinkHREF', $container->href());


$galaxy = $player->getSector()->getGalaxy();

$template->assign('GalaxyName', $galaxy->getDisplayName());

$mapSectors = $galaxy->getMapSectors($player->getSectorID(), $player->getZoom());
$template->assign('MapSectors', $mapSectors);
