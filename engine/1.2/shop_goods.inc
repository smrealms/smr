<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

$player->get_relations();
function get_good_distance($good_id, $transaction) {

	global $var, $container;

	// check if we already did this
	if (isset($var["good_distance"])) {

		// transfer this value
		transfer("good_distance");

		// return this value
		return $var["good_distance"];

	}

	global $player;

	// if we buy a good we're looking for the nearest sector that sells that good
	if ($transaction == 'Buy')
		$neg_transaction = 'Sell';
	elseif ($transaction == 'Sell')
		$neg_transaction = 'Buy';

	// initialize the queue. all sectors we have to visit are in here
	$sector_queue = array();

	// keeps the distance to the start sector
	$sector_distance = array();

	// putting start sector in queue
	array_push($sector_queue, $player->sector_id);

	// it has a null distance
	$sector_distance[$player->sector_id] = 0;

	$good_distance = 0;
	while (sizeof($sector_queue) > 0) {

		// get current sector and
		$curr_sector_id = array_shift($sector_queue);

		// get the distance for this sector from the start sector
		$distance = $sector_distance[$curr_sector_id];

		// create a new sector object
		$curr_sector = new SMR_SECTOR($curr_sector_id, $player->game_id, $player->account_id);

		// does the current sector buy/sell the good we're looking for?
        if ($good_distance != 0) {
			if ($curr_sector->has_good($good_id, $neg_transaction) && $distance < $good_distance)
				$good_distance = $distance;
        } else {
			if ($curr_sector->has_good($good_id, $neg_transaction))
				$good_distance = $distance;
        }

		// if we already found a port that buy or sell our product we don't need
		// to go further than this one.
		if ($good_distance != 0 && $good_distance <= $distance) continue;

		// enqueue all neighbours
		if ($curr_sector->link_up > 0 && (!isset($sector_distance[$curr_sector->link_up]) || $sector_distance[$curr_sector->link_up] > $distance + 1)) {

			array_push($sector_queue, $curr_sector->link_up);
			$sector_distance[$curr_sector->link_up] = $distance + 1;

		}

		if ($curr_sector->link_down > 0 && (!isset($sector_distance[$curr_sector->link_down]) || $sector_distance[$curr_sector->link_down] > $distance + 1)) {

			array_push($sector_queue, $curr_sector->link_down);
			$sector_distance[$curr_sector->link_down] = $distance + 1;

		}

		if ($curr_sector->link_left > 0 && (!isset($sector_distance[$curr_sector->link_left]) || $sector_distance[$curr_sector->link_left] > $distance + 1)) {

			array_push($sector_queue, $curr_sector->link_left);
			$sector_distance[$curr_sector->link_left] = $distance + 1;

		}

		if ($curr_sector->link_right > 0 && (!isset($sector_distance[$curr_sector->link_right]) || $sector_distance[$curr_sector->link_right] > $distance + 1)) {

			array_push($sector_queue, $curr_sector->link_right);
			$sector_distance[$curr_sector->link_right] = $distance + 1;

		}

		if ($curr_sector->warp > 0 && (!isset($sector_distance[$curr_sector->warp]) || $sector_distance[$curr_sector->warp] > $distance + 5)) {

			array_push($sector_queue, $curr_sector->warp);
			$sector_distance[$curr_sector->warp] = $distance + 5;

		}

	}

	$container["good_distance"] = $good_distance;

	return $good_distance;
}

function get_ideal_price() {

	global $var, $container;

	// check if we already did this
	if (isset($var["ideal_price"])) {

		// transfer this value
		transfer("ideal_price");

		// return this value
		return $var["ideal_price"];

	}

	global $port, $good_id, $amount, $good_distance, $relations, $offered_price;

	$base		= $port->base_price[$good_id];
	$supply		= $port->amount[$good_id];
	$max_supply	= $port->max_amount[$good_id];
	$holds		= $amount;
	$dist		= $good_distance;
	$port_lvl	= $port->level;

	$relations_factor_buy = 2-(($relations + 500) / 1500);
	$relations_factor_sell = ($relations + 500) / 1500;

	if ($port->transaction[$good_id] == 'Sell') {

		//$ideal_price = round( $holds * $base * 0.65 * (pow($dist + 0.5, 2)) * (2.5-($supply / $max_supply)) * (($relations + 350) / 1650) / 5.1);
		$ideal_price = round( ($holds * $base * 0.65 * (pow($dist + 0.5, 2)) * (2.5-($supply / $max_supply)) * (($relations + 350) / 1650) / 5.1)*.65);

	} elseif ($port->transaction[$good_id] == 'Buy') {

		//$ideal_price = round( $holds * $base * 0.65 * $dist * $dist * (2.5-($supply / $max_supply)) * (2-($relations + 50) / 850) * (($relations + 350) / 1500) / 8 / 4.82 * (1 + (10 - $port->level) / 50));
		$ideal_price = round( ($holds * $base * 0.65 * $dist * $dist * (2.5-($supply / $max_supply)) * (2-($relations + 50) / 850) * (($relations + 350) / 1500) / 8 / 4.82 * (1 + (10 - $port->level) / 50))*.65);
	}

	// save value
	$container["ideal_price"] = $ideal_price;

	// return it
	return $ideal_price;

}

