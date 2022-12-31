<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use AbstractSmrPlayer;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use Smr\TradeGood;

class Stockpile extends PlayerPage {

	use ReusableTrait;

	public string $file = 'planet_stockpile.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		require_once(LIB . 'Default/planet.inc.php');
		planet_common();

		$planet = $player->getSectorPlanet();
		$ship = $player->getShip();

		$goodInfo = [];
		foreach (TradeGood::getAll() as $goodID => $good) {
			if (!$ship->hasCargo($goodID) && !$planet->hasStockpile($goodID)) {
				continue;
			}

			$container = new StockpileProcessor($goodID);

			$goodInfo[] = [
				'Name' => $good->name,
				'ImageHTML' => $good->getImageHTML(),
				'ShipAmount' => $ship->getCargo($goodID),
				'PlanetAmount' => $planet->getStockpile($goodID),
				'DefaultAmount' => min($ship->getCargo($goodID), $planet->getRemainingStockpile($goodID)),
				'HREF' => $container->href(),
			];
		}

		$template->assign('GoodInfo', $goodInfo);
	}

}
