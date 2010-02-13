<?php
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());
require_once(get_file_loc('SmrPort.class.inc'));
require_once('shop_goods.inc');

// creates needed objects
$port =& SmrPort::getPort(SmrSession::$game_id,$player->getSectorID());
$GLOBALS['port'] =& $port;
$amount = get_amount();
$bargain_price = get_bargain_price();

if (!is_numeric($amount))
	create_error('Numbers only please');
if (!is_numeric($bargain_price))
	create_error('Numbers only please');
// get good name, id, ...
$good_id = $var['good_id'];
$good_name = $var['good_name'];
$good_class = $var['good_class'];

// do we have enough turns?
if ($player->getTurns() == 0)
	create_error('You don\'t have enough turns to trade.');

// get rid of those bugs when we die...there is no port at the home sector
if (!$sector->hasPort())
	create_error('I can\'t see a port in this sector. Can you?');

// check if the player has the right relations to trade at the current port
$portRelations = Globals::getRaceRelations(SmrSession::$game_id,$port->getRaceID());
if ($portRelations[$player->getRaceID()] + $player->getRelation($port->getRaceID()) < -300)
	create_error('This port refuses to trade with you because you are at <big><span class="bold red">WAR!</span></big>');

$portGood = $port->getGood($good_id);
// check if there are enough left at port
if ($portGood['Amount'] < $amount)
	create_error('I\'m short of '.$good_name.'. So I\'m not going to sell you '.$amount.' pcs.');

// does we have what we are going to sell?
if ($portGood['TransactionType'] == 'Sell' && $amount > $ship->getCargo($good_id))
	create_error('Scanning your ships indicates you don\'t have '.$amount.' pcs. of '.$good_name.'!');

// check if we have enough room for the thing we are going to buy
if ($portGood['TransactionType'] == 'Buy' && $amount > $ship->getEmptyHolds())
	create_error('Scanning your ships indicates you don\'t have enough free cargo bay!');

// check if the guy has enough money
if ($portGood['TransactionType'] == 'Buy' && $player->getCredits() < $bargain_price)
	create_error('You don\'t have enough credits!');

// get relations for us (global + personal)
$portRelations = Globals::getRaceRelations(SmrSession::$game_id,$port->getRaceID());
$relations = $player->getRelation($port->getRaceID()) + $portRelations[$player->getRaceID()];

$container = array();

$good_distance = max(1,get_good_distance($sector,$good_id, $portGood['TransactionType']));
global $ideal_price;
if (isset($var['ideal_price']))
{
	// transfer this value
	transfer('ideal_price');

	// return this value
	$ideal_price = $var['ideal_price'];

}
else
{
	$ideal_price = $port->getIdealPrice($good_id, $good_distance, $portGood['TransactionType'], $amount, $relations);
	$container['ideal_price'] = $ideal_price;
}
if (isset($var['offered_price'])) {

	// transfer this value
	transfer('offered_price');

	// return this value
	$offered_price = $var['offered_price'];

}
else
{
	$offered_price = $port->getOfferPrice($ideal_price, $relations, $portGood['TransactionType']);
	$container['offered_price'] = $offered_price;
}

// nothing should happen here but just to avoid / by 0
if ($ideal_price == 0 || $offered_price == 0)
	create_error('Port calculation error...buy more goods.');

