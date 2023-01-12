<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class Defense extends PlayerPage {

	use ReusableTrait;

	public string $file = 'planet_defense.php';

	public function build(AbstractPlayer $player, Template $template): void {
		require_once(LIB . 'Default/planet.inc.php');
		planet_common();

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
