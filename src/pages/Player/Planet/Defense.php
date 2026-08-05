<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class Defense extends PlanetPage {

	use ReusableTrait;

	public string $file = 'planet_defense.php';

	protected function buildPlanetPage(Player $player, Template $template): void {
		$container = new DefenseProcessor(HARDWARE_SHIELDS);
		$template->assign('TransferShields', $container);

		$container = new DefenseProcessor(HARDWARE_COMBAT);
		$template->assign('TransferCDs', $container);

		$container = new DefenseProcessor(HARDWARE_ARMOUR);
		$template->assign('TransferArmour', $container);

		$container = new DefenseWeaponProcessor();
		$template->assign('WeaponProcessingPage', $container);
	}

}
