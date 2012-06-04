<?
$amount = $_REQUEST['amount'];
if (!is_numeric($amount))
	create_error('Numbers only please');
    
// only whole numbers allowed
$amount = floor($amount);

$hardware_id	= $var['hardware_id'];
$hardware_name	= $var['hardware_name'];
$cost			= $var['cost'];

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
$ship->update_hardware();

$ship->removeUnderAttack();

//HoF
if ($hardware_id == 4) $player->increaseHOF($amount,array('forces','bought','combat_drones'));
if ($hardware_id == 5) $player->increaseHOF($amount,array('forces','bought','scout_drones'));
if ($hardware_id == 6) $player->increaseHOF($amount,array('forces','bought','mines'));

$account->log(10, 'Player Buys '.$amount.' '.$hardware_name, $player->getSectorID());

forward(create_container('skeleton.php', 'shop_hardware.php'));

?>