<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\AbstractPlayer;
use Smr\Page\ReusableTrait;
use Smr\Template;
use Smr\TradeGood;

class Stockpile extends PlanetPage {

	use ReusableTrait;

	public string $file = 'planet_stockpile.php';

	protected function buildPlanetPage(AbstractPlayer $player, Template $template): void {
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
