<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Epoch;
use Smr\Force;
use Smr\Page\PlayerPageProcessor;
use Smr\Page\ReusableTrait;

class ForcesRefreshAllProcessor extends PlayerPageProcessor {

	use ReusableTrait;

	public function build(AbstractPlayer $player): never {
		// Note: getSectorForces is cached and also called for sector display,
		// so it saves time to call it here instead of a new query.
		$sectorForces = Force::getSectorForces($player->getGameID(), $player->getSectorID());
		$time = Epoch::time();
		foreach ($sectorForces as $sectorForce) {
			if ($player->sharedForceAlliance($sectorForce->getOwner())) {
				$time += Force::REFRESH_ALL_TIME_PER_STACK;
				$sectorForce->updateRefreshAll($player, $time);
			}
		}

		$container = new CurrentSector(showForceRefreshMessage: true);
		$container->go();
	}

}
