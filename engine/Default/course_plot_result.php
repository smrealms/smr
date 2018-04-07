<?php
// Load the Distance object to do the common processing
// for both "Conventional" and "Plot To Nearest".
$path = $var['Distance'];

// Throw start sector away (it's useless for the route),
// but save the full path in case we end up needing to display it.
$fullPath = implode(' - ', $path->getPath());
$path->removeStart();

// now get the sector we are going to but don't remove it (sector_move_processing does it)
$next_sector = $path->getNextOnPath();

if ($player->getSector()->isLinked($next_sector)) {

	// save this to db (if we still have something)
	if ($path->getTotalSectors()>0) {
		$player->setPlottedCourse($path);
	}

	if (!$player->isLandedOnPlanet()) {
		// If the course can immediately be followed, display it on the current sector page
		$container = create_container('skeleton.php', 'current_sector.php');
		forward($container);
	}
}

$template->assign('PageTopic', 'Plot A Course');
require_once(get_file_loc('menu.inc'));
create_nav_menu($template, $player);

$template->assign('Path', $path);
$template->assign('FullPath', $fullPath);

?>
