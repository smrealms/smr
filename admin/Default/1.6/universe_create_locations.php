<?php
require_once(get_file_loc('SmrGalaxy.class.inc'));

//universe_create_locations.php
$container = $var;
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sectors.php';

$PHP_OUTPUT.= create_echo_form($container);
//get totals
//$totalLocs[5] = 0;
//$totalLocs[6] = 0;
$locations =& SmrLocation::getAllLocations();

$galSectors =& SmrSector::getGalaxySectors($var['game_id'],$var['gal_on']);
foreach ($galSectors as &$sector) {
	$sectorLocations =& $sector->getLocations();
	foreach ($sectorLocations as &$sectorLocation) {
		$totalLocs[$sectorLocation->getTypeID()]++;
	} unset($sectorLocation);
} unset($sector);
$galaxy =& SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
$PHP_OUTPUT.= 'Working on Galaxy : ' . $galaxy->getName() . ' (' . $galaxy->getGalaxyID() . ')<br />';
$PHP_OUTPUT.= '<table class="standard">';

foreach ($locations as &$location) {
//	if (isset($loc_array['Do Not List']) && $loc_array['Do Not List']) continue;
	$extra = '<span class="small"><br />';
	if ($location->isWeaponSold()) {
		//$extra = '<table class="nobord right">';
		$weaponsSold =& $location->getWeaponsSold();
		foreach($weaponsSold as &$weapon) {
			$extra .= $weapon->getName() . '&nbsp;&nbsp;&nbsp;(' . $weapon->getShieldDamage() . '/' . $weapon->getArmourDamage() . '/' . $weapon->getBaseAccuracy() . ')<br />';
		} unset($weapon);
			//$extra .= '<tr><td class="right"><span class="small">' . $WEAPONS[$wep_id]['Weapon Name'] . '</span></td><td class="left"><span class="small">(' . $WEAPONS[$wep_id]['Shield Damage'] . '/' . $WEAPONS[$wep_id]['Armor Damage'] . '/' . $WEAPONS[$wep_id]['Accuracy'] . ')</span></td></tr>';
		//$extra .= '</table>';
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
	$PHP_OUTPUT.= '<tr><td class="right">' . $location->getName() . $extra . '</td><td class="left">';
	$PHP_OUTPUT.= '<input type="text" value="';
	if (isset($totalLocs[$location->getTypeID()])) $PHP_OUTPUT.= $totalLocs[$location->getTypeID()];
	else $PHP_OUTPUT.= '0';
	$PHP_OUTPUT.= '" size="5" name="loc' . $location->getTypeID() . '"></td></tr>';
} unset($location);
$PHP_OUTPUT.= '<tr><td colspan="2" class="center"><input type="submit" name="submit" value="Create Locations">';
$container = $var;
$container['body'] = '1.6/universe_create_sectors.php';
$PHP_OUTPUT.= '<br /><br /><a href="'.SmrSession::getNewHREF($container).'" class="submitStyle">Cancel</a>';
$PHP_OUTPUT.= '</td></tr></table></form>';

$PHP_OUTPUT.= '<span class="small">Note: When you press "Create Locations" this will rearrange all current locations.<br />';
$PHP_OUTPUT.= 'To add new locations without rearranging everything use the edit sector feature.</span>';

?>