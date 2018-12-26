<?php
// Load the Distance object to do the common processing
// for both "Conventional" and "Plot To Nearest".
$path = unserialize($var['Distance']);

// Throw start sector away (it's useless for the route),
// but save the full path in case we end up needing to display it.
$fullPath = implode(' - ', $path->getPath());
$startSectorID = $path->removeStart();

if ($player->getSectorID() == $startSectorID) {
	$player->setPlottedCourse($path);

	if (!$player->isLandedOnPlanet()) {
		// If the course can immediately be followed, display it on the current sector page
		$container = create_container('skeleton.php', 'current_sector.php');
		forward($container);
	}
}

$template->assign('PageTopic', 'Plot A Course');
Menu::navigation($template, $player);

$template->assign('Path', $path);
$template->assign('FullPath', $fullPath);
