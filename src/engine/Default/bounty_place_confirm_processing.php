<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();
$player = $session->getPlayer();

if (!$player->getSector()->hasLocation($var['LocationID'])) {
	create_error('That location does not exist in this sector');
}

$location = SmrLocation::getLocation($var['LocationID']);
$container = Page::create('skeleton.php');
$container->addVar('LocationID');

list($type, $body) = match(true) {
	$location->isHQ() => ['HQ', 'government.php'],
	$location->isUG() => ['UG', 'underground.php'],
};
$container['body'] = $body;

// if we don't have a yes we leave immediatly
if (Request::get('action') != 'Yes') {
	$container->go();
}

// get values from container (validated in bounty_place_processing.php)
$amount = $var['amount'];
$smrCredits = $var['SmrCredits'];
$account_id = $var['account_id'];

// take the bounty from the cash
$player->decreaseCredits($amount);
$account->decreaseSmrCredits($smrCredits);

$player->increaseHOF($smrCredits, array('Bounties', 'Placed', 'SMR Credits'), HOF_PUBLIC);
$player->increaseHOF($amount, array('Bounties', 'Placed', 'Money'), HOF_PUBLIC);
$player->increaseHOF(1, array('Bounties', 'Placed', 'Number'), HOF_PUBLIC);

$placed = SmrPlayer::getPlayer($account_id, $player->getGameID());
$placed->increaseCurrentBountyAmount($type, $amount);
$placed->increaseCurrentBountySmrCredits($type, $smrCredits);
$placed->increaseHOF($smrCredits, array('Bounties', 'Received', 'SMR Credits'), HOF_PUBLIC);
$placed->increaseHOF($amount, array('Bounties', 'Received', 'Money'), HOF_PUBLIC);
$placed->increaseHOF(1, array('Bounties', 'Received', 'Number'), HOF_PUBLIC);

//Update for top bounties list
$player->update();
$account->update();
$placed->update();
$container->go();
