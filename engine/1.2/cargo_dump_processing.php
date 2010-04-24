<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

$good_id = $var["good_id"];
$good_name = $var["good_name"];
$amount = $_REQUEST['amount'];
if (!is_numeric($amount))
	create_error("Numbers only please");

if ($amount <= 0)
	create_error("You must actually enter an ammount > 0!");

//lets make sure there is actually that much on the ship
if ($amount > $ship->cargo[$good_id])
	create_error("You can't dump more than you have.");

if ($sector->has_fed_beacon())
	create_error("You can't dump cargo in a Federal Sector!");

if ($player->turns < 1)
	create_error("You do not have enough turns to dump cargo!");

require_once("shop_goods.inc");

// get the distance
$good_distance = get_good_distance($good_id, "Buy");

$lost_xp = (round($amount / 30) + 1) * 2 * $good_distance;
$player->experience -= $lost_xp;
if ($player->experience < 0)
	$player->experience = 0;

// take turn
$player->take_turns(1);
$player->update();

$ship->cargo[$good_id] -= $amount;
$ship->update_cargo();

// log action
$account->log(6, "Dumps $amount of $good_name and looses $lost_xp experience", $player->sector_id);

$container = array();
$container["url"] = "skeleton.php";
if ($amount > 1)
	$container["msg"] = "You have jettisoned <font color=yellow>$amount</font> units of $good_name and have lost <font color=yellow>$lost_xp</font> experience.";
else
	$container["msg"] = "You have jettisoned <font color=yellow>$amount</font> unit of $good_name and have lost <font color=yellow>$lost_xp</font> experience.";

if ($player->land_on_planet == "TRUE")
	$container["body"] = "planet_main.php";
else
	$container["body"] = "current_sector.php";

forward($container);

?>