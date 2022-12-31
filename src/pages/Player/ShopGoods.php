<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;
use Smr\TradeGood;

class ShopGoods extends PlayerPage {

	public string $file = 'shop_goods.php';

	public function __construct(
		private readonly ?string $tradeMessage = null
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$ship = $player->getShip();

		// create object from port we can work with
		$port = $player->getSectorPort();

		$tradeRestriction = $port->getTradeRestriction($player);
		if ($tradeRestriction !== false) {
			create_error($tradeRestriction);
		}

		// topic
		$template->assign('PageTopic', 'Port In Sector #' . $player->getSectorID());
		$template->assign('Port', $port);

		$player->log(LOG_TYPE_TRADING, 'Player examines port');
		$searchedByFeds = false;

		//The player is sent here after trading and sees this if his offer is accepted.
		$template->assign('TradeMsg', $this->tradeMessage);

		if ($player->getLastPort() != $player->getSectorID()) {
			// test if we are searched, but only if we hadn't a previous trade here

			$baseChance = PORT_SEARCH_BASE_CHANCE;
			if ($port->hasGood(GOODS_SLAVES)) {
				$baseChance -= PORT_SEARCH_REDUCTION_PER_EVIL_GOOD;
			}
			if ($port->hasGood(GOODS_WEAPONS)) {
				$baseChance -= PORT_SEARCH_REDUCTION_PER_EVIL_GOOD;
			}
			if ($port->hasGood(GOODS_NARCOTICS)) {
				$baseChance -= PORT_SEARCH_REDUCTION_PER_EVIL_GOOD;
			}

			if ($ship->isUnderground()) {
				$baseChance -= PORT_SEARCH_REDUCTION_FOR_EVIL_SHIP;
			}

			$rand = rand(1, 100);
			if ($rand <= $baseChance) {
				$searchedByFeds = true;
				$player->increaseHOF(1, ['Trade', 'Search', 'Total'], HOF_PUBLIC);
				if ($ship->hasIllegalGoods()) {
					$template->assign('IllegalsFound', true);
					$player->increaseHOF(1, ['Trade', 'Search', 'Caught', 'Number Of Times'], HOF_PUBLIC);
					//find the fine
					//get base for ports that dont happen to trade that good
					$fine = $totalFine = $port->getLevel() *
					    (($ship->getCargo(GOODS_SLAVES) * TradeGood::get(GOODS_SLAVES)->basePrice) +
					     ($ship->getCargo(GOODS_WEAPONS) * TradeGood::get(GOODS_WEAPONS)->basePrice) +
					     ($ship->getCargo(GOODS_NARCOTICS) * TradeGood::get(GOODS_NARCOTICS)->basePrice));
					$player->increaseHOF($ship->getCargo(GOODS_SLAVES) + $ship->getCargo(GOODS_WEAPONS) + $ship->getCargo(GOODS_NARCOTICS), ['Trade', 'Search', 'Caught', 'Goods Confiscated'], HOF_PUBLIC);
					$player->increaseHOF($totalFine, ['Trade', 'Search', 'Caught', 'Amount Fined'], HOF_PUBLIC);
					$template->assign('TotalFine', $totalFine);

					if ($fine > $player->getCredits()) {
						$fine -= $player->getCredits();
						$player->decreaseCredits($player->getCredits());
						if ($fine > 0) {
							// because credits is 0 it will take money from bank
							$player->decreaseBank(min($fine, $player->getBank()));
							// leave insurance
							if ($player->getBank() < 5000) {
								$player->setBank(5000);
							}
						}
					} else {
						$player->decreaseCredits($fine);
					}

					//lose align and the good your carrying along with money
					$player->decreaseAlignment(5);

					$ship->setCargo(GOODS_SLAVES, 0);
					$ship->setCargo(GOODS_WEAPONS, 0);
					$ship->setCargo(GOODS_NARCOTICS, 0);
					$player->log(LOG_TYPE_TRADING, 'Player gets caught with illegals');

				} else {
					$template->assign('IllegalsFound', false);
					$player->increaseHOF(1, ['Trade', 'Search', 'Times Found Innocent'], HOF_PUBLIC);
					$player->increaseAlignment(1);
					$player->log(LOG_TYPE_TRADING, 'Player gains alignment at port');
				}
			}
		}
		$template->assign('SearchedByFeds', $searchedByFeds);

		$player->setLastPort($player->getSectorID());

		$boughtGoods = [];
		foreach ($port->getVisibleGoodsBought($player) as $goodID => $good) {
			$portAmount = $port->getGoodAmount($goodID);
			$boughtGoods[$goodID] = [
				'HREF' => (new ShopGoodsProcessor($goodID))->href(),
				'Image' => $good->getImageHTML(),
				'Name' => $good->name,
				'BasePrice' => $good->basePrice,
				'PortAmount' => $portAmount,
				'Amount' => min($portAmount, $ship->getEmptyHolds()),
			];
		}

		$soldGoods = [];
		foreach ($port->getVisibleGoodsSold($player) as $goodID => $good) {
			$portAmount = $port->getGoodAmount($goodID);
			$soldGoods[$goodID] = [
				'HREF' => (new ShopGoodsProcessor($goodID))->href(),
				'Image' => $good->getImageHTML(),
				'Name' => $good->name,
				'BasePrice' => $good->basePrice,
				'PortAmount' => $portAmount,
				'Amount' => min($portAmount, $ship->getCargo($goodID)),
			];
		}

		$template->assign('BoughtGoods', $boughtGoods);
		$template->assign('SoldGoods', $soldGoods);

		$container = new CurrentSector();
		$template->assign('LeavePortHREF', $container->href());
	}

}
