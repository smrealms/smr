<?php
require_once(get_file_loc('SmrSector.class.inc'));
require_once(get_file_loc('Plotter.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());

$template->assign('PageTopic','Plot A Course');

// create menu
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'course_plot.php';
$menue_items[] = create_link($container, 'Plot a Course');
if(!$player->isLandedOnPlanet()) {
	$container['body'] = 'map_local.php';
	$menue_items[] = create_link($container, 'Local Map');
}
$menue_items[] = '<a href="' . URL . '/map_galaxy.php" target="_blank">Galaxy Map</a>';

// echo it
$PHP_OUTPUT.= create_menue($menue_items);

$path = unserialize($var['Distance']);

$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0" style="width:100%;border:none"><tr><td style="padding:0px;vertical-align:top">';
$PHP_OUTPUT.=('The plotted course is ' . $path->getTotalSectors() . ' sectors long and '.$path->getTurns().' turns.');
$PHP_OUTPUT.= '</td><td style="padding:0px;vertical-align:top;width:32em">';

// get the array back

$full = (implode(' - ', $path->getPath()));

// throw start sector away
// it's useless for the route
$path->removeStart();

// now get the sector we are going to but don't remove it (sector_move_processing does it)
$next_sector = $path->getNextOnPath();

if ($next_sector == $sector->getLinkUp() ||
	$next_sector == $sector->getLinkDown() ||
	$next_sector == $sector->getLinkLeft() ||
	$next_sector == $sector->getLinkRight() ||
	$next_sector == $sector->getWarp()) {

	// save this to db (if we still have something
	if ($path->getTotalSectors()>0)
	{
		$player->setPlottedCourse($path);
	}

	//$PHP_OUTPUT.=create_echo_form($container);
	if (!$player->isLandedOnPlanet())
	{
		$container = array();
		$container['url'] = 'sector_move_processing.php';
		$container['target_page'] = 'current_sector.php';
		$container['target_sector'] = $next_sector;

		$PHP_OUTPUT.=create_button($container, 'Follow plotted course - ' .  $next_sector . ' (' . $path->getTotalSectors() . ')');
	
		if ($ship->hasScanner()) {
	
			$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;');
			$container = array();
			$container['url']			= 'skeleton.php';
			$container['body']			= 'sector_scan.php';
			$container['target_sector'] = $next_sector;
			$PHP_OUTPUT.=create_button($container, 'Scan');
	
		}
	}
}

$PHP_OUTPUT.= '</td></tr></table><br /><h2>Plotted Course</h2><br />';
$PHP_OUTPUT.= $full;

?>