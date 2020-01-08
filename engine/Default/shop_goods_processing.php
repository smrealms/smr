<?php declare(strict_types=1);
require_once(LIB . 'Default/shop_goods.inc');

// creates needed objects
$amount = get_amount();
$bargain_price = get_bargain_price();

if (!is_numeric($amount) || !is_numeric($bargain_price)) {
	create_error('Numbers only please!');
}
// get good name, id, ...
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
$transaction = $port->getGoodTransaction($good_id);
if (empty($transaction)) {
	create_error('I don\'t trade in that good.');
}

$portGood = $port->getGood($good_id);
// check if there are enough left at port
if ($port->getGoodAmount($good_id) < $amount) {
	create_error('I\'m short of ' . $good_name . '. So I\'m not going to sell you ' . $amount . ' pcs.');
}

// does we have what we are going to sell?
if ($transaction == 'Sell' && $amount > $ship->getCargo($good_id)) {
	create_error('Scanning your ships indicates you don\'t have ' . $amount . ' pcs. of ' . $good_name . '!');
}

// check if we have enough room for the thing we are going to buy
if ($transaction == 'Buy' && $amount > $ship->getEmptyHolds()) {
	create_error('Scanning your ships indicates you don\'t have enough free cargo bays!');
}

// check if the guy has enough money
if ($transaction == 'Buy' && $player->getCredits() < $bargain_price) {
	create_error('You don\'t have enough credits!');
}

// get relations for us (global + personal)
$relations = $player->getRelation($port->getRaceID());

if (!isset($var['ideal_price'])) {
	SmrSession::updateVar('ideal_price', $port->getIdealPrice($good_id, $transaction, $amount, $relations));
}
$ideal_price = $var['ideal_price'];

if (!isset($var['offered_price'])) {
	SmrSession::updateVar('offered_price', $port->getOfferPrice($ideal_price, $relations, $transaction));
}
$offered_price = $var['offered_price'];

// nothing should happen here but just to avoid / by 0
if ($ideal_price == 0 || $offered_price == 0) {
	create_error('Port calculation error...buy more goods.');
}

if ($_REQUEST['action'] == 'Steal') {
	if (!$ship->isUnderground()) {
		create_error('You are not allowed to steal goods!');
	}
	$transaction = $_REQUEST['action'];

	// Small chance to get caught stealing
	$catchChancePercent = $port->getMaxLevel() - $port->getLevel() + 1;
	if (rand(1, 100) <= $catchChancePercent) {
		$fine = $ideal_price * ($port->getLevel() + 1);
		// Don't take the trader all the way to 0 credits
		$newCredits = max(5000, $player->getCredits() - $fine);
		$player->setCredits($newCredits);
		$player->decreaseAlignment(5);
		$player->decreaseRelationsByTrade($amount, $port->getRaceID());

		$fineMessage = '<span class="red">A Federation patrol caught you loading stolen goods onto your ship!<br />The stolen goods have been confiscated and you have been fined ' . number_format($fine) . ' credits.</span><br /><br />';
		$container = create_container('skeleton.php', 'shop_goods.php');
		$container['trade_msg'] = $fineMessage;
		forward($container);
	}
}

