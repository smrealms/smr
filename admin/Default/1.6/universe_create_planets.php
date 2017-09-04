<?php
require_once(get_file_loc('SmrGalaxy.class.inc'));

$galaxy =& SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
$galSectors =& $galaxy->getSectors();
//get totals
$numberOfPlanets=0;
foreach ($galSectors as &$galSector) {
	if($galSector->hasPlanet()) {
		$numberOfPlanets++;
	}
}

$template->assignByRef('Galaxy', $galaxy);
$template->assign('NumberOfPlanets', $numberOfPlanets);

$numberOfNpcPlanets = (isset($planet_info['NPC']) ? $planet_info['NPC'] : 0);
$template->assign('NumberOfNpcPlanets', $numberOfNpcPlanets);

// Form to make planet changes
$container = create_container('1.6/universe_create_save_processing.php',
                              '1.6/universe_create_sectors.php', $var);
$template->assign('Form', create_echo_form($container));

// HREF to cancel and return to the previous page
$container = create_container('skeleton.php', '1.6/universe_create_sectors.php', $var);
$template->assign('CancelHREF', SmrSession::getNewHREF($container));

?>
