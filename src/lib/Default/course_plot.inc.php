<?php declare(strict_types=1);
// NOTE: This file is included by "Conventional" and "Plot To Nearest" pages.

if ($player->getSectorID() == $path->getStartSectorID()) {
	$player->setPlottedCourse($path);

	if (!$player->isLandedOnPlanet()) {
		// If the course can immediately be followed, display it on the current sector page
		$container = Page::create('skeleton.php', 'current_sector.php');
		$container->go();
	}
}

$container = Page::create('skeleton.php', 'course_plot_result.php');
$container['Path'] = $path;
$container->go();
