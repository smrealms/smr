<?php declare(strict_types=1);

function check_bargain_number(int $amount, int $ideal_price, int $offered_price, int $bargain_price, Page $container, SmrPlayer $player) : void {
	$var = Smr\Session::getInstance()->getCurrentVar();

	$port = $player->getSectorPort();

	// increase current number of tries
	$container['number_of_bargains'] = isset($var['number_of_bargains']) ? $var['number_of_bargains'] + 1 : 1;

	if (isset($var['overall_number_of_bargains'])) {
		// lose relations for bad bargain
		$player->decreaseRelationsByTrade($amount, $port->getRaceID());
		$player->increaseHOF(1, ['Trade', 'Results', 'Fail'], HOF_PUBLIC);
		// transfer values
		$container->addVar('overall_number_of_bargains');

		// does we have enough of it?
		if ($container['number_of_bargains'] > $container['overall_number_of_bargains']) {
			$player->decreaseRelationsByTrade($amount, $port->getRaceID());
			$player->increaseHOF(1, ['Trade', 'Results', 'Epic Fail'], HOF_PUBLIC);
			throw new Smr\Exceptions\UserError('You don\'t want to accept my offer? I\'m sick of you! Get out of here!');
		}

		$port_off = IRound($offered_price * 100 / $ideal_price);
		$trader_off = IRound($bargain_price * 100 / $ideal_price);

		// get relative numbers!
		// be carefull! one of this value is negative!
		$port_off_rel = 100 - $port_off;
		$trader_off_rel = 100 - $trader_off;

		// only do something, if we are more off than the trader
		if (abs($port_off_rel) > abs($trader_off_rel)) {
			// get a random number between
			// (port_off) and (100 +/- $trader_off_rel)
			if (100 + $trader_off_rel < $port_off) {
				$offer_modifier = rand(100 + $trader_off_rel, $port_off);
			} else {
				$offer_modifier = rand($port_off, 100 + $trader_off_rel);
			}

			$container['offered_price'] = IRound($container['ideal_price'] * $offer_modifier / 100);
		}
	} else {
		$container['overall_number_of_bargains'] = rand(2, 5);
	}
}
