<?php
$action = $_REQUEST['action'];
if (!isset($action) || ($action != "Deposit" && $action != "Withdraw"))
    create_error("You must choose if you want to deposit or withdraw");
$amount = $_REQUEST['amount'];
// only whole numbers allowed
$amount = floor($amount);
$account_num = $var["account_num"];
// no negative amounts are allowed
if ($amount <= 0)
    create_error("You must actually enter an amount > 0!");

if ($action == "Deposit") {

    if ($player->credits < $amount)
        create_error("You don't own that much money!");

    $player->credits -= $amount;
    $db->query("SELECT * FROM anon_bank_transactions WHERE game_id = $player->game_id AND anon_id = $account_num ORDER BY transaction_id DESC LIMIT 1");
    if ($db->next_record())
        $trans_id = $db->f("transaction_id") + 1;
    else
        $trans_id = 1;
	$time = time();
    $db->query("INSERT INTO anon_bank_transactions (account_id, game_id, anon_id, transaction_id, transaction, amount, time) " .
                            "VALUES ($player->account_id, $player->game_id, $account_num, $trans_id, 'Deposit', $amount, $time)");
    $db->query("UPDATE anon_bank SET amount = amount + $amount WHERE game_id = $player->game_id AND anon_id = $account_num");
    $db->query("SELECT * FROM anon_bank WHERE game_id = $player->game_id AND anon_id = $account_num");
    $db->next_record();
    $total = $db->f("amount");
    //too much money?
	if ($total > 4294967295) {
		
		$overflow = $total - 4294967295;
		$db->query("UPDATE anon_bank SET amount = amount - $overflow WHERE game_id = $player->game_id AND anon_id = $account_num");
		$player->credits += $overflow;
		
	}
    $player->update();

	// log action
	$account->log(4, "Deposits $amount credits in anonymous account #$account_num", $player->sector_id);

} else {

    $db->query("SELECT * FROM anon_bank WHERE anon_id = $account_num AND game_id = $player->game_id");
    $db->next_record();
    if ($db->f("amount") < $amount)
        create_error("You don't have that much money on your account!");
    $db->query("SELECT * FROM anon_bank_transactions WHERE game_id = $player->game_id AND anon_id = $account_num ORDER BY transaction_id DESC LIMIT 1");
    if ($db->next_record())
        $trans_id = $db->f("transaction_id") + 1;
    else
        $trans_id = 1;
	$time = time();
    $db->query("INSERT INTO anon_bank_transactions (account_id, game_id, anon_id, transaction_id, transaction, amount, time) " .
                            "VALUES ($player->account_id, $player->game_id, $account_num, $trans_id, 'Payment', $amount, $time)");
    $db->query("UPDATE anon_bank SET amount = amount - $amount WHERE game_id = $player->game_id AND anon_id = $account_num");
    $player->credits += $amount;
    $db->query("SELECT * FROM anon_bank WHERE game_id = $player->game_id AND anon_id = $account_num");
    $db->next_record();
    $total = $db->f("amount");
    //too much money?
	if ($player->credits > 4294967295) {
		
		$overflow = $player->credits - 4294967295;
		$db->query("UPDATE anon_bank SET amount = amount + $overflow WHERE game_id = $player->game_id AND anon_id = $account_num");
		$player->credits -= $overflow;
		
	}
    $player->update();

	// log action
	$account->log(4, "Takes $amount credits from anonymous account #$account_num", $player->sector_id);

}

$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "bank_anon.php";
$container["account_num"] = $account_num;
$container["allowed"] = "yes";
transfer($password);
forward($container);

?>