<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;

class PlotCourseCancelProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		$player->deletePlottedCourse();

		(new CurrentSector())->go();
	}

}
