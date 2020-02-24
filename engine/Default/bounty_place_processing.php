<?php declare(strict_types=1);

$amount = Request::getInt('amount');
$smrCredits = Request::getInt('smrcredits');

if ($player->getCredits() < $amount) {
	create_error('You dont have that much money.');
}

if ($account->getSmrCredits() < $smrCredits) {
	create_error('You dont have that many SMR credits.');
}

if ($amount <= 0 && $smrCredits <= 0) {
	create_error('You must enter an amount greater than 0!');
}

$container = create_container('skeleton.php', 'bounty_place_confirm.php');
$container['amount'] = $amount;
$container['SmrCredits'] = $smrCredits;
$container['player_id'] = Request::getInt('player_id');
transfer('LocationID');

forward($container);
