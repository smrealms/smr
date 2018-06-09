<?php
$action = $_REQUEST['action'];
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

if ($action == 'Buy') {
	// do we have enough cash?
	if ($player->getCredits() < $cost * $amount) {
		create_error('You don\'t have enough credits to buy '.$amount.' items!');
	}

	// chec for max. we can hold!
	if ($amount > $ship->getMaxHardware($hardware_id) - $ship->getHardware($hardware_id)) {
		create_error('You can\'t buy more '.$hardware_name.' than you can transport!');
	}

	$player->decreaseCredits($cost * $amount);
	$ship->increaseHardware($hardware_id, $amount);

	//HoF
	if ($hardware_id == HARDWARE_COMBAT) $player->increaseHOF($amount,array('Forces','Bought','Combat Drones'), HOF_ALLIANCE);
	if ($hardware_id == HARDWARE_SCOUT) $player->increaseHOF($amount,array('Forces','Bought','Scout Drones'), HOF_ALLIANCE);
	if ($hardware_id == HARDWARE_MINE) $player->increaseHOF($amount,array('Forces','Bought','Mines'), HOF_ALLIANCE);
}
else if ($action == 'Sell') {
	// We only allow selling combat drones
	if ($hardware_id != HARDWARE_COMBAT) {
		throw new Exception('This item cannot be sold!');
	}

	// Make sure we have the specified amount to sell
	if ($amount > $ship->getCDs()) {
		create_error('You can\'t sell more '.$hardware_name.' than you have aboard your ship!');
	}

	$player->increaseCredits(round($cost * CDS_REFUND_PERCENT) * $amount);
	$ship->decreaseCDs($amount, true);  // 2nd arg avoids under attack warning
}
else {
	throw new Exception('Action must be either Buy or Sell.');
}

$account->log(LOG_TYPE_HARDWARE, 'Player '.$action.'s '.$amount.' '.$hardware_name, $player->getSectorID());

$player->update();
$ship->updateHardware();

$container = create_container('skeleton.php', 'shop_hardware.php');
transfer('LocationID');
forward($container);