// can we accept the current price?
if (!empty($bargain_price) &&
	(($portGood['TransactionType'] == 'Buy' && $bargain_price >= $ideal_price) ||
	($portGood['TransactionType'] == 'Sell' && $bargain_price <= $ideal_price)))
{

	// the url we going to
	$container['url'] = 'skeleton.php';

	/*
	$first = pow($relations, 6);
	$second = pow(1000, 6) + .01;

	$factor = ($bargain_price - $offered_price) / (($ideal_price - $offered_price) + .01) + ($first / $second);
	if ($factor > 1)
		$factor = 1;
	elseif ($factor < 0)
		$factor = 0;
	$gained_exp = round( ((((floor($amount / 30)) + 1) * 2) * $factor) * $good_distance);
	*/

	// what does this trader archieved
	/*if ($port->transaction[$good_id] == 'Buy')
		$gained_exp = round($base_xp * ($offered_price - $bargain_price) / ($offered_price - $ideal_price) * ($amount / $ship->getCargoHolds()));
	elseif ($port->transaction[$good_id] == 'Sell')
		$gained_exp = round($base_xp * ($bargain_price - $offered_price) / ($ideal_price - $offered_price) * ($amount / $ship->getCargoHolds()));
	*/

	// base xp is the amount you would get for a perfect trade.
	// this is the absolut max. the real xp can only be smaller.
	$base_xp = (round($ship->getCargoHolds() / 30) + 1) * 2 * $good_distance;

	// if offered equals ideal we get a problem (division by zero)
	$gained_exp = round($port->calculateExperiencePercent($ideal_price,$offered_price,$bargain_price,$portGood['TransactionType']) * $base_xp * $amount / $ship->getCargoHolds());

	//will use these variables in current sector and port after successful trade
	$container['traded_xp'] = $gained_exp;
	$container['traded_amount'] = $amount;
	$container['traded_good'] = $good_name;
	$container['traded_credits'] = $bargain_price;

	if ($portGood['TransactionType'] == 'Buy')
	{
		$container['traded_transaction'] = 'bought';
		$ship->increaseCargo($good_id,$amount);
		$player->decreaseCredits($bargain_price);
		$player->increaseHOF($amount,array('Trade','Goods','Bought'));
		$player->increaseHOF($gained_exp,array('Trade','Experience','Buying'));
		$player->decreaseHOF($bargain_price,array('Trade','Money','Profit'));
		$player->increaseHOF($bargain_price,array('Trade','Money','Buying'));

		$port->buyGoods($portGood,$amount,$bargain_price,$gained_exp);

	}
	elseif ($portGood['TransactionType'] == 'Sell')
	{

		$container['traded_transaction'] = 'sold';
		$ship->decreaseCargo($good_id,$amount);
		$player->increaseCredits($bargain_price);
		$player->increaseHOF($amount,array('Trade','Goods','Sold'));
		$player->increaseHOF($gained_exp,array('Trade','Experience','Selling'));
		$player->increaseHOF($bargain_price,array('Trade','Money','Profit'));
		$player->increaseHOF($bargain_price,array('Trade','Money','Selling'));
		$port->sellGoods($portGood,$amount,$credits_in,$gained_exp);
	}
	$player->increaseHOF($gained_exp,array('Trade','Experience','Total'));
	$player->increaseHOF(1,array('Trade','Results','Success'));

	// log action
	$account->log(6, $portGood['TransactionType'] . 's '.$amount.' '.$good_name.' for '.$bargain_price.' credits and '.$gained_exp.' experience', $player->getSectorID());

	//update ship
	$ship->update_cargo();
	$port->update();

	$player->increaseExperience($gained_exp);

	// change relation for non neutral ports (Alskants get to treat neutrals as an alskant port);
	if ($port->getRaceID() > 1 || $player->getRaceID() == 2)
	{
		$player->increaseRelationsByTrade($amount,$port->getRaceID());
	}

	if ($ship->getEmptyHolds() == 0)
		$container['body'] = 'current_sector.php';
	else
		$container['body'] = 'shop_goods.php';

}
else
{
	// does the trader try to outsmart us?
	check_bargain_number($amount);

	$container['url'] = 'skeleton.php';
	$container['body'] = 'shop_goods_trade.php';

	// transfer values to next page
	transfer('good_id');
	transfer('good_name');
	transfer('good_class');

	$container['amount'] = $amount;
	$container['bargain_price'] = $bargain_price;

}

// only take turns if they bargained
if ($container['number_of_bargains'] != 1)
	$player->takeTurns(1,1);

$player->update();

// go to next page
forward($container);

?>