function get_offered_price() {

	global $var, $container;

	// check if we already did this
	if (isset($var["offered_price"])) {

		// transfer this value
		transfer("offered_price");

		// return this value
		return $var["offered_price"];

	}

	global $port, $good_id, $amount, $good_distance, $relations, $ideal_price;

	$base		= $port->base_price[$good_id];
	$supply		= $port->amount[$good_id];
	$max_supply	= $port->max_amount[$good_id];
	$holds		= $amount;
	$dist		= $good_distance;
	$port_lvl	= $port->level;

	$relations_factor_buy = 2-(($relations + 500) / 1500);
	$relations_factor_sell = ($relations + 500) / 1500;
	$range = .11 - .09;
	$rand = .09 + $range * mt_rand(0, 32767)/32767;

	if ($port->transaction[$good_id] == "Sell") {

		$offered_price = round( $ideal_price * (($relations - 1250) / 10000 + 1 * (pow($relations / 500 + 1, $rand)) - ($relations / 9500)) );

	} elseif ($port->transaction[$good_id] == "Buy") {

		$offered_price = round( $ideal_price * (($relations - 1250) / 10000 + 1 * (pow($relations / 500 + 1, $rand)) - ($relations / 2500) + .35) );

	}

	// save value
	$container["offered_price"] = $offered_price;

	// return it
	return $offered_price;

}

function check_bargain_number() {

	global $var, $container;
	global $offered_price, $ideal_price, $bargain_price;
	global $port, $amount, $player;

	// increase current number of tries
	$container["number_of_bargains"] = $var["number_of_bargains"] + 1;

	if (isset($var["overall_number_of_bargains"])) {

		// transfer values
		transfer("overall_number_of_bargains");

		// does we have enough of it?
		if ($container["number_of_bargains"] > $container["overall_number_of_bargains"]) {

			// change relation for non neutral ports
			if ($port->race_id > 1) {

				$relation_modifier = round($amount / 30);
				if ($relation_modifier > 10)
					$relation_modifier = 10;

				$player->relations[$port->race_id] -= $relation_modifier;

				/*what the hell is this doing here Spock??? ;)
				//any player can just go to a port and make their - 500 personal align go to 0
				if ($player->relations[$port->race_id] < 0)
					$player->relations[$port->race_id] = 0;
				*/
				$player->update();

			}

			create_error("You don't want to accept my offer? I'm sick of you! Get out of here!");

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
			if (100 + $trader_off_rel < $port_off)
				$offer_modifier = mt_rand(100 + $trader_off_rel, $port_off);
			else
				$offer_modifier = mt_rand($port_off, 100 + $trader_off_rel);

			$container["offered_price"] = round($container["ideal_price"] * $offer_modifier / 100);

		}

	} else
		$container["overall_number_of_bargains"] = mt_rand(2, 5);

}

function get_amount() {

	global $var, $_POST;

	// retrieve amount
	if (!isset($var["amount"])) {

		// we get it from form
		if (isset($_POST["amount"]))
			$amount = $_POST["amount"];
		else
			$amount = 0;

		// only numbers
		if (!is_numeric($amount))
			create_error("You must actually enter a number!");

		// we take as it is but round it
		$amount = floor($amount);

		// no negative amounts are allowed
		if ($amount <= 0)
			create_error("You must actually enter an amount > 0!");

	} else
		// we get it from session
		$amount = $var["amount"];

	return $amount;

}

function get_bargain_price() {

	global $_POST;

	// we get it from form
	if (isset($_POST["bargain_price"]))
		$price = $_POST["bargain_price"];
	else
		$price = 0;

	// only numbers
	if (!is_numeric($price))
		create_error("You must actually enter a number!");

	// we take as it is but round it
	$price = floor($price);

	// no negative amounts are allowed
	if ($price < 0)
		create_error("No negative prices are allowed!");

	return $price;

}

?>