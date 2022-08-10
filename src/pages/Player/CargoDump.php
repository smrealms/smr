<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Globals;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class CargoDump extends PlayerPage {

	use ReusableTrait;

	public string $file = 'cargo_dump.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$ship = $player->getShip();

		$template->assign('PageTopic', 'Dump Cargo');

		if ($ship->hasCargo()) {

			$goods = [];
			foreach ($ship->getCargo() as $goodID => $amount) {
				$container = new CargoDumpProcessor($goodID);
				$goods[] = [
					'image' => Globals::getGood($goodID)['ImageLink'],
					'name' => Globals::getGood($goodID)['Name'],
					'amount' => $amount,
					'dump_href' => $container->href(),
				];
			}

			$template->assign('Goods', $goods);
		}
	}

}
