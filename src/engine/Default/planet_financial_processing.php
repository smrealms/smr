<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if (!$player->isLandedOnPlanet()) {
	create_error('You are not on a planet!');
}
$planet = $player->getSectorPlanet();
$action = Smr\Request::get('action');

if ($action == 'Deposit' || $action == 'Withdraw') {
	// Player has requested a planetary fund transaction
	$amount = Smr\Request::getInt('amount');
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
	$player->log(LOG_TYPE_BANK, $action . ' ' . $amount . ' credits at planet');
} elseif ($action == 'Confirm') {
	// Player has confirmed the request to bond
	$planet->bond();

	// save to db
	$player->log(LOG_TYPE_BANK, 'Player bonds ' . $planet->getBonds() . ' credits at planet.');
}

Page::create('planet_financial.php')->go();
