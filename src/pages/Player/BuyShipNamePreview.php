<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;

class BuyShipNamePreview extends PlayerPage {

	public string $file = 'buy_ship_name_preview.php';

	public function __construct(
		private readonly string $shipName,
		private readonly int $cost
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Naming Your Ship');

		$container = new BuyShipNamePreviewProcessor($this->shipName, $this->cost);
		$template->assign('ContinueHREF', $container->href());

		$template->assign('ShipName', $this->shipName);
	}

}
