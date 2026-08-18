<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class BuyShipNamePreview extends PlayerPage {

	public function __construct(
		private readonly string $shipName,
		private readonly int $cost,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Naming Your Ship';

		$template->pageRenderer = fn() => BuyShipNamePreviewRenderer::render(
			ContinueHREF: new BuyShipNamePreviewProcessor($this->shipName, $this->cost)->href(),
			ShipName: $this->shipName,
		);
	}

}
