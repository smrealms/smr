<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use Smr\TradeGood;

class CargoDump extends PlayerPage {

	use ReusableTrait;

	public string $file = 'cargo_dump.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$ship = $player->getShip();

		$template->assign('PageTopic', 'Dump Cargo');

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
		$template->assign('Goods', $goods);
	}

}
