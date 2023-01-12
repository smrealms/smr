<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;

class BondConfirm extends PlayerPage {

	public string $file = 'planet_bond_confirmation.php';

	public function build(AbstractPlayer $player, Template $template): void {

		require_once(LIB . 'Default/planet.inc.php');
		planet_common();

		$planet = $player->getSectorPlanet();

		$template->assign('BondDuration', format_time($planet->getBondTime()));
		$template->assign('ReturnHREF', $planet->getFinancesHREF());
	}

}
