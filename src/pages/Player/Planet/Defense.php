<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class Defense extends PlanetPage {

	use ReusableTrait;
	protected function buildPlanetPage(Player $player, Template $template): void {
		$template->pageRenderer = fn() => DefenseRenderer::render(
			TransferShields: new DefenseProcessor(HARDWARE_SHIELDS),
			TransferCDs: new DefenseProcessor(HARDWARE_COMBAT),
			TransferArmour: new DefenseProcessor(HARDWARE_ARMOUR),
			WeaponProcessingPage: new DefenseWeaponProcessor(),
			ThisPlanet: $player->getSectorPlanet(),
			ThisShip: $player->getShip(),
		);
	}

}
