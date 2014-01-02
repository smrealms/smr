<?php
require_once(get_file_loc('SmrGalaxy.class.inc'));

if (!isset($var['game_id'])) SmrSession::updateVar('game_id', $_REQUEST['game_id']);
if (!isset($var['gal_on'])) SmrSession::updateVar('gal_on', 1);

//generate sector array
$galaxy =& SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
$galaxies =& SmrGalaxy::getGameGalaxies($var['game_id']);

$topLeft =& SmrSector::getSector($var['game_id'], $galaxy->getStartSector());
$mapSectors = array();
$leftMostSec =& $topLeft;
for ($i=0;$i<$galaxy->getHeight();$i++) {
	$mapSectors[$i] = array();
	//new row
	if ($i!=0) $leftMostSec =& $leftMostSec->getNeighbourSector('Down');

	//get left most sector for this row
	$thisSec =& $leftMostSec;
	//iterate through the columns
	for ($j=0;$j<$galaxy->getWidth();$j++) {
		//new sector
		if ($j!=0) $thisSec =& $thisSec->getNeighbourSector('Right');
		$mapSectors[$i][$j] =& $thisSec;
	}
}


$template->assignByRef('Galaxy', $galaxy);
$template->assignByRef('Galaxies', $galaxies);
$template->assignByRef('MapSectors',$mapSectors);
$template->assignByRef('Message',$var['message']);
SmrSession::updateVar('message',null); // Only show message once

if (isset($_REQUEST['connect']) && $_REQUEST['connect'] > 0) {
	SmrSession::updateVar('conn',$_REQUEST['connect']);
}
else if (!isset($var['conn'])) {
	SmrSession::updateVar('conn',100);
}
$template->assign('Connectivity', $var['conn']);


$container = $var;
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sectors.php';
$template->assign('SubmitChangesHREF', SmrSession::getNewHref($container));

$container['submit'] = 'Jump To Galaxy';
$template->assign('JumpGalaxyHREF', SmrSession::getNewHref($container));

$container['submit'] = 'Toggle Link';
$template->assign('ToggleLink', $container);

$container = $var;
$container['body'] = '1.6/universe_create_sector_details.php';
$template->assign('UniGen', $container);

$container = $var;
$container['body'] = '1.6/universe_create_locations.php';
$template->assign('ModifyLocationsHREF',SmrSession::getNewHREF($container));

$container['body'] = '1.6/universe_create_planets.php';
$template->assign('ModifyPlanetsHREF',SmrSession::getNewHREF($container));

$container['body'] = '1.6/universe_create_ports.php';
$template->assign('ModifyPortsHREF',SmrSession::getNewHREF($container));

$container['body'] = '1.6/universe_create_warps.php';
$template->assign('ModifyWarpsHREF',SmrSession::getNewHREF($container));

$template->assign('SMRFileHREF',Globals::getSmrFileCreateHREF($var['game_id']));

if ($var['gal_on'] > 1) {
	$container = $var;
	$container['gal_on']--;
	$template->assign('PreviousGalaxyHREF', SmrSession::getNewHREF($container));
}
if ($var['gal_on'] < count($galaxies)) {
	$container = $var;
	$container['gal_on']++;
	$template->assign('NextGalaxyHREF', SmrSession::getNewHREF($container));
}

?>