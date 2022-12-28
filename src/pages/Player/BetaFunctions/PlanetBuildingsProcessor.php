<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use AbstractSmrPlayer;

class PlanetBuildingsProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractSmrPlayer $player): void {
		$planet = $player->getSector()->getPlanet();
		foreach ($planet->getMaxBuildings() as $id => $amount) {
			$planet->setBuilding($id, $amount);
		}
	}

}
