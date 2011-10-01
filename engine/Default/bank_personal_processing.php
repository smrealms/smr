<?php
$amount = $_REQUEST['amount'];
if (!is_numeric($amount))
	create_error('Numbers only!');
$action = $_REQUEST['action'];
if (!isset($action) || ($action != 'Deposit' && $action != 'Withdraw'))
	create_error('You must choose if you want to deposit or withdraw.');

// only whole numbers allowed
$amount = floor($amount);

// no negative amounts are allowed
if ($amount <= 0)
	create_error('You must actually enter an amount > 0!');

if ($action == 'Deposit') {

	if ($player->getCredits() < $amount)
		create_error('You don\'t own that much money!');

	$player->decreaseCredits($amount);
	$player->increaseBank($amount);
	//too much money?
//	if ($player->getBank() > 4294967295) {
//		
//		$overflow = $player->getBank() - 4294967295;
//		$player->getCredits() += $overflow;
//		$player->getBank() -= $overflow;
//		
//	}
	$player->update();

	// log action
	$account->log(4, 'Deposits '.$amount.' credits in personal account', $player->getSectorID());

} else {

	if ($player->getBank() < $amount)
		create_error('You don\'t have that much money on your account!');

	$player->decreaseBank($amount);
	$player->increaseCredits($amount);
	$player->update();

	// log action
	$account->log(4, 'Takes '.$amount.' credits from personal account', $player->getSectorID());

}

forward(create_container('skeleton.php', 'bank_personal.php'));

?>