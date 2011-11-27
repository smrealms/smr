<?php
$action = $_REQUEST['action'];
if ($action == 'Buy')
{
	$cost = $var['cost'];
	$power_level = $var['power_level'];
	$cant_buy = $var['cant_buy'];

	if ($cant_buy == 'Yes')
    	create_error('We are at WAR!!! Do you really think I\'m gonna sell you that weapon?');
	
	// do we have enough cash?
	if ($player->getCredits() < $cost)
		create_error('You do not have enough cash to purchase this weapon!');

	// can we load such a weapon (power_level)
	if ($ship->check_power_level($power_level) == 0)
		create_error('Your ship doesn\'t have enough power to support that weapon!');

    if ($ship->getOpenWeaponSlots() < 1)
		create_error('You can\'t buy any more weapon!');

	if ($var['buyer_restriction'] == 2 && $player->getAlignment() > -100)
		create_error('You can\'t buy evil weapons!');

	if ($var['buyer_restriction'] == 1 && $player->getAlignment() < 100)
		create_error('You can\'t buy good weapons!');

	// take the money from the user
	$player->decreaseCredits($cost);

	// add the weapon to the users ship
	$weapon =& $ship->addWeapon($var['weapon_id']);
	$account->log(LOG_TYPE_HARDWARE, 'Player Buys a '.$weapon->getName(), $player->getSectorID());
}
elseif ($action == 'Sell')
{
	// mhh we wonna sell our weapon
	// give the money to the user
	$player->increaseCredits($var['cash_back']);

	// take weapon
	$ship->removeWeapon($var['order_id']);

	$account->log(LOG_TYPE_HARDWARE, 'Player Sells a '.SmrWeapon::getWeapon($player->getGameID(),$var['weapon_type_id'])->getName(), $player->getSectorID());
}
$container = create_container('skeleton.php', 'shop_weapon.php');
transfer('LocationID');
forward($container);

?>