<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Exceptions\PathNotFound;
use Smr\Exceptions\SectorNotFound;
use Smr\Page\PlayerPageProcessor;
use Smr\Plotter;
use Smr\Request;
use Smr\Sector;

class PlotCourseConventionalProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly ?int $from = null,
		private readonly ?int $to = null
	) {}

	public function build(AbstractPlayer $player): never {
		$start = $this->from ?? Request::getInt('from');
		$target = $this->to ?? Request::getInt('to');

		// perform some basic checks on both numbers
		if (empty($start) || empty($target)) {
			create_error('Where do you want to go today?');
		}

		if ($start === $target) {
			create_error('Hmmmm...if ' . $start . '=' . $target . ' then that means...YOU\'RE ALREADY THERE! *cough*you\'re real smart*cough*');
		}

		try {
			$startSector = Sector::getSector($player->getGameID(), $start);
			$targetSector = Sector::getSector($player->getGameID(), $target);
		} catch (SectorNotFound) {
			create_error('The sectors have to exist!');
		}

		$player->log(LOG_TYPE_MOVEMENT, 'Player plots to ' . $target . '.');

		try {
			$path = Plotter::findReversiblePathToX($targetSector, $startSector);
		} catch (PathNotFound) {
			create_error('Unable to plot from ' . $startSector->getSectorID() . ' to ' . $targetSector->getSectorID());
		}

		require_once(LIB . 'Default/course_plot.inc.php');
		course_plot_forward($player, $path);
	}

}
