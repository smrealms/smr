<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\Player;
use Smr\Template;

class BondConfirm extends PlanetPage {

	protected function buildPlanetPage(Player $player, Template $template): void {
		$planet = $player->getSectorPlanet();

		$template->pageRenderer = fn() => BondConfirmRenderer::render(
			CancelHREF: new Financial()->href(),
			ConfirmHREF: new BondProcessor()->href(),
			BondDuration: format_time($planet->getBondTime()),
		);
	}

}
