<?php declare(strict_types=1);
////////////////////////////////////////////////////////////
//
//	Script:		map_local.php
//	Purpose:	Displays Local Map
//
////////////////////////////////////////////////////////////

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
	if (isset($_POST['change_settings'])) {
		$_SESSION['show_seedlist_sectors'] = isset($_POST['show_seedlist_sectors']);
		$_SESSION['hide_allied_forces'] = isset($_POST['hide_allied_forces']);
	}
	$showSeedlistSectors = isset($_SESSION['show_seedlist_sectors']) ? $_SESSION['show_seedlist_sectors'] : false;
	$hideAlliedForces = isset($_SESSION['hide_allied_forces']) ? $_SESSION['hide_allied_forces'] : false;
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
	SmrSession::updateVar('ZoomDir', null);
}

$container = create_container('skeleton.php', 'map_local.php');
$container['ZoomDir'] = 'Expand';
$template->assign('MapExpandHREF', SmrSession::getNewHREF($container));
$container['ZoomDir'] = 'Shrink';
$template->assign('MapShrinkHREF', SmrSession::getNewHREF($container));


$galaxy = $player->getSector()->getGalaxy();

$template->assign('GalaxyName', $galaxy->getName());

$mapSectors = $galaxy->getMapSectors($player->getSectorID(), $player->getZoom());
$template->assign('MapSectors', $mapSectors);
