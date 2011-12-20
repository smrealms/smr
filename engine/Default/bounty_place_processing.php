<?php
$sector =& $player->getSector();

$container = array();
$container['url'] = 'skeleton.php';
transfer('LocationID');
if ($sector->hasHQ()) {
	$container['body'] = 'government.php';
	$type = 'HQ';
}
else {
	$container['body'] = 'underground.php';
	$type = 'UG';
}
$action = $_REQUEST['action'];
// if we don't have a yes we leave immediatly
if ($action != 'Yes') {
	forward($container);
}

// get values from container
$amount = $var['amount'];
$smrCredits = $var['SmrCredits'];
$account_id = $var['account_id'];
if ((!is_numeric($amount) && !is_numeric($smrCredits)) || ($amount == 0 && $smrCredits == 0)) {
	create_error('You must enter an amount!');
}
if ($amount < 0) {
	create_error('You must enter a positive amount!');
}
if ($smrCredits < 0) {
	create_error('You must enter a positive SMR credits amount!');
}
// take the bounty from the cash
$player->decreaseCredits($amount);
$account->decreaseSmrCredits($smrCredits);

$player->increaseHOF($smrCredits,array('Bounties','Placed','SMR Credits'), HOF_PUBLIC);
$player->increaseHOF($amount,array('Bounties','Placed','Money'), HOF_PUBLIC);
$player->increaseHOF(1,array('Bounties','Placed','Number'), HOF_PUBLIC);

$placed =& SmrPlayer::getPlayer($account_id, $player->getGameID());
$placed->increaseCurrentBountyAmount($type,$amount);
$placed->increaseCurrentBountySmrCredits($type,$smrCredits);
$placed->increaseHOF($smrCredits,array('Bounties','Received','SMR Credits'), HOF_PUBLIC);
$placed->increaseHOF($amount,array('Bounties','Received','Money'), HOF_PUBLIC);
$placed->increaseHOF(1,array('Bounties','Received','Number'), HOF_PUBLIC);

//Update for top bounties list
$player->update();
$account->update();
$placed->update();
forward($container);

?>
