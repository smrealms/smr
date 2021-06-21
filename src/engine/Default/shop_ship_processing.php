<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();
$ship = $player->getShip();

$shipTypeID = $var['ship_type_id'];
$newShipType = SmrShipType::get($shipTypeID);
$cost = $ship->getCostToUpgrade($shipTypeID);

if ($newShipType->getRestriction() == BUYER_RESTRICTION_EVIL && $player->getAlignment() > ALIGNMENT_EVIL) {
	create_error('You can\'t buy smuggler ships!');
}

if ($newShipType->getRestriction() == BUYER_RESTRICTION_GOOD && $player->getAlignment() < ALIGNMENT_GOOD) {
	create_error('You can\'t buy federal ships!');
}

if ($newShipType->getRaceID() != RACE_NEUTRAL && $player->getRaceID() != $newShipType->getRaceID()) {
	create_error('You can\'t buy other race\'s ships!');
}

// do we have enough cash?
if ($player->getCredits() < $cost) {
	create_error('You do not have enough cash to purchase this ship!');
}

// take the money from the user
if ($cost > 0) {
	$player->decreaseCredits($cost);
} else {
	$player->increaseCredits(-$cost);
}

// assign the new ship
$ship->decloak();
$ship->disableIllusion();
$ship->setTypeID($shipTypeID);

$player->log(LOG_TYPE_HARDWARE, 'Buys a ' . $newShipType->getName() . ' for ' . $cost . ' credits');

$container = Page::create('skeleton.php', 'current_sector.php');
$container->addVar('LocationID');
$container->go();
