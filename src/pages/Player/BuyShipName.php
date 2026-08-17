<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Globals;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class BuyShipName extends PlayerPage {

	public function build(Player $player, Template $template): void {
		$costs = Globals::getBuyShipNameCosts();

		$template->pageTopic = 'Naming Your Ship';

		$template->pageRenderer = fn() => BuyShipNameRenderer::render(
			Costs: $costs,
			ProcessorPage: new BuyShipNameProcessor(),
		);
	}

}
