<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\Player;

class PlanetDefensesProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(Player $player): void {
		$player->getSectorPlanet()->setDefensesToMax();
	}

}
