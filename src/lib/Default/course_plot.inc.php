<?php declare(strict_types=1);

use Smr\Pages\Player\CurrentSector;
use Smr\Pages\Player\PlotCourseResult;
use Smr\Path;
use Smr\Player;

/**
 * This function is called by "Conventional" and "Plot To Nearest" pages.
 */
function course_plot_forward(Player $player, Path $path): never {

	if ($player->getSectorID() === $path->getStartSectorID()) {
		$player->setPlottedCourse($path);

		if (!$player->isLandedOnPlanet()) {
			// If the course can immediately be followed, display it on the current sector page
			$container = new CurrentSector();
			$container->go();
		}
	}

	$container = new PlotCourseResult($path);
	$container->go();

}
