<?php
// include helper file
require_once(LIB . 'Default/shop_goods.inc');

// create object from port we can work with
$port = $player->getSectorPort();
$template->assign('Port', $port);

$tradeable = checkPortTradeable($port,$player);
if($tradeable!==true)
	create_error($tradeable);

// topic
$template->assign('PageTopic','Port In Sector #'.$player->getSectorID());

$container = create_container('skeleton.php', 'council_list.php');
$container['race_id'] = $port->getRaceID();
$template->assign('CouncilHREF', SmrSession::getNewHREF($container));

$account->log(LOG_TYPE_TRADING, 'Player examines port', $player->getSectorID());
$searchedByFeds = false;

//The player is sent here after trading and sees this if his offer is accepted.
if (!empty($var['trade_msg'])) {
	$template->assign('TradeMsg', $var['trade_msg']);
}
elseif ($player->getLastPort() != $player->getSectorID()) {
	// test if we are searched, but only if we hadn't a previous trade here

	$base_chance = 15;
	if ($port->hasGood(GOODS_SLAVES))
		$base_chance -= 4;
	if ($port->hasGood(GOODS_WEAPONS))
		$base_chance -= 4;
	if ($port->hasGood(GOODS_NARCOTICS))
		$base_chance -= 4;

	if ($ship->isUnderground()) {
		$base_chance -= 4;
	}

	$rand = mt_rand(1, 100);
	if ($rand <= $base_chance) {
		$searchedByFeds = true;
		$player->increaseHOF(1,array('Trade','Search','Total'), HOF_PUBLIC);
		if ($ship->hasIllegalGoods()) {
			$template->assign('IllegalsFound', true);
			$player->increaseHOF(1,array('Trade','Search','Caught','Number Of Times'), HOF_PUBLIC);
			//find the fine
			//get base for ports that dont happen to trade that good
			$GOODS = Globals::getGoods();
			$fine = $totalFine = $port->getLevel() *
			    (($ship->getCargo(GOODS_SLAVES) * $GOODS[GOODS_SLAVES]['BasePrice']) +
			     ($ship->getCargo(GOODS_WEAPONS) * $GOODS[GOODS_WEAPONS]['BasePrice']) +
			     ($ship->getCargo(GOODS_NARCOTICS) * $GOODS[GOODS_NARCOTICS]['BasePrice']));
			$player->increaseHOF($ship->getCargo(GOODS_SLAVES) + $ship->getCargo(GOODS_WEAPONS) + $ship->getCargo(GOODS_NARCOTICS), array('Trade','Search','Caught','Goods Confiscated'), HOF_PUBLIC);
			$player->increaseHOF($totalFine,array('Trade','Search','Caught','Amount Fined'), HOF_PUBLIC);
			if($fine > $player->getCredits()) {
				$fine -= $player->getCredits();
				$player->decreaseCredits($player->getCredits());
				if ($fine > 0) {
					// because credits is 0 it will take money from bank
					$player->decreaseBank(min($fine,$player->getBank()));
					// leave insurance
					if ($player->getBank() < 5000)
						$player->setBank(5000);
				}
			}
			else {
				$player->decreaseCredits($fine);
			}

			//lose align and the good your carrying along with money
			$player->decreaseAlignment(5);

			$ship->setCargo(GOODS_SLAVES, 0);
			$ship->setCargo(GOODS_WEAPONS, 0);
			$ship->setCargo(GOODS_NARCOTICS, 0);
			$account->log(LOG_TYPE_TRADING, 'Player gets caught with illegals', $player->getSectorID());

		}
		else {
			$template->assign('IllegalsFound', false);
			$player->increaseHOF(1,array('Trade','Search','Times Found Innocent'), HOF_PUBLIC);
			$player->increaseAlignment(1);
			$account->log(LOG_TYPE_TRADING, 'Player gains alignment at port', $player->getSectorID());
		}
	}
}
$template->assign('SearchedByFeds', $searchedByFeds);

$player->setLastPort($player->getSectorID());

$container = create_container('shop_goods_processing.php');

$boughtGoods = [];
foreach ($port->getVisibleGoodsBought($player) as $goodID) {
	$good = Globals::getGood($goodID);
	$container['good_id'] = $goodID;
	$good['HREF'] = SmrSession::getNewHREF($container);

	$amount = $port->getGoodAmount($goodID);
	$good['PortAmount'] = $amount;
	if ($amount < $ship->getEmptyHolds()) {
		$good['Amount'] = $amount;
	} else {
		$good['Amount'] = $ship->getEmptyHolds();
	}
	$boughtGoods[$goodID] = $good;
}

$soldGoods = [];
foreach ($port->getVisibleGoodsSold($player) as $goodID) {
	$good = Globals::getGood($goodID);
	$container['good_id'] = $good['ID'];
	$good['HREF'] = SmrSession::getNewHREF($container);

	$amount = $port->getGoodAmount($goodID);
	$good['PortAmount'] = $amount;
	if ($amount < $ship->getCargo($good['ID'])) {
		$good['Amount'] = $amount;
	} else {
		$good['Amount'] = $ship->getCargo($good['ID']);
	}
	$soldGoods[$goodID] = $good;
}

$template->assign('BoughtGoods', $boughtGoods);
$template->assign('SoldGoods', $soldGoods);

$container = create_container('skeleton.php', 'current_sector.php');
$template->assign('LeavePortHREF', SmrSession::getNewHREF($container));
