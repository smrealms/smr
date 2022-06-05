<?php declare(strict_types=1);

/**
 * This function is called by "Conventional" and "Plot To Nearest" pages.
 */
function course_plot_forward(SmrPlayer $player, Smr\Path $path): never {

	if ($player->getSectorID() == $path->getStartSectorID()) {
		$player->setPlottedCourse($path);

		if (!$player->isLandedOnPlanet()) {
			// If the course can immediately be followed, display it on the current sector page
			$container = Page::create('current_sector.php');
			$container->go();
		}
	}

	$container = Page::create('course_plot_result.php');
	$container['Path'] = $path;
	$container->go();

}
