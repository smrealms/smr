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
		$template->assign('TransferShieldsHref', $container->href());

		$container = new DefenseProcessor(HARDWARE_COMBAT);
		$template->assign('TransferCDsHref', $container->href());

		$container = new DefenseProcessor(HARDWARE_ARMOUR);
		$template->assign('TransferArmourHref', $container->href());

		$container = new DefenseWeaponProcessor();
		$template->assign('WeaponProcessingHREF', $container->href());
	}

}
