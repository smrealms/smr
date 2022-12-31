<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use AbstractSmrPlayer;
use Smr\TradeGood;
use SmrPlanet;

class PlanetStockpileProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractSmrPlayer $player): void {
		$planet = $player->getSector()->getPlanet();
		foreach (TradeGood::getAllIDs() as $goodID) {
			$planet->setStockpile($goodID, SmrPlanet::MAX_STOCKPILE);
		}
	}

}
