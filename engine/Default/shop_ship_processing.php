<?php declare(strict_types=1);

$shipID = $var['ship_id'];
$newShip = AbstractSmrShip::getBaseShip(Globals::getGameType($player->getGameID()), $shipID);
$cost = $ship->getCostToUpgrade($shipID);

// trade master 33
// trip maker 30
//(22,25,23,75,43,55,61,24,21,38,67,33,49)
// Top racials minus ATM + top UG/FED are restricted 

if ($newShip['AlignRestriction'] == BUYER_RESTRICTION_EVIL && $player->getAlignment() > ALIGNMENT_EVIL) {
	create_error('You can\'t buy smuggler ships!');
}

if ($newShip['AlignRestriction'] == BUYER_RESTRICTION_GOOD && $player->getAlignment() < ALIGNMENT_GOOD) {
	create_error('You can\'t buy federal ships!');
}

if ($newShip['RaceID'] != RACE_NEUTRAL && $player->getRaceID() != $newShip['RaceID']) {
	create_error('You can\'t buy other race\'s ships!');
}

/*if ($player->getAccountID() == 101)
	create_error('Cheaters do NOT get ships!');*/

// do we have enough cash?
if ($player->getCredits() < $cost) {
	create_error('You do not have enough cash to purchase this ship!');
}

// adapt turns
$player->setTurns(round($player->getTurns() * $newShip['Speed'] / $ship->getSpeed())); //Don't times by game speed as getSpeed doesn't include it meaning ratio will be the same but less work.

// take the money from the user
if ($cost > 0) {
	$player->decreaseCredits($cost);
} else {
	$player->increaseCredits(-$cost);
}

// assign the new ship
$ship->decloak();
$ship->disableIllusion();
$ship->setShipTypeID($shipID);


// update again
$ship->removeUnderAttack();
$ship->update();
$player->update();

$account->log(LOG_TYPE_HARDWARE, 'Buys a ' . $ship->getName() . ' for ' . $cost . ' credits', $player->getSectorID());

$container = create_container('skeleton.php', 'current_sector.php');
transfer('LocationID');
forward($container);
