<?php
require_once(get_file_loc('SmrGalaxy.class.inc'));

$locations =& SmrLocation::getAllLocations();

// Initialize all location counts to zero
$totalLocs = array();
foreach ($locations as &$location) {
	$totalLocs[$location->getTypeID()] = 0;
}

// Determine the current amount of each location
$galSectors = SmrSector::getGalaxySectors($var['game_id'],$var['gal_on']);
foreach ($galSectors as $galSector) {
	foreach ($galSector->getLocations() as $sectorLocation) {
		$totalLocs[$sectorLocation->getTypeID()]++;
	}
}
$template->assign('TotalLocs', $totalLocs);

$galaxy =& SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
$template->assign('Galaxy', $galaxy);

// Though we expect a location to be only in one category, it is possible to
// edit a location in the Admin Tools so that it is in two or more categories.
// For simplicity here, it will only show up in the first category it matches,
// but it will identify all other categories that it is in.
// If multi-category locations becomes common, this code should be modified.
class Categories {
	public $locTypes = array();
	private $locAdded = array();  // list of locs added to a category
	public function addLoc($locID, $category) {
		if (!$this->added($locID)) {
			$this->locTypes[$category][] = $locID;
			$this->locAdded[] = $locID;
			return '';
		} else {
			return "<b>Also in $category</b><br />";
		}
	}
	public function added($locID) {
		return in_array($locID, $this->locAdded);
	}
}

// Set any extra information to be displayed with each location
$locText = array();
$categories = new Categories();
foreach ($locations as &$location) {
	$extra = '<span class="small"><br />';
	if ($location->isWeaponSold()) {
		$extra .= $categories->addLoc($location->getTypeID(), 'Weapons');
		$weaponsSold =& $location->getWeaponsSold();
		foreach($weaponsSold as &$weapon) {
			$extra .= $weapon->getName() . '&nbsp;&nbsp;&nbsp;(' . $weapon->getShieldDamage() . '/' . $weapon->getArmourDamage() . '/' . $weapon->getBaseAccuracy() . ')<br />';
		} unset($weapon);
	}
	if ($location->isShipSold()) {
		$extra .= $categories->addLoc($location->getTypeID(), 'Ships');
		$shipsSold =& $location->getShipsSold();
		foreach ($shipsSold as &$shipSold) {
			$extra .= $shipSold['Name'] . '<br />';
		} unset($shipSold);
	}
	if ($location->isHardwareSold()) {
		$extra .= $categories->addLoc($location->getTypeID(), 'Hardware');
		$hardwareSold =& $location->getHardwareSold();
		foreach ($hardwareSold as &$hardware) {
			$extra .= $hardware['Name'] . '<br />';
		} unset($hardware);
	}
	if ($location->isBar()) {
		$extra .= $categories->addLoc($location->getTypeID(), 'Bars');
	}
	if ($location->isBank()) {
		$extra .= $categories->addLoc($location->getTypeID(), 'Banks');
	}
	if ($location->isHQ() || $location->isUG() || $location->isFed()) {
		$extra .= $categories->addLoc($location->getTypeID(), 'Headquarters');
	}
	if (!$categories->added($location->getTypeID())) {
		// Anything that doesn't fit the other categories
		$extra .= $categories->addLoc($location->getTypeID(), 'Miscellaneous');
	}
	$extra .= '</span>';

	$locText[$location->getTypeID()] = $location->getName() . $extra;
} unset($location);
$template->assign('LocText', $locText);
$template->assign('LocTypes', $categories->locTypes);

// Form to make location changes
$container = create_container('1.6/universe_create_save_processing.php',
                              '1.6/universe_create_sectors.php', $var);
$template->assign('CreateLocationsFormHREF', SmrSession::getNewHREF($container));

// HREF to cancel and return to the previous page
$container = create_container('skeleton.php', '1.6/universe_create_sectors.php', $var);
$template->assign('CancelHREF', SmrSession::getNewHREF($container));
