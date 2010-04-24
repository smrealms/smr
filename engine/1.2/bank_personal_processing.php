<?php
$amount = $_REQUEST['amount'];
if (!is_numeric($amount))
	create_error("Numbers only please");
$action = $_REQUEST['action'];
if (!isset($action) || ($action != "Deposit" && $action != "Withdraw"))
	create_error("You must choose if you want to deposit or withdraw");

// only whole numbers allowed
$amount = floor($amount);

// no negative amounts are allowed
if ($amount <= 0)
	create_error("You must actually enter an amount > 0!");

if ($action == "Deposit") {

	if ($player->credits < $amount)
		create_error("You don't own that much money!");

	$player->credits -= $amount;
	$player->bank += $amount;
	//too much money?
	if ($player->bank > 4294967295) {
		
		$overflow = $player->bank - 4294967295;
		$player->credits += $overflow;
		$player->bank -= $overflow;
		
	}
	$player->update();

	// log action
	$account->log(4, "Deposits $amount credits in personal account", $player->sector_id);

} else {

	if ($player->bank < $amount)
		create_error("You don't have that much money on your account!");

	$player->bank -= $amount;
	$player->credits += $amount;
	$player->update();

	// log action
	$account->log(4, "Takes $amount credits from personal account", $player->sector_id);

}

forward(create_container("skeleton.php", "bank_personal.php"));

?>