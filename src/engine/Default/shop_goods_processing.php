<?php declare(strict_types=1);

use Smr\TransactionType;

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();
$ship = $player->getShip();
$sector = $player->getSector();

$amount = Smr\Request::getVarInt('amount');
// no negative amounts are allowed
if ($amount <= 0) {
	create_error('You must enter an amount > 0!');
}

$bargain_price = Smr\Request::getVarInt('bargain_price', 0);
// no negative amounts are allowed
if ($bargain_price < 0) {
	create_error('Negative prices are not allowed!');
}

/** @var int $good_id */
$good_id = $var['good_id'];
$good_name = Globals::getGoodName($good_id);

// do we have enough turns?
if ($player->getTurns() == 0) {
	create_error('You don\'t have enough turns to trade.');
}

// get rid of those bugs when we die...there is no port at the home sector
if (!$sector->hasPort()) {
	create_error('I can\'t see a port in this sector. Can you?');
}
$port = $sector->getPort();

// check if the player has the right relations to trade at the current port
if ($player->getRelation($port->getRaceID()) < RELATIONS_WAR) {
	create_error('This port refuses to trade with you because you are at <span class="big bold red">WAR!</span>');
}

// does the port actually buy or sell this good?
if (!$port->hasGood($good_id)) {
	create_error('I don\'t trade in that good.');
}

// check if there are enough left at port
if ($port->getGoodAmount($good_id) < $amount) {
	create_error('I\'m short of ' . $good_name . '. So I\'m not going to sell you ' . $amount . ' pcs.');
}

$transaction = $port->getGoodTransaction($good_id);

// does we have what we are going to sell?
if ($transaction === TransactionType::Sell && $amount > $ship->getCargo($good_id)) {
	create_error('Scanning your ship indicates you don\'t have ' . $amount . ' pcs. of ' . $good_name . '!');
}

// check if we have enough room for the thing we are going to buy
if ($transaction === TransactionType::Buy && $amount > $ship->getEmptyHolds()) {
	create_error('Scanning your ship indicates you don\'t have enough free cargo bays!');
}

// check if the guy has enough money
if ($transaction === TransactionType::Buy && $player->getCredits() < $bargain_price) {
	create_error('You don\'t have enough credits!');
}

// get relations for us (global + personal)
$relations = $player->getRelation($port->getRaceID());

if (!isset($var['ideal_price'])) {
	$var['ideal_price'] = $port->getIdealPrice($good_id, $transaction, $amount, $relations);
}
$ideal_price = $var['ideal_price'];

if (!isset($var['offered_price'])) {
	$var['offered_price'] = $port->getOfferPrice($ideal_price, $relations, $transaction);
}
$offered_price = $var['offered_price'];

// nothing should happen here but just to avoid / by 0
if ($ideal_price == 0 || $offered_price == 0) {
	create_error('Port calculation error...buy more goods.');
}

$stealing = false;
if (Smr\Request::getVar('action') === TransactionType::STEAL) {
	$stealing = true;
	if (!$ship->isUnderground()) {
		throw new Exception('Player tried to steal in a non-underground ship!');
	}
	if ($transaction !== TransactionType::Buy) {
		throw new Exception('Player tried to steal a good the port does not sell!');
	}

	// Small chance to get caught stealing
	$catchChancePercent = $port->getMaxLevel() - $port->getLevel() + 1;
	if (rand(1, 100) <= $catchChancePercent) {
		$fine = $ideal_price * ($port->getLevel() + 1);
		// Don't take the trader all the way to 0 credits
		$newCredits = max(5000, $player->getCredits() - $fine);
		$player->setCredits($newCredits);
		$player->decreaseAlignment(5);
		$player->decreaseRelationsByTrade($amount, $port->getRaceID());

		$fineMessage = '<span class="red">A Federation patrol caught you loading stolen goods onto your ship!<br />The stolen goods have been confiscated and you have been fined ' . number_format($fine) . ' credits.</span>';
		$container = Page::create('shop_goods.php');
		$container['trade_msg'] = $fineMessage;
		$container->go();
	}
}

