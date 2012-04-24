<?php
$action = $_REQUEST['action'];
if ($action == 'Buy') {
	$weapon =& SmrWeapon::getWeapon($var['weapon_type_id']);
	if ($weapon->getRaceID() != RACE_NEUTRAL && $player->getRelation($weapon->getRaceID()) < 300) {
		create_error('We are at WAR!!! Do you really think I\'m gonna sell you that weapon?');
	}

	// do we have enough cash?
	if ($player->getCredits() < $weapon->getCost()) {
		create_error('You do not have enough cash to purchase this weapon!');
	}

	// can we load such a weapon (power_level)
	if (!$ship->checkPowerLevel($var['power_level'])) {
		create_error('Your ship doesn\'t have enough power to support that weapon!');
	}

	if ($ship->getOpenWeaponSlots() < 1) {
		create_error('You can\'t buy any more weapon!');
	}

	if ($weapon->getBuyerRestriction() == 2 && $player->getAlignment() > -100) {
		create_error('You can\'t buy evil weapons!');
	} else if ($weapon->getBuyerRestriction() == 1 && $player->getAlignment() < 100) {
		create_error('You can\'t buy good weapons!');
	}

	// take the money from the user
	$player->decreaseCredits($weapon->getCost());

	// add the weapon to the users ship
	$weapon =& $ship->addWeapon($weapon->getWeaponTypeID());
	$account->log(LOG_TYPE_HARDWARE, 'Player Buys a '.$weapon->getName(), $player->getSectorID());
}
elseif ($action == 'Sell') {
	$weapon =& SmrWeapon::getWeapon($var['weapon_type_id']);
	// mhh we wanna sell our weapon
	// give the money to the user
	$player->increaseCredits(floor($weapon->getCost() * WEAPON_REFUND_PERCENT));

	// take weapon
	$ship->removeWeapon($var['order_id']);

	$account->log(LOG_TYPE_HARDWARE, 'Player Sells a '.$weapon->getName(), $player->getSectorID());
}
$container = create_container('skeleton.php', 'shop_weapon.php');
transfer('LocationID');
forward($container);

?>