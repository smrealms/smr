<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use AbstractSmrPlayer;
use Smr\Request;
use Smr\SectorLock;
use SmrSector;

class SetSectorProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractSmrPlayer $player): void {
		$sector_to = Request::getInt('sector_to');
		if (!SmrSector::sectorExists($player->getGameID(), $sector_to)) {
			create_error('Sector ID is not in any galaxy.');
		}
		$player->setSectorID($sector_to);
		$player->setLandedOnPlanet(false);
		// Update sector lock
		$player->update();
		$lock = SectorLock::getInstance();
		$lock->release();
		$lock->acquireForPlayer($player);
	}

}
