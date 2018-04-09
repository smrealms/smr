<?php
$amount = $_REQUEST['amount'];
if (!is_numeric($amount))
	create_error('Numbers only please');

// only whole numbers allowed
$amount = floor($amount);

$hardware_id = $var['hardware_id'];
$hardware_name = Globals::getHardwareName($hardware_id);
$cost = Globals::getHardwareCost($hardware_id);

// no negative amounts are allowed
if ($amount <= 0)
	create_error('You must actually enter an amount greater than zero!');

// do we have enough cash?
if ($player->getCredits() < $cost * $amount)
	create_error('You don\'t have enough credits to buy '.$amount.' items!');

// chec for max. we can hold!
if ($amount > $ship->getMaxHardware($hardware_id) - $ship->getHardware($hardware_id))
	create_error('You can\'t buy more '.$hardware_name.' than you can transport!');

// take the money from the user
$player->decreaseCredits($cost * $amount);
$player->update();

// now adjust add to ship
$ship->increaseHardware($hardware_id,$amount);
$ship->updateHardware();

$ship->removeUnderAttack();

//HoF
if ($hardware_id == 4) $player->increaseHOF($amount,array('Forces','Bought','Combat Drones'), HOF_ALLIANCE);
if ($hardware_id == 5) $player->increaseHOF($amount,array('Forces','Bought','Scout Drones'), HOF_ALLIANCE);
if ($hardware_id == 6) $player->increaseHOF($amount,array('Forces','Bought','Mines'), HOF_ALLIANCE);

$account->log(LOG_TYPE_HARDWARE, 'Player Buys '.$amount.' '.$hardware_name, $player->getSectorID());

$container = create_container('skeleton.php', 'shop_hardware.php');
transfer('LocationID');
forward($container);
