<?php
require_once('shop_goods.inc');

// creates needed objects
$amount = get_amount();
$bargain_price = get_bargain_price();

if (!is_numeric($amount) || !is_numeric($bargain_price))
	create_error('Numbers only please!');
// get good name, id, ...
$good_id = $var['good_id'];
$good_name = Globals::getGoodName($good_id);

// do we have enough turns?
if ($player->getTurns() == 0)
	create_error('You don\'t have enough turns to trade.');

$sector =& $player->getSector();
// get rid of those bugs when we die...there is no port at the home sector
if (!$sector->hasPort())
	create_error('I can\'t see a port in this sector. Can you?');
$port =& $sector->getPort();

// check if the player has the right relations to trade at the current port
if ($player->getRelation($port->getRaceID()) < -300)
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
	create_error('Scanning your ships indicates you don\'t have enough free cargo bays!');

// check if the guy has enough money
if ($portGood['TransactionType'] == 'Buy' && $player->getCredits() < $bargain_price)
	create_error('You don\'t have enough credits!');

// get relations for us (global + personal)
$relations = $player->getRelation($port->getRaceID());

if (!isset($var['ideal_price'])) SmrSession::updateVar('ideal_price',$port->getIdealPrice($good_id, $portGood['TransactionType'], $amount, $relations));
transfer('ideal_price');
$ideal_price = $var['ideal_price'];

if (!isset($var['offered_price'])) SmrSession::updateVar('offered_price',$port->getOfferPrice($ideal_price, $relations, $portGood['TransactionType']));
transfer('offered_price');
$offered_price = $var['offered_price'];

// nothing should happen here but just to avoid / by 0
if ($ideal_price == 0 || $offered_price == 0)
	create_error('Port calculation error...buy more goods.');

// can we accept the current price?
if (!empty($bargain_price) &&
	(($portGood['TransactionType'] == 'Buy' && $bargain_price >= $ideal_price) ||
	($portGood['TransactionType'] == 'Sell' && $bargain_price <= $ideal_price)))
{

	// the url we going to
	$container = create_container('skeleton.php');

	// base xp is the amount you would get for a perfect trade.
	// this is the absolut max. the real xp can only be smaller.
	$base_xp = (round($ship->getCargoHolds() / 30) + 1) * 2 * $port->getGoodDistance($good_id);

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
		$player->increaseHOF($amount,array('Trade','Goods','Bought'), HOF_PUBLIC);
		$player->increaseHOF($gained_exp,array('Trade','Experience','Buying'), HOF_PUBLIC);
		$player->decreaseHOF($bargain_price,array('Trade','Money','Profit'), HOF_PUBLIC);
		$player->increaseHOF($bargain_price,array('Trade','Money','Buying'), HOF_PUBLIC);

		$port->buyGoods($portGood,$amount,$bargain_price,$gained_exp);

	}
	elseif ($portGood['TransactionType'] == 'Sell')
	{

		$container['traded_transaction'] = 'sold';
		$ship->decreaseCargo($good_id,$amount);
		$player->increaseCredits($bargain_price);
		$player->increaseHOF($amount,array('Trade','Goods','Sold'), HOF_PUBLIC);
		$player->increaseHOF($gained_exp,array('Trade','Experience','Selling'), HOF_PUBLIC);
		$player->increaseHOF($bargain_price,array('Trade','Money','Profit'), HOF_PUBLIC);
		$player->increaseHOF($bargain_price,array('Trade','Money','Selling'), HOF_PUBLIC);
		$port->sellGoods($portGood,$amount,$bargain_price,$gained_exp);
	}
	$player->increaseHOF($gained_exp,array('Trade','Experience','Total'), HOF_PUBLIC);
	$player->increaseHOF(1,array('Trade','Results','Success'), HOF_PUBLIC);

	// log action
	$account->log(6, $portGood['TransactionType'] . 's '.$amount.' '.$good_name.' for '.$bargain_price.' credits and '.$gained_exp.' experience', $player->getSectorID());

	$player->increaseExperience($gained_exp);

	// change relation for non neutral ports (Alskants get to treat neutrals as an alskant port);
	if ($port->getRaceID() != RACE_NEUTRAL || $player->getRaceID() == RACE_ALSKANT)
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
	check_bargain_number($amount,$ideal_price,$offered_price,$bargain_price);

	$container = create_container('skeleton.php', 'shop_goods_trade.php');

	// transfer values to next page
	transfer('good_id');

	$container['amount'] = $amount;
	$container['bargain_price'] = $bargain_price;
}

// only take turns if they bargained
if (!isset($container['number_of_bargains'])||$container['number_of_bargains'] != 1)
	$player->takeTurns(TURNS_PER_TRADE,TURNS_PER_TRADE);

// go to next page
forward($container);

?>
