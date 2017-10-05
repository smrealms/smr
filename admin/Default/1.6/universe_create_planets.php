<?php
require_once(get_file_loc('SmrGalaxy.class.inc'));

// Get a list of all available planet types
$allowedTypes = array();
$db->query('SELECT * FROM planet_type');
while ($db->nextRecord()) {
	$allowedTypes[$db->getInt('planet_type_id')] = $db->getField('planet_type_name');
}
$template->assignByRef('AllowedTypes', $allowedTypes);

// Initialize all planet counts to zero
$numberOfPlanets = array();
foreach (array_keys($allowedTypes) as $ID) {
	$numberOfPlanets[$ID] = 0;
}

// Get the current number of each type of planet
$galaxy =& SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
$galSectors =& $galaxy->getSectors();
foreach ($galSectors as &$galSector) {
	if($galSector->hasPlanet()) {
		$numberOfPlanets[$galSector->getPlanet()->getTypeID()]++;
	}
}

$template->assignByRef('Galaxy', $galaxy);
$template->assignByRef('NumberOfPlanets', $numberOfPlanets);

$numberOfNpcPlanets = (isset($planet_info['NPC']) ? $planet_info['NPC'] : 0);
$template->assign('NumberOfNpcPlanets', $numberOfNpcPlanets);

// Form to make planet changes
$container = create_container('1.6/universe_create_save_processing.php',
                              '1.6/universe_create_sectors.php', $var);
$template->assign('CreatePlanetsFormHREF', SmrSession::getNewHREF($container));

// HREF to cancel and return to the previous page
$container = create_container('skeleton.php', '1.6/universe_create_sectors.php', $var);
$template->assign('CancelHREF', SmrSession::getNewHREF($container));

?>
