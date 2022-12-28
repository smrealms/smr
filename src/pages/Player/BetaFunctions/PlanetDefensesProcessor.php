<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use AbstractSmrPlayer;

class PlanetDefensesProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractSmrPlayer $player): void {
		$planet = $player->getSector()->getPlanet();
		$planet->setShields($planet->getMaxShields());
		$planet->setCDs($planet->getMaxCDs());
		$planet->setArmour($planet->getMaxArmour());
	}

}
