<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class HardwareConfigure extends PlayerPage {

	use ReusableTrait;
	public function build(Player $player, Template $template): void {
		$ship = $player->getShip();

		$template->pageTopic = 'Configure Hardware';

		$toggleCloakHREF = $ship->hasCloak() ?
			new HardwareConfigureCloakProcessor(disable: $ship->isCloaked())->href() : null;

		if ($ship->hasIllusion()) {
			$setIllusionFormHREF = new HardwareConfigureIllusionProcessor(disable: false)->href();
			$disableIllusionHREF = new HardwareConfigureIllusionProcessor(disable: true)->href();

			$ships = [];
			$db = Database::getInstance();
			$dbResult = $db->select('ship_type', [], ['ship_type_id', 'ship_name']);
			foreach ($dbResult->records() as $dbRecord) {
				$ships[$dbRecord->getInt('ship_type_id')] = $dbRecord->getString('ship_name');
			}
		} else {
			$setIllusionFormHREF = null;
			$disableIllusionHREF = null;
			$ships = null;
		}

		$jumpDrivePage = $ship->hasJump() ? new SectorJumpProcessor() : null;

		$template->pageRenderer = fn() => HardwareConfigureRenderer::render(
			ToggleCloakHREF: $toggleCloakHREF,
			SetIllusionFormHREF: $setIllusionFormHREF,
			IllusionShips: $ships,
			DisableIllusionHref: $disableIllusionHREF,
			JumpDrivePage: $jumpDrivePage,
			ThisShip: $player->getShip(),
		);
	}

}
