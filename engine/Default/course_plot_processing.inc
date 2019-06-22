<?php
// NOTE: This file is included by "Conventional" and "Plot To Nearest" pages.

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

$container = create_container('skeleton.php', 'course_plot_result.php');
$container['Path'] = $path;
$container['FullPath'] = $fullPath;
forward($container);
