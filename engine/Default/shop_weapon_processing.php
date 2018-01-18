<?php
if(!$player->getSector()->hasLocation($var['LocationID'])) {
	create_error('That location does not exist in this sector');
}

$weapon =& SmrWeapon::getWeapon($player->getGameID(), $var['WeaponTypeID']);
// Are we buying?
if (!isset($var['OrderID'])) {
	$location =& SmrLocation::getLocation($var['LocationID']);
	if(!$location->isWeaponSold($var['WeaponTypeID'])) {
		create_error('We do not sell that weapon here!');
	}
	
	if ($weapon->getRaceID() != RACE_NEUTRAL && $player->getRelation($weapon->getRaceID()) < RELATIONS_PEACE) {
		create_error('We are at WAR!!! Do you really think I\'m gonna sell you that weapon?');
	}

	// do we have enough cash?
	if ($player->getCredits() < $weapon->getCost()) {
		create_error('You do not have enough cash to purchase this weapon!');
	}

	// can we load such a weapon (power_level)
	if (!$ship->checkPowerLevel($weapon->getPowerLevel())) {
		create_error('Your ship doesn\'t have enough power to support that weapon!');
	}

	if ($ship->getOpenWeaponSlots() < 1) {
		create_error('You can\'t buy any more weapons!');
	}

	if ($weapon->getBuyerRestriction() == BUYER_RESTRICTION_EVIL && $player->getAlignment() > ALIGNMENT_EVIL) {
		create_error('You can\'t buy evil weapons!');
	} else if ($weapon->getBuyerRestriction() == BUYER_RESTRICTION_GOOD && $player->getAlignment() < ALIGNMENT_GOOD) {
		create_error('You can\'t buy good weapons!');
	} else if ($weapon->getBuyerRestriction() == BUYER_RESTRICTION_NEWBIE && !$player->getAccount()->isNewbie()) {
		create_error('You can\'t buy newbie weapons!');
	}

	// take the money from the user
	$player->decreaseCredits($weapon->getCost());

	// add the weapon to the users ship
	$weapon =& $ship->addWeapon($weapon->getWeaponTypeID());
	$account->log(LOG_TYPE_HARDWARE, 'Player Buys a '.$weapon->getName(), $player->getSectorID());
}
else {
	// mhh we wanna sell our weapon
	// give the money to the user
	$player->increaseCredits(floor($weapon->getCost() * WEAPON_REFUND_PERCENT));

	// take weapon
	$ship->removeWeapon($var['OrderID']);

	$account->log(LOG_TYPE_HARDWARE, 'Player Sells a '.$weapon->getName(), $player->getSectorID());
}
$container = create_container('skeleton.php', 'shop_weapon.php');
transfer('LocationID');
forward($container);

?>
