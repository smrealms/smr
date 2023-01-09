<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class HardwareConfigure extends PlayerPage {

	use ReusableTrait;

	public string $file = 'configure_hardware.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$ship = $player->getShip();

		$template->assign('PageTopic', 'Configure Hardware');

		if ($ship->hasCloak()) {
			if (!$ship->isCloaked()) {
				$action = 'Enable Cloak';
			} else {
				$action = 'Disable Cloak';
			}
			$container = new HardwareConfigureProcessor($action);
			$template->assign('ToggleCloakHREF', $container->href());
		}

		if ($ship->hasIllusion()) {
			$container = new HardwareConfigureProcessor('Set Illusion');
			$template->assign('SetIllusionFormHREF', $container->href());

			$ships = [];
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT ship_type_id,ship_name FROM ship_type ORDER BY ship_name');
			foreach ($dbResult->records() as $dbRecord) {
				$ships[$dbRecord->getInt('ship_type_id')] = $dbRecord->getString('ship_name');
			}
			$template->assign('IllusionShips', $ships);
			$container = new HardwareConfigureProcessor('Disable Illusion');
			$template->assign('DisableIllusionHref', $container->href());
		}

		if ($ship->hasJump()) {
			$container = new SectorJumpProcessor();
			$template->assign('JumpDriveFormLink', $container->href());
		}
	}

}
