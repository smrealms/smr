<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\AbstractPlayer;
use Smr\Planet;
use Smr\TradeGood;

class PlanetStockpileProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractPlayer $player): void {
		$planet = $player->getSector()->getPlanet();
		foreach (TradeGood::getAllIDs() as $goodID) {
			$planet->setStockpile($goodID, Planet::MAX_STOCKPILE);
		}
	}

}
