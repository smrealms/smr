<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use AbstractSmrPlayer;
use Globals;
use SmrPlanet;

class PlanetStockpileProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractSmrPlayer $player): void {
		$planet = $player->getSector()->getPlanet();
		foreach (Globals::getGoods() as $goodID => $good) {
			$planet->setStockpile($goodID, SmrPlanet::MAX_STOCKPILE);
		}
	}

}
