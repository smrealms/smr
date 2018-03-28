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
$dist = $player->getZoom();

$template->assign('isZoomOn',$zoomOn);

$container = array();
$container['url'] = 'skeleton.php';
$container['Dir'] = 'Down';
$container['rid'] = 'zoom_down';
$container['body'] = 'map_local.php';
$container['valid_for'] = -5;
$template->assign('ZoomDownLink',SmrSession::getNewHREF($container));
$container['Dir'] = 'Up';
$container['rid'] = 'zoom_up';
$template->assign('ZoomUpLink',SmrSession::getNewHREF($container));

$span = 1 + ($dist * 2);

$topLeft =& $player->getSector();
$galaxy =& $topLeft->getGalaxy();

$template->assign('GalaxyName',$galaxy->getName());

//figure out what should be the top left and bottom right
//go left then up
for ($i=0;$i<$dist&&$i<(int)($galaxy->getWidth()/2);$i++)
	$topLeft =& $topLeft->getNeighbourSector('Left');
for ($i=0;$i<$dist&&$i<(int)($galaxy->getHeight()/2);$i++)
	$topLeft =& $topLeft->getNeighbourSector('Up');

$mapSectors = array();
$leftMostSec =& $topLeft;
for ($i=0;$i<$span&&$i<$galaxy->getHeight();$i++) {
	$mapSectors[$i] = array();
	//new row
	if ($i!=0) $leftMostSec =& $leftMostSec->getNeighbourSector('Down');
	
	//get left most sector for this row
	$thisSec =& $leftMostSec;
	//iterate through the columns
	for ($j=0;$j<$span&&$j<$galaxy->getWidth();$j++) {
		//new sector
		if ($j!=0) $thisSec =& $thisSec->getNeighbourSector('Right');
		$mapSectors[$i][$j] =& $thisSec;
	}
}
$template->assignByRef('MapSectors',$mapSectors);

?>
