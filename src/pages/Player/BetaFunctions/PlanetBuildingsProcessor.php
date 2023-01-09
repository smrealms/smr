<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\AbstractPlayer;

class PlanetBuildingsProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractPlayer $player): void {
		$planet = $player->getSector()->getPlanet();
		foreach ($planet->getMaxBuildings() as $id => $amount) {
			$planet->setBuilding($id, $amount);
		}
	}

}
