<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Exceptions\PathNotFound;
use Smr\Page\PlayerPageProcessor;
use Smr\PlotGroup;
use Smr\Plotter;
use Smr\Request;

class PlotCourseNearestProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		$sector = $player->getSector();

		$xType = PlotGroup::from(Request::get('xtype'));
		$X = Request::get('X');
		$realX = Plotter::getX($xType, $X, $player->getGameID(), $player);

		$player->log(LOG_TYPE_MOVEMENT, 'Player plots to nearest ' . $xType->value . ': ' . $X . '.');

		if ($sector->hasX($realX, $player)) {
			create_error('Current sector has what you\'re looking for!');
		}

		try {
			$path = Plotter::findReversiblePathToX($realX, $sector, $player, $player);
		} catch (PathNotFound) {
			create_error('Unable to find what you\'re looking for! It either hasn\'t been added to this game or you haven\'t explored it yet.');
		}

		require_once(LIB . 'Default/course_plot.inc.php');
		course_plot_forward($player, $path);
	}

}
