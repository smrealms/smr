<?php
$amount = $_REQUEST['amount'];
if (!is_numeric($amount))
	create_error("Numbers only please");
    
// only whole numbers allowed
$amount = floor($amount);

$hardware_id	= $var["hardware_id"];
$hardware_name	= $var["hardware_name"];
$cost			= $var["cost"];

// no negative amounts are allowed
if ($amount <= 0)
	create_error("You must actually enter an amount greater than zero!");

// do we have enough cash?
if ($player->credits < $cost * $amount)
	create_error("You don't have enough credits to buy $amount items!");

// chec for max. we can hold!
if ($amount > $ship->max_hardware[$hardware_id] - $ship->hardware[$hardware_id])
	create_error("You can't buy more $hardware_name than you can transport!");

// take the money from the user
$player->credits -= ($cost * $amount);
$player->update();

// now adjust add to ship
$ship->hardware[$hardware_id] += $amount;
$ship->old_hardware[$hardware_id] = $ship->hardware[$hardware_id];
$ship->update_hardware();

$ship->mark_seen();

//HoF
if ($hardware_id == 4) $player->update_stat("combat_drones", $amount);
if ($hardware_id == 5) $player->update_stat("scout_drones", $amount);
if ($hardware_id == 6) $player->update_stat("mines", $amount);

$account->log(10, "Player Buys $amount $hardware_name", $player->sector_id);

forward(create_container("skeleton.php", "shop_hardware.php"));

?>