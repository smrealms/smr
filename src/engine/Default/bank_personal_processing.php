<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$amount = Smr\Request::getInt('amount');
$action = Smr\Request::get('action');

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
$player->log(LOG_TYPE_BANK, $action . ' ' . $amount . ' credits for personal account');

Page::create('skeleton.php', 'bank_personal.php')->go();
