<?php declare(strict_types=1);

if (!$player->getSector()->hasLocation($var['LocationID'])) {
	create_error('That location does not exist in this sector');
}

$location = SmrLocation::getLocation($var['LocationID']);
$container = create_container('skeleton.php');
transfer('LocationID');
if ($location->isHQ()) {
	$container['body'] = 'government.php';
	$type = 'HQ';
} elseif ($location->isUG()) {
	$container['body'] = 'underground.php';
	$type = 'UG';
} else {
	create_error('The location is not a UG or HQ, how did you get here?');
}
// if we don't have a yes we leave immediatly
if (Request::get('action') != 'Yes') {
	forward($container);
}

// get values from container (validated in bounty_place_processing.php)
$amount = $var['amount'];
$smrCredits = $var['SmrCredits'];

// take the bounty from the cash
$player->decreaseCredits($amount);
$account->decreaseSmrCredits($smrCredits);

$player->increaseHOF($smrCredits, array('Bounties', 'Placed', 'SMR Credits'), HOF_PUBLIC);
$player->increaseHOF($amount, array('Bounties', 'Placed', 'Money'), HOF_PUBLIC);
$player->increaseHOF(1, array('Bounties', 'Placed', 'Number'), HOF_PUBLIC);

$placed = SmrPlayer::getPlayer($var['player_id'], $player->getGameID());
$placed->increaseCurrentBountyAmount($type, $amount);
$placed->increaseCurrentBountySmrCredits($type, $smrCredits);
$placed->increaseHOF($smrCredits, array('Bounties', 'Received', 'SMR Credits'), HOF_PUBLIC);
$placed->increaseHOF($amount, array('Bounties', 'Received', 'Money'), HOF_PUBLIC);
$placed->increaseHOF(1, array('Bounties', 'Received', 'Number'), HOF_PUBLIC);

//Update for top bounties list
$player->update();
$account->update();
$placed->update();
forward($container);