// can we accept the current price?
if ($stealing ||
	(!empty($bargain_price) &&
	 (($transaction === TransactionType::Buy && $bargain_price >= $ideal_price) ||
	  ($transaction === TransactionType::Sell && $bargain_price <= $ideal_price)))) {

	// base xp is the amount you would get for a perfect trade.
	// this is the absolut max. the real xp can only be smaller.
	$base_xp = SmrPort::getBaseExperience($amount, $port->getGoodDistance($good_id));

	// if offered equals ideal we get a problem (division by zero)
	if ($stealing) {
		$expPercent = 1; // stealing gives full exp
	} else {
		$expPercent = $port->calculateExperiencePercent($ideal_price, $bargain_price, $transaction);
	}
	$gained_exp = IRound($expPercent * $base_xp);

	$portGood = Globals::getGood($good_id);
	if ($stealing) {
		$msg_transaction = 'stolen';
		$ship->increaseCargo($good_id, $amount);
		$player->increaseHOF($amount, ['Trade', 'Goods', 'Stolen'], HOF_ALLIANCE);
		$player->increaseHOF($gained_exp, ['Trade', 'Experience', 'Stealing'], HOF_PUBLIC);
		$port->stealGoods($portGood, $amount);
	} elseif ($transaction === TransactionType::Buy) {
		$msg_transaction = 'bought';
		$ship->increaseCargo($good_id, $amount);
		$player->decreaseCredits($bargain_price);
		$player->increaseHOF($amount, ['Trade', 'Goods', 'Bought'], HOF_ALLIANCE);
		$player->increaseHOF($gained_exp, ['Trade', 'Experience', 'Buying'], HOF_PUBLIC);
		$player->decreaseHOF($bargain_price, ['Trade', 'Money', 'Profit'], HOF_PUBLIC);
		$player->increaseHOF($bargain_price, ['Trade', 'Money', 'Buying'], HOF_PUBLIC);
		$port->buyGoods($portGood, $amount, $ideal_price, $bargain_price, $gained_exp);
		$player->increaseRelationsByTrade($amount, $port->getRaceID());
	} else { // $transaction === TransactionType::Sell
		$msg_transaction = 'sold';
		$ship->decreaseCargo($good_id, $amount);
		$player->increaseCredits($bargain_price);
		$player->increaseHOF($amount, ['Trade', 'Goods', 'Sold'], HOF_ALLIANCE);
		$player->increaseHOF($gained_exp, ['Trade', 'Experience', 'Selling'], HOF_PUBLIC);
		$player->increaseHOF($bargain_price, ['Trade', 'Money', 'Profit'], HOF_PUBLIC);
		$player->increaseHOF($bargain_price, ['Trade', 'Money', 'Selling'], HOF_PUBLIC);
		$port->sellGoods($portGood, $amount, $gained_exp);
		$player->increaseRelationsByTrade($amount, $port->getRaceID());
	}

	$player->increaseHOF($gained_exp, ['Trade', 'Experience', 'Total'], HOF_PUBLIC);
	$player->increaseHOF(1, ['Trade', 'Results', 'Success'], HOF_PUBLIC);

	// log action
	$logAction = $stealing ? TransactionType::STEAL : $transaction->value;
	$player->log(LOG_TYPE_TRADING, $logAction . 's ' . $amount . ' ' . $good_name . ' for ' . $bargain_price . ' credits and ' . $gained_exp . ' experience');

	$player->increaseExperience($gained_exp);

	//will use these variables in current sector and port after successful trade
	$tradeMessage = 'You have just ' . $msg_transaction . ' <span class="yellow">' . $amount . '</span> ' . pluralise($amount, 'unit', false) . ' of <span class="yellow">' . $good_name . '</span>';
	if ($bargain_price > 0) {
		$tradeMessage .= ' for <span class="creds">' . $bargain_price . '</span> ' . pluralise($bargain_price, 'credit', false) . '.';
	}

	if ($gained_exp > 0) {
		if ($stealing) {
			$qualifier = 'cunning';
		} elseif ($gained_exp < $base_xp * 0.25) {
			$qualifier = 'novice';
		} elseif ($gained_exp < $base_xp * 0.5) {
			$qualifier = 'mediocre';
		} elseif ($gained_exp < $base_xp * 0.75) {
			$qualifier = 'respectable';
		} elseif ($gained_exp < IRound($base_xp)) {
			$qualifier = 'excellent';
		} else {
			$qualifier = 'peerless';
		}
		$skill = $stealing ? 'thievery' : 'trading';
		$tradeMessage .= '<br />Your ' . $qualifier . ' ' . $skill . ' skills have earned you <span class="exp">' . $gained_exp . ' </span> ' . pluralise($gained_exp, 'experience point', false) . '!';
	}


	if ($ship->getEmptyHolds() == 0) {
		$container = Page::create('current_sector.php');
	} else {
		$container = Page::create('shop_goods.php');
	}
	$container->addVar('ideal_price');
	$container->addVar('offered_price');
	$container['trade_msg'] = $tradeMessage;

} else {
	// does the trader try to outsmart us?
	$container = Page::create('shop_goods_trade.php');
	$container->addVar('ideal_price');
	$container->addVar('offered_price');

	require_once(LIB . 'Default/shop_goods.inc.php');
	check_bargain_number($amount, $ideal_price, $offered_price, $bargain_price, $container, $player);

	// transfer values to next page
	$container->addVar('good_id');

	$container['amount'] = $amount;
	$container['bargain_price'] = $bargain_price;
}

// only take turns if they bargained
if (!isset($container['number_of_bargains']) || $container['number_of_bargains'] != 1) {
	$player->takeTurns(TURNS_PER_TRADE, TURNS_PER_TRADE);
}

// go to next page
$container->go();
