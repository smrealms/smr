<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);
		require_once(get_file_loc("smr_port.inc"));
$player->get_relations();
// Yay for another hack...
$GLOBALS['port'] = null;
$GLOBALS['good_id'] = null;
$GLOBALS['amount'] = null;
$GLOBALS['good_distance'] = null;
$GLOBALS['relations'] = null;
$GLOBALS['ideal_price'] = null;
$GLOBALS['offered_price'] = null;
$GLOBALS['ideal_price'] = null;
$GLOBALS['bargain_price'] = null;
$GLOBALS['port'] = null;
$GLOBALS['amount'] = null;

global $port, $good_id, $amount, $good_distance, $relations, $ideal_price, $offered_price, $ideal_price, $bargain_price, $amount;

// initialize random generator.
mt_srand((double)microtime()*1000000);

require_once("shop_goods.inc");

// creates needed objects
$port = new SMR_PORT($player->sector_id, SmrSession::$game_id);

$amount = get_amount();
$bargain_price = get_bargain_price();

if (!is_numeric($amount))
	create_error("Numbers only please");
if (!is_numeric($bargain_price))
	create_error("Numbers only please");
// get good name, id, ...
$good_id = $var["good_id"];
$good_name = $var["good_name"];
$good_class = $var["good_class"];

// do we have enough turns?
if ($player->turns == 0)
	create_error("You don't have enough turns to trade.");

// get rid of those bugs when we die...there is no port at the home sector
if (!$sector->has_port())
	create_error("I can't see a port in this sector. Can you?");

// check if the player has the right relations to trade at the current port
if ($player->relations_global_rev[$port->race_id] + $player->relations[$port->race_id] < -300)
	create_error("This port refuses to trade with you because you are at <big><b style=\"color:red;\">WAR!</b></big>");

// check if there are enough left at port
if ($port->amount[$good_id] < $amount)
	create_error("I'm short of $good_name. So i'm not going to sell you $amount pcs.");

// does we have what we are going to sell?
if ($port->transaction[$good_id] == 'Sell' && $amount > $ship->cargo[$good_id])
	create_error("Scanning your ships indicates you don't have $amount pcs. of $good_name!");

// check if we have enough room for the thing we are going to buy
if ($port->transaction[$good_id] == 'Buy' && $amount > $ship->cargo_left)
	create_error("Scanning your ships indicates you don't have enough free cargo bay!");

// check if the guy has enough money
if ($port->transaction[$good_id] == "Buy" && $player->credits < $bargain_price)
	create_error("You don't have enough credits!");

// get relations for us (global + personal)
$relations = $player->relations[$port->race_id] + $player->relations_global_rev[$port->race_id];

$container = array();

$good_distance = get_good_distance($good_id, $port->transaction[$good_id]);
$ideal_price = get_ideal_price($good_id);
$offered_price = get_offered_price($good_id);

// nothing should happen here but just to avoid / by 0
if ($ideal_price == 0 || $offered_price == 0)
	create_error("Port calculation error...buy more goods.");

