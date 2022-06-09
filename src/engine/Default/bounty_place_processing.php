<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();
$player = $session->getPlayer();

$amount = Smr\Request::getInt('amount');
$smrCredits = Smr\Request::getInt('smrcredits');

if ($player->getCredits() < $amount) {
	create_error('You dont have that much money.');
}

if ($account->getSmrCredits() < $smrCredits) {
	create_error('You dont have that many SMR credits.');
}

if ($amount <= 0 && $smrCredits <= 0) {
	create_error('You must enter an amount greater than 0!');
}

$container = Page::create('bounty_place_confirm.php');
$container['amount'] = $amount;
$container['SmrCredits'] = $smrCredits;
$container['player_id'] = Smr\Request::getInt('player_id');
$container->addVar('LocationID');

$container->go();
