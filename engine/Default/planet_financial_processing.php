<?php declare(strict_types=1);
if (!$player->isLandedOnPlanet()) {
	create_error('You are not on a planet!');
}
$planet = $player->getSectorPlanet();
$action = Request::get('action');

// Player has requested a planetary fund transaction
if ($action == 'Deposit' || $action == 'Withdraw') {
	$amount = Request::getInt('amount');
	if ($amount <= 0) {
		create_error('You must actually enter an amount > 0!');
	}

	if ($action == 'Deposit') {
		if ($player->getCredits() < $amount) {
			create_error('You don\'t own that much money!');
		}

		$amount = $planet->increaseCredits($amount); // handles overflow
		$player->decreaseCredits($amount);
	} elseif ($action == 'Withdraw') {
		if ($planet->getCredits() < $amount) {
			create_error('There are not enough credits in the planetary account!');
		}

		$amount = $player->increaseCredits($amount); // handles overflow
		$planet->decreaseCredits($amount);
	}
	$account->log(LOG_TYPE_BANK, $action . ' ' . $amount . ' credits at planet', $player->getSectorID());
}

// Player has confirmed the request to bond
elseif ($action == 'Confirm') {
	$planet->bond();

	// save to db
	$account->log(LOG_TYPE_BANK, 'Player bonds ' . $planet->getBonds() . ' credits at planet.', $player->getSectorID());
}

forward(create_container('skeleton.php', 'planet_financial.php'));
