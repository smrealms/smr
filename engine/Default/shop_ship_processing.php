<?

$shipID = $var['ship_id'];
$newShip =& AbstractSmrShip::getBaseShip(Globals::getGameType($player->getGameID()),$shipID);
$cost		= $newShip['Cost'] - $ship->getCost() / 2;

// trade master 33
// trip maker 30
//(22,25,23,75,43,55,61,24,21,38,67,33,49)
// Top racials minus ATM + top UG/FED are restricted 

if ($newShip['AlignRestriction'] == 2 && $player->getAlignment() > -100)
	create_error('You can\'t buy smuggler ships!');

if ($newShip['AlignRestriction'] == 1 && $player->getAlignment() < 100)
	create_error('You can\'t buy federal ships!');

if ($newShip['RaceID'] != 1 && $player->getRaceID() != $newShip['RaceID'])
	create_error('You can\'t buy other race\'s ships!');

/*if ($player->getAccountID() == 101)
	create_error('Cheaters do NOT get ships!');*/

// do we have enough cash?
if ($player->getCredits() < $cost)
	create_error('You do not have enough cash to purchase this ship!');

// adapt turns
$player->setTurns(round($player->getTurns() * $newShip['Speed'] / $ship->getSpeed())); //Don't times by game speed as getSpeed doesn't include it meaning ratio will be the same but less work.

// take the money from the user
if($cost>0)
	$player->decreaseCredits($cost);
else
	$player->increaseCredits(-$cost);

// assign the new ship
$ship->decloak();
$ship->disableIllusion();
$ship->setShipTypeID($shipID);


// update again
$ship->removeUnderAttack();
$ship->update();
$player->update();

$account->log(10, 'Buys a '.$ship->getName().' for '.$cost.' credits', $player->getSectorID());

forward(create_container('skeleton.php', 'current_sector.php'));

?>
