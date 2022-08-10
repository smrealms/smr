<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;

class PlotCourseCancelProcessor extends PlayerPageProcessor {

	public function build(AbstractSmrPlayer $player): never {
		$player->deletePlottedCourse();

		(new CurrentSector())->go();
	}

}
