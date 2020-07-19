<?php declare(strict_types=1);
$amount = Request::getInt('amount');
$action = Request::get('action');

// no negative amounts are allowed
if ($amount <= 0) {
	create_error('You must actually enter an amount > 0!');
}

if ($action == 'Deposit') {
	if ($player->getCredits() < $amount) {
		create_error('You don\'t have that much money on your ship!');
	}
	$amount = $player->increaseBank($amount); // handles overflow
	$player->decreaseCredits($amount);
} else {
	if ($player->getBank() < $amount) {
		create_error('You don\'t have that much money in your account!');
	}
	$amount = $player->increaseCredits($amount); // handles overflow
	$player->decreaseBank($amount);
}

// log action
$account->log(LOG_TYPE_BANK, $action . ' ' . $amount . ' credits for personal account', $player->getSectorID());

$player->update();
forward(create_container('skeleton.php', 'bank_personal.php'));
