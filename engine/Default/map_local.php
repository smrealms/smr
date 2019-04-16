<?php
////////////////////////////////////////////////////////////
//
//	Script:		map_local.php
//	Purpose:	Displays Local Map
//
////////////////////////////////////////////////////////////

if($player->isLandedOnPlanet())
	create_error('You are on a planet!');

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

$template->assign('SpaceView',true);
$template->assign('HeaderTemplateInclude','includes/LocalMapJS.inc');

$zoomOn = false;
if(isset($var['Dir'])) {
	$zoomOn = true;
	if ($var['Dir'] == 'Up') {
		$player->decreaseZoom(1);
	}
	elseif ($var['Dir'] == 'Down') {
		$player->increaseZoom(1);
	}
}

$template->assign('isZoomOn',$zoomOn);

$container = create_container('skeleton.php', 'map_local.php');
$container['Dir'] = 'Down';
$container['rid'] = 'zoom_down';
$container['valid_for'] = -5;
$template->assign('ZoomDownLink',SmrSession::getNewHREF($container));
$container['Dir'] = 'Up';
$container['rid'] = 'zoom_up';
$template->assign('ZoomUpLink',SmrSession::getNewHREF($container));


$galaxy = $player->getSector()->getGalaxy();

$template->assign('GalaxyName',$galaxy->getName());

$mapSectors = $galaxy->getMapSectors($player->getSectorID(), $player->getZoom());
$template->assign('MapSectors',$mapSectors);
