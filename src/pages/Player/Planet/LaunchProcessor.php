<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;

class LaunchProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		if (!$player->isLandedOnPlanet()) {
			create_error('You are not on a planet!');
		}

		$player->setLandedOnPlanet(false);
		$player->update();
		$player->log(LOG_TYPE_MOVEMENT, 'Player launches from planet');
		(new CurrentSector())->go();
	}

}