// can we accept the current price?
if ($transaction == 'Steal' ||
	(!empty($bargain_price) &&
	 (($transaction == 'Buy' && $bargain_price >= $ideal_price) ||
	  ($transaction == 'Sell' && $bargain_price <= $ideal_price)))) {

	// the url we going to
	$container = create_container('skeleton.php');
	transfer('ideal_price');
	transfer('offered_price');

	// base xp is the amount you would get for a perfect trade.
	// this is the absolut max. the real xp can only be smaller.
	$base_xp = SmrPort::getBaseExperience($amount, $port->getGoodDistance($good_id));

	// if offered equals ideal we get a problem (division by zero)
	$gained_exp = IRound($port->calculateExperiencePercent($ideal_price, $offered_price, $bargain_price, $transaction) * $base_xp);

	if ($transaction == 'Buy') {
		$msg_transaction = 'bought';
		$ship->increaseCargo($good_id, $amount);
		$player->decreaseCredits($bargain_price);
		$player->increaseHOF($amount, array('Trade', 'Goods', 'Bought'), HOF_ALLIANCE);
		$player->increaseHOF($gained_exp, array('Trade', 'Experience', 'Buying'), HOF_PUBLIC);
		$player->decreaseHOF($bargain_price, array('Trade', 'Money', 'Profit'), HOF_PUBLIC);
		$player->increaseHOF($bargain_price, array('Trade', 'Money', 'Buying'), HOF_PUBLIC);
		$port->buyGoods($portGood, $amount, $ideal_price, $bargain_price, $gained_exp);
		$player->increaseRelationsByTrade($amount, $port->getRaceID());
	} elseif ($transaction == 'Sell') {
		$msg_transaction = 'sold';
		$ship->decreaseCargo($good_id, $amount);
		$player->increaseCredits($bargain_price);
		$player->increaseHOF($amount, array('Trade', 'Goods', 'Sold'), HOF_ALLIANCE);
		$player->increaseHOF($gained_exp, array('Trade', 'Experience', 'Selling'), HOF_PUBLIC);
		$player->increaseHOF($bargain_price, array('Trade', 'Money', 'Profit'), HOF_PUBLIC);
		$player->increaseHOF($bargain_price, array('Trade', 'Money', 'Selling'), HOF_PUBLIC);
		$port->sellGoods($portGood, $amount, $ideal_price, $bargain_price, $gained_exp);
		$player->increaseRelationsByTrade($amount, $port->getRaceID());
	} elseif ($transaction == 'Steal') {
		$msg_transaction = 'stolen';
		$ship->increaseCargo($good_id, $amount);
		$player->increaseHOF($amount, array('Trade', 'Goods', 'Stolen'), HOF_ALLIANCE);
		$player->increaseHOF($gained_exp, array('Trade', 'Experience', 'Stealing'), HOF_PUBLIC);
		$port->stealGoods($portGood, $amount);
	}

	$player->increaseHOF($gained_exp, array('Trade', 'Experience', 'Total'), HOF_PUBLIC);
	$player->increaseHOF(1, array('Trade', 'Results', 'Success'), HOF_PUBLIC);

	// log action
	$account->log(LOG_TYPE_TRADING, $transaction . 's ' . $amount . ' ' . $good_name . ' for ' . $bargain_price . ' credits and ' . $gained_exp . ' experience', $player->getSectorID());

	$player->increaseExperience($gained_exp);

	//will use these variables in current sector and port after successful trade
	$tradeMessage = 'You have just ' . $msg_transaction . ' <span class="yellow">' . $amount . '</span> ' . pluralise('unit', $amount) . ' of <span class="yellow">' . $good_name . '</span>';
	if ($bargain_price > 0) {
		$tradeMessage .= ' for <span class="creds">' . $bargain_price . '</span> ' . pluralise('credit', $bargain_price);
	}
	$tradeMessage .= '.<br />';
	if ($gained_exp > 0) {
		$skill = $transaction == 'Steal' ? 'thievery' : 'trading';
		$tradeMessage .= 'Your excellent ' . $skill . ' skills have earned you <span class="exp">' . $gained_exp . ' </span> experience ' . pluralise('point', $gained_exp) . '!<br />';
	}
	$tradeMessage .= '<br />';

	$container['trade_msg'] = $tradeMessage;

	if ($ship->getEmptyHolds() == 0) {
		$container['body'] = 'current_sector.php';
	} else {
		$container['body'] = 'shop_goods.php';
	}

} else {
	// does the trader try to outsmart us?
	$container = create_container('skeleton.php', 'shop_goods_trade.php');
	transfer('ideal_price');
	transfer('offered_price');
	check_bargain_number($amount, $ideal_price, $offered_price, $bargain_price, $container);

	// transfer values to next page
	transfer('good_id');

	$container['amount'] = $amount;
	$container['bargain_price'] = $bargain_price;
}

// only take turns if they bargained
if (!isset($container['number_of_bargains']) || $container['number_of_bargains'] != 1) {
	$player->takeTurns(TURNS_PER_TRADE, TURNS_PER_TRADE);
}

// go to next page
forward($container);
