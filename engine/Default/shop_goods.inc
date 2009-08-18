<?php
require_once(get_file_loc('Plotter.class.inc'));
//$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());

function get_good_distance(SmrSector &$sector, $goodID, $transaction)
{
	global $var, $container;

	// check if we already did this
	if (isset($var['good_distance']))
	{
		// transfer this value
		transfer('good_distance');

		// return this value
		return $var['good_distance'];
	}
	$x = Globals::getGood($goodID);
	switch($transaction)
	{
		case 'Buy':
			$x['TransactionType'] = 'Sell';
		break;
		case 'Sell':
			$x['TransactionType'] = 'Buy';
	}
	$di = Plotter::findDistanceToX($x, $sector, true);
	if(is_object($di))
		$di = $di->getRelativeDistance();
	$container['good_distance'] = $di;
	return $di;
}
function check_bargain_number($amount)
{
	global $var, $container;
	global $offered_price, $ideal_price, $bargain_price;
	global $player;

	$port =& SmrPort::getPort($player->getGameID(),$player->getSectorID());
	
	// increase current number of tries
	$container['number_of_bargains'] = $var['number_of_bargains'] + 1;

	if (isset($var['overall_number_of_bargains']))
	{
		// lose relations for bad bargain
		if ($port->getRaceID() > 1 || $player->getRaceID() == 2)
		{
			$player->decreaseRelationsByTrade($amount,$port->getRaceID());
		}
		$player->increaseHOF(1,array('Trade','Results','Fail'));
		// transfer values
		transfer('overall_number_of_bargains');

		// does we have enough of it?
		if ($container['number_of_bargains'] > $container['overall_number_of_bargains'])
		{
			// change relation for non neutral ports
			if ($port->getRaceID() > 1 || $player->getRaceID() == 2)
			{
				$player->decreaseRelationsByTrade($amount,$port->getRaceID());

				/*what the hell is this doing here Spock??? ;)
				//any player can just go to a port and make their - 500 personal align go to 0
				if ($player->relations[$port->getRaceID()] < 0)
					$player->relations[$port->getRaceID()] = 0;
				*/

			}
			$player->increaseHOF(1,array('Trade','Results','Epic Fail'));
			create_error('You don\'t want to accept my offer? I\'m sick of you! Get out of here!');
		}

		$port_off = round($offered_price * 100 / $ideal_price);
		$trader_off = round($bargain_price * 100 / $ideal_price);

		// get relative numbers!
		// be carefull! one of this value is negative!
		$port_off_rel = 100 - $port_off;
		$trader_off_rel = 100 - $trader_off;

		// only do something, if we are more off than the trader
		if (abs($port_off_rel) > abs($trader_off_rel))
		{
			// get a random number between
			// (port_off) and (100 +/- $trader_off_rel)
			if (100 + $trader_off_rel < $port_off)
				$offer_modifier = mt_rand(100 + $trader_off_rel, $port_off);
			else
				$offer_modifier = mt_rand($port_off, 100 + $trader_off_rel);

			$container['offered_price'] = round($container['ideal_price'] * $offer_modifier / 100);
		}
	}
	else
		$container['overall_number_of_bargains'] = mt_rand(2, 5);
}

function get_amount()
{
	global $var, $_POST;

	// retrieve amount
	if (!isset($var['amount']))
	{
		// we get it from form
		if (isset($_POST['amount']))
			$amount = $_POST['amount'];
		else
			$amount = 0;

		// only numbers
		if (!is_numeric($amount))
			create_error('You must actually enter a number!');

		// we take as it is but round it
		$amount = floor($amount);

		// no negative amounts are allowed
		if ($amount <= 0)
			create_error('You must actually enter an amount > 0!');
	}
	else
		// we get it from session
		$amount = $var['amount'];

	return $amount;

}

function get_bargain_price()
{
	global $_POST;

	// we get it from form
	if (isset($_POST['bargain_price']))
		$price = $_POST['bargain_price'];
	else
		$price = 0;

	// only numbers
	if (!is_numeric($price))
		create_error('You must actually enter a number!');

	// we take as it is but round it
	$price = floor($price);

	// no negative amounts are allowed
	if ($price < 0)
		create_error('No negative prices are allowed!');

	return $price;

}

?>