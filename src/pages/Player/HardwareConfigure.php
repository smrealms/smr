<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class HardwareConfigure extends PlayerPage {

	use ReusableTrait;

	public string $file = 'configure_hardware.php';

	public function build(Player $player, Template $template): void {
		$ship = $player->getShip();

		$template->assign('PageTopic', 'Configure Hardware');

		if ($ship->hasCloak()) {
			$container = new HardwareConfigureCloakProcessor(disable: $ship->isCloaked());
			$template->assign('ToggleCloakHREF', $container->href());
		}

		if ($ship->hasIllusion()) {
			$container = new HardwareConfigureIllusionProcessor(disable: false);
			$template->assign('SetIllusionFormHREF', $container->href());

			$ships = [];
			$db = Database::getInstance();
			$dbResult = $db->select('ship_type', [], ['ship_type_id', 'ship_name']);
			foreach ($dbResult->records() as $dbRecord) {
				$ships[$dbRecord->getInt('ship_type_id')] = $dbRecord->getString('ship_name');
			}
			$template->assign('IllusionShips', $ships);
			$container = new HardwareConfigureIllusionProcessor(disable: true);
			$template->assign('DisableIllusionHref', $container->href());
		}

		if ($ship->hasJump()) {
			$container = new SectorJumpProcessor();
			$template->assign('JumpDrivePage', $container);
		}
	}

}
