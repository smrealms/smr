<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;
use Smr\TradeGood;

class CargoDump extends PlayerPage {

	use ReusableTrait;

	public function build(Player $player, Template $template): void {
		$ship = $player->getShip();

		$template->pageTopic = 'Dump Cargo';

		$goods = [];
		foreach ($ship->getCargo() as $goodID => $amount) {
			$container = new CargoDumpProcessor($goodID);
			$good = TradeGood::get($goodID);
			$goods[] = [
				'image' => $good->getImageHTML(),
				'name' => $good->name,
				'amount' => $amount,
				'dump_href' => $container->href(),
			];
		}

		$template->pageRenderer = fn() => CargoDumpRenderer::render($goods);
	}

}
