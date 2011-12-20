<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
$planet =& $player->getSectorPlanet();
$action = $_REQUEST['action'];
$amount = $_REQUEST['amount'];
if ($action == 'Deposit' || $action == 'Withdraw') {
	if (!is_numeric($amount))
		create_error('Numbers only please!');

	// only whole numbers allowed
	$amount = floor($amount);

	// no negative amounts are allowed
	if ($amount <= 0)
		create_error('You must actually enter an amount > 0!');

	if ($action == 'Deposit') {
		if ($player->getCredits() < $amount)
			create_error('You don\'t own that much money!');

		$player->decreaseCredits($amount);
		$planet->increaseCredits($amount);
		$account->log(LOG_TYPE_BANK, 'Player puts '.$amount.' credits on planet', $player->getSectorID());
	}
	elseif ($action == 'Withdraw') {
		if ($planet->getCredits() < $amount)
			create_error('There are not enough credits in the planetary account!');

		$player->increaseCredits($amount);
		$planet->decreaseCredits($amount);
		$account->log(LOG_TYPE_BANK, 'Player takes '.$amount.' credits from planet', $player->getSectorID());
	}
}
elseif ($action == 'Bond It!') {
	if(!$planet->isClaimed())
		create_error('Cannot bond on an unclaimed planet.');
	// add it to bond
	$planet->increaseBonds($planet->getCredits());

	// set free cash to 0
	$planet->setCredits(0);

	// initialize time
	$planet->setMaturity(TIME + round(BOND_TIME / Globals::getGameSpeed($player->getGameID())));

	// save to db
	$account->log(LOG_TYPE_BANK, 'Player bonds '.$planet->getCredits().' credits at planet.', $player->getSectorID());
}

forward(create_container('skeleton.php', 'planet_financial.php'));

?>