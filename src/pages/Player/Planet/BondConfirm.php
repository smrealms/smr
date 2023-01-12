<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\AbstractPlayer;
use Smr\Template;

class BondConfirm extends PlanetPage {

	public string $file = 'planet_bond_confirmation.php';

	protected function buildPlanetPage(AbstractPlayer $player, Template $template): void {
		$planet = $player->getSectorPlanet();

		$template->assign('BondDuration', format_time($planet->getBondTime()));
		$template->assign('ReturnHREF', $planet->getFinancesHREF());
	}

}
