<?php
require_once(get_file_loc('Plotter.class.inc'));
$sector =& $player->getSector();

$template->assign('PageTopic','Plot A Course');

require_once(get_file_loc('menu.inc'));
create_nav_menu($template, $player);

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

if ($sector->isLinked($next_sector)) {

	// save this to db (if we still have something
	if ($path->getTotalSectors()>0) {
		$player->setPlottedCourse($path);
	}

	if (!$player->isLandedOnPlanet()) {
		// If the course can immediately be followed, display it on the current sector page
		$container = create_container('skeleton.php', 'current_sector.php');
		forward($container);
	}
}

$PHP_OUTPUT.= '</td></tr></table><br /><h2>Plotted Course</h2><br />';
$PHP_OUTPUT.= $full;

?>
