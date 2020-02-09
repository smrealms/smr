<?php declare(strict_types=1);

function checkPortTradeable($port, $player) {
	if ($port->getSectorID() != $player->getSectorID()) {
		return 'That port is not in this sector!';
	}
	if (!$port->exists()) {
		return 'There is no port in this sector!';
	}
	if ($player->getRelation($port->getRaceID()) <= RELATIONS_WAR) {
		return 'We will not trade with our enemies!';
	}
	if ($port->getReinforceTime() > TIME) {
		return 'We are still repairing damage caused during the last raid.';
	}
	return true;
}

function check_bargain_number($amount, $ideal_price, $offered_price, $bargain_price, &$container) {
	global $var, $player;

	$port = $player->getSectorPort();
	
	// increase current number of tries
	$container['number_of_bargains'] = isset($var['number_of_bargains']) ? $var['number_of_bargains'] + 1 : 1;

	if (isset($var['overall_number_of_bargains'])) {
		// lose relations for bad bargain
		$player->decreaseRelationsByTrade($amount, $port->getRaceID());
		$player->increaseHOF(1, array('Trade', 'Results', 'Fail'), HOF_PUBLIC);
		// transfer values
		transfer('overall_number_of_bargains');

		// does we have enough of it?
		if ($container['number_of_bargains'] > $container['overall_number_of_bargains']) {
			$player->decreaseRelationsByTrade($amount, $port->getRaceID());
			$player->increaseHOF(1, array('Trade', 'Results', 'Epic Fail'), HOF_PUBLIC);
			create_error('You don\'t want to accept my offer? I\'m sick of you! Get out of here!');
		}

		$port_off = round($offered_price * 100 / $ideal_price);
		$trader_off = round($bargain_price * 100 / $ideal_price);

		// get relative numbers!
		// be carefull! one of this value is negative!
		$port_off_rel = 100 - $port_off;
		$trader_off_rel = 100 - $trader_off;

		// only do something, if we are more off than the trader
		if (abs($port_off_rel) > abs($trader_off_rel)) {
			// get a random number between
			// (port_off) and (100 +/- $trader_off_rel)
			if (100 + $trader_off_rel < $port_off) {
				$offer_modifier = mt_rand(100 + $trader_off_rel, $port_off);
			} else {
				$offer_modifier = mt_rand($port_off, 100 + $trader_off_rel);
			}

			$container['offered_price'] = round($container['ideal_price'] * $offer_modifier / 100);
		}
	} else {
		$container['overall_number_of_bargains'] = mt_rand(2, 5);
	}
}
