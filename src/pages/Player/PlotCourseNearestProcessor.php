<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Plotter;
use Smr\Page\PlayerPageProcessor;
use Smr\PlotGroup;
use Smr\Request;

class PlotCourseNearestProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly mixed $realX = null // for NPCs only
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$sector = $player->getSector();

		if ($this->realX !== null) {
			// This is only used by NPC's
			$realX = $this->realX;
		} else {
			$xType = PlotGroup::from(Request::get('xtype'));
			$X = Request::get('X');
			$realX = Plotter::getX($xType, $X, $player->getGameID(), $player);

			$player->log(LOG_TYPE_MOVEMENT, 'Player plots to nearest ' . $xType->value . ': ' . $X . '.');
		}

		if ($sector->hasX($realX, $player)) {
			create_error('Current sector has what you\'re looking for!');
		}

		$path = Plotter::findReversiblePathToX($realX, $sector, true, $player, $player);

		require_once(LIB . 'Default/course_plot.inc.php');
		course_plot_forward($player, $path);
	}

}
