<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Globals;
use Smr\Page\PlayerPage;
use Smr\Template;

class BuyShipName extends PlayerPage {

	public string $file = 'buy_ship_name.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$costs = Globals::getBuyShipNameCosts();

		$container = new BuyShipNameProcessor();

		$template->assign('PageTopic', 'Naming Your Ship');
		$template->assign('Costs', $costs);
		$template->assign('ShipNameFormHref', $container->href());
	}

}
