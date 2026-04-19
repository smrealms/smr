<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPageProcessor;
use Smr\Player;

class PlotCourseCancelProcessor extends PlayerPageProcessor {

	public function build(Player $player): never {
		$player->deletePlottedCourse();

		(new CurrentSector())->go();
	}

}
