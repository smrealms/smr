<?php
require_once(get_file_loc('SmrGalaxy.class.inc'));

$locations =& SmrLocation::getAllLocations();
$template->assignByRef('Locations', $locations);

// Initialize all location counts to zero
$totalLocs = array();
foreach ($locations as &$location) {
	$totalLocs[$location->getTypeID()] = 0;
}

// Determine the current amount of each location
$galSectors =& SmrSector::getGalaxySectors($var['game_id'],$var['gal_on']);
foreach ($galSectors as &$sector) {
	$sectorLocations =& $sector->getLocations();
	foreach ($sectorLocations as &$sectorLocation) {
		$totalLocs[$sectorLocation->getTypeID()]++;
	} unset($sectorLocation);
} unset($sector);
$template->assignByRef('TotalLocs', $totalLocs);

$galaxy =& SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
$template->assignByRef('Galaxy', $galaxy);

// Set any extra information to be displayed with each location
$extraLocs = array();
foreach ($locations as &$location) {
	$extra = '<span class="small"><br />';
	if ($location->isWeaponSold()) {
		$weaponsSold =& $location->getWeaponsSold();
		foreach($weaponsSold as &$weapon) {
			$extra .= $weapon->getName() . '&nbsp;&nbsp;&nbsp;(' . $weapon->getShieldDamage() . '/' . $weapon->getArmourDamage() . '/' . $weapon->getBaseAccuracy() . ')<br />';
		} unset($weapon);
	}
	if ($location->isShipSold()) {
		$shipsSold =& $location->getShipsSold();
		foreach ($shipsSold as &$shipSold) {
			$extra .= $shipSold['Name'] . '<br />';
		} unset($shipSold);
	}
	if ($location->isHardwareSold()) {
		$hardwareSold =& $location->getHardwareSold();
		foreach ($hardwareSold as &$hardware) {
			$extra .= $hardware['Name'] . '<br />';
		} unset($hardware);
	}
	$extra .= '</span>';

	$extraLocs[$location->getTypeID()] = $extra;
} unset($location);
$template->assignByRef('ExtraLocs', $extraLocs);

// Form to make location changes
$container = create_container('1.6/universe_create_save_processing.php',
                              '1.6/universe_create_sectors.php', $var);
$template->assign('Form', create_echo_form($container));

// HREF to cancel and return to the previous page
$container = create_container('skeleton.php', '1.6/universe_create_sectors.php', $var);
$template->assign('CancelHREF', SmrSession::getNewHREF($container));

?>
