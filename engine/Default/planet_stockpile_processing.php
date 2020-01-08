<?php declare(strict_types=1);
if (!$player->isLandedOnPlanet()) {
	create_error('You are not on a planet!');
}
$amount = $_REQUEST['amount'];
if (!is_numeric($amount)) {
	create_error('Numbers only please');
}
$amount = floor($amount);

if ($amount <= 0) {
	create_error('You must actually enter an amount > 0!');
}

// get a planet from the sector where the player is in
$planet = $player->getSectorPlanet();
$action = $_REQUEST['action'];
// transfer to ship
if ($action == 'Ship') {

	// do we want transfer more than we have?
	if ($amount > $planet->getStockpile($var['good_id'])) {
		create_error('You can\'t take more than on planet!');
	}

	// do we want to transfer more than we can carry?
	if ($amount > $ship->getEmptyHolds()) {
		create_error('You can\'t take more than you can carry!');
	}

	// now transfer
	$planet->decreaseStockpile($var['good_id'], $amount);
	$ship->increaseCargo($var['good_id'], $amount);
	$account->log(LOG_TYPE_PLANETS, 'Player takes ' . $amount . ' ' . Globals::getGoodName($var['good_id']) . ' from planet.', $player->getSectorID());

// transfer to planet
} elseif ($action == 'Planet') {
	// do we want transfer more than we have?
	if ($amount > $ship->getCargo($var['good_id'])) {
		create_error('You can\'t store more than you carry!');
	}

	// do we want to transfer more than the planet can hold?
	if ($amount > $planet->getRemainingStockpile($var['good_id'])) {
		create_error('This planet cannot store more than ' . SmrPlanet::MAX_STOCKPILE . ' of each good!');
	}

	// now transfer
	$planet->increaseStockpile($var['good_id'], $amount);
	$ship->decreaseCargo($var['good_id'], $amount);
}

// update both
$planet->update();
$ship->updateCargo();

forward(create_container('skeleton.php', 'planet_stockpile.php'));
