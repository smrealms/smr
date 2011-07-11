<?php
require_once(get_file_loc('Plotter.class.inc'));

function checkPortTradeable(&$port,&$player)
{
	if($port->getSectorID()!=$player->getSectorID())
		return 'That port is not in this sector!';
	
	if(!$port->exists())
		return 'There is no port in this sector!';
	
	// total relations with that race (personal + global)
	$portRelations = Globals::getRaceRelations(SmrSession::$game_id,$port->getRaceID());
	$relations = $player->getRelation($port->getRaceID()) + $portRelations[$player->getRaceID()];
	if (empty($relations))
		$relations = 0;
	
	if($relations <= -300)
		return 'We will not trade with our enemies!';
	
	if($port->getReinforceTime() > TIME)
		return 'We are still repairing damage caused during the last raid.';
	
	
	return true;
}

function check_bargain_number($amount,$ideal_price,$offered_price,$bargain_price)
{
	global $var, $container;
	global $player;

	$port =& SmrPort::getPort($player->getGameID(),$player->getSectorID());
	
	// increase current number of tries
	$container['number_of_bargains'] = isset($var['number_of_bargains']) ? $var['number_of_bargains'] + 1 : 1;

	if (isset($var['overall_number_of_bargains']))
	{
		// lose relations for bad bargain
		if ($port->getRaceID() > 1 || $player->getRaceID() == 2)
		{
			$player->decreaseRelationsByTrade($amount,$port->getRaceID());
		}
		$player->increaseHOF(1,array('Trade','Results','Fail'), HOF_PUBLIC);
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
			$player->increaseHOF(1,array('Trade','Results','Epic Fail'), HOF_PUBLIC);
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
	global $var, $_REQUEST;

	// retrieve amount
	if (isset($var['amount']))
		$amount = $var['amount'];
	else if (isset($_REQUEST['amount']))
		$amount = $_REQUEST['amount'];
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

	return $amount;
}

function get_bargain_price()
{
	global $_REQUEST,$var;

	// we get it from form
	if (isset($_REQUEST['bargain_price']))
		$price = $_REQUEST['bargain_price'];
	else if (isset($var['bargain_price']))
		$price = $var['bargain_price'];
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