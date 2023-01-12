<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\AbstractPlayer;

class PlanetDefensesProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractPlayer $player): void {
		$planet = $player->getSector()->getPlanet();
		$planet->setShields($planet->getMaxShields());
		$planet->setCDs($planet->getMaxCDs());
		$planet->setArmour($planet->getMaxArmour());
	}

}
