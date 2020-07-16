<?php declare(strict_types=1);
$amount = Request::getInt('amount');
$action = Request::get('action');

// no negative amounts are allowed
if ($amount <= 0) {
	create_error('You must actually enter an amount > 0!');
}

if ($action == 'Deposit') {
	if ($player->getCredits() < $amount) {
		create_error('You don\'t own that much money!');
	}

	$player->decreaseCredits($amount);
	$player->increaseBank($amount);
	//too much money?
//	if ($player->getBank() > MAX_MONEY) {
//		
//		$overflow = $player->getBank() - MAX_MONEY;
//		$player->getCredits() += $overflow;
//		$player->getBank() -= $overflow;
//		
//	}
	$player->update();

	// log action
	$account->log(LOG_TYPE_BANK, 'Deposits ' . $amount . ' credits in personal account', $player->getSectorID());

} else {

	if ($player->getBank() < $amount) {
		create_error('You don\'t have that much money on your account!');
	}

	$player->decreaseBank($amount);
	$player->increaseCredits($amount);
	$player->update();

	// log action
	$account->log(LOG_TYPE_BANK, 'Takes ' . $amount . ' credits from personal account', $player->getSectorID());
}

forward(create_container('skeleton.php', 'bank_personal.php'));