// can we accept the current price?
if (!empty($bargain_price) &&
	($port->transaction[$good_id] == 'Buy' && $bargain_price >= $ideal_price ||
	 $port->transaction[$good_id] == 'Sell' && $bargain_price <= $ideal_price)) {

	// the url we going to
	$container["url"] = "skeleton.php";

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
		$gained_exp = round($base_xp * ($offered_price - $bargain_price) / ($offered_price - $ideal_price) * ($amount / $ship->hardware[HARDWARE_CARGO]));
	elseif ($port->transaction[$good_id] == 'Sell')
		$gained_exp = round($base_xp * ($bargain_price - $offered_price) / ($ideal_price - $offered_price) * ($amount / $ship->hardware[HARDWARE_CARGO]));
	*/

	// base xp is the amount you would get for a perfect trade.
	// this is the absolut max. the real xp can only be smaller.
	$base_xp = (round($ship->hardware[HARDWARE_CARGO] / 30) + 1) * 2 * $good_distance;

	// if offered equals ideal we get a problem (division by zero)
	if ($bargain_price != $ideal_price && $offered_price != $ideal_price) {
		//print("$offered_price, $ideal_price, $bargain_price");
		if ($port->transaction[$good_id] == "Buy") {
			if ($offered_price - $bargain_price < 0)
				$val = 0;
			else
				$val = abs($offered_price - $bargain_price);
		} else {
			if ($offered_price - $bargain_price > 0)
				$val = 0;
			else
				$val = abs($offered_price - $bargain_price);
		}
		$gained_exp = round($base_xp * $val / abs($offered_price - $ideal_price) * $amount / $ship->hardware[HARDWARE_CARGO]);
	} else
		$gained_exp = round($base_xp * $amount / $ship->hardware[HARDWARE_CARGO]);

	//will use these variables in current sector and port after successful trade
	$container["traded_xp"] = $gained_exp;
	$container["traded_amount"] = $amount;
	$container["traded_good"] = $good_name;
	$container["traded_credits"] = $bargain_price;

	if ($port->transaction[$good_id] == 'Buy') {

		$container["traded_transaction"] = "bought";
		$ship->cargo[$good_id] += $amount;
		$player->credits -= $bargain_price;
		$player->update_stat("trade_profit",$bargain_price * -1);

		$cap = $amount * 1000;

		if($bargain_price > $cap) {
			$credits_in = $cap;
		}
		else {
			$credits_in = $bargain_price;
		}

		$port->upgrade += $credits_in;
		$port->credits += $credits_in;

	} elseif ($port->transaction[$good_id] == 'Sell') {

		$container["traded_transaction"] = "sold";
		$ship->cargo[$good_id] -= $amount;
		$player->credits += $bargain_price;
		$player->update_stat("trade_profit",$bargain_price);
		$player->update_stat("trade_sales",$bargain_price);

	}

	// log action
	$account->log(6, $port->transaction[$good_id] . "s $amount $good_name for $bargain_price credits and $gained_exp experience.", $player->sector_id);

	//update ship
	$ship->update_cargo();

	// take the number of goods from port
	$port->amount[$good_id] -= $amount;

	// now refresh lower lvled. goods
	// get get all goods with good_class - 1
	if ($good_class < 3) {

		$db->query("SELECT * FROM good WHERE good_class = " . ($good_class + 1));
		while ($db->next_record()) {

			$id = $db->f("good_id");
			if (isset($port->amount[$id])) {

				$port->amount[$id] += round($amount / 4);
				if ($port->amount[$id] > $port->max_amount[$id])
					$port->amount[$id] = $port->max_amount[$id];

			}

		}

	}

	$port->gained_experience += $gained_exp;
	if ($port->upgrade >= 10000000)
		$port->upgrade();
	$port->update();

	$player->experience += $gained_exp;
	$player->update_stat("experience_traded", $gained_exp);
	$player->update_stat("goods_traded", $amount);

	// change relation for non neutral ports (Alskants get to treat neutrals as an alskant port);
	if ($port->race_id > 1 || $player->race_id == 2) {

		$relation_modifier = round($amount / 30);
		if ($relation_modifier > 10)
			$relation_modifier = 10;

		$player->relations[$port->race_id] += $relation_modifier;

		if ($player->relations[$port->race_id] > 500)
			$player->relations[$port->race_id] = 500;

	}

	if ($ship->cargo_left == 0)
		$container["body"] = "current_sector.php";
	else
		$container["body"] = "shop_goods.php";

} else {

	// does the trader tries to outsmart us?
	check_bargain_number();

	$container["url"] = "skeleton.php";
	$container["body"] = "shop_goods_trade.php";

	// transfer values to next page
	transfer("good_id");
	transfer("good_name");
	transfer("good_class");

	$container["amount"] = $amount;
	$container["bargain_price"] = $bargain_price;

}

// only take turns if they bargained
if ($container["number_of_bargains"] != 1)
	$player->take_turns(1);

$player->update();

// go to next page
forward($container);

?>
