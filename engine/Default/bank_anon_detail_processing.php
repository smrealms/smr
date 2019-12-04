<?php declare(strict_types=1);
$action = $_REQUEST['action'];
if (!isset($action) || ($action != 'Deposit' && $action != 'Withdraw')) {
	create_error('You must choose if you want to deposit or withdraw.');
}
$amount = $_REQUEST['amount'];
// only whole numbers allowed
$amount = floor($amount);
$account_num = $var['account_num'];
// no negative amounts are allowed
if ($amount <= 0) {
	create_error('You must actually enter an amount > 0!');
}

if ($action == 'Deposit') {
	if ($player->getCredits() < $amount) {
		create_error('You don\'t own that much money!');
	}

	$player->decreaseCredits($amount);
	$db->query('SELECT transaction_id FROM anon_bank_transactions WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND anon_id = ' . $db->escapeNumber($account_num) . ' ORDER BY transaction_id DESC LIMIT 1');
	if ($db->nextRecord()) {
		$trans_id = $db->getInt('transaction_id') + 1;
	}
	else {
		$trans_id = 1;
	}
	$db->query('INSERT INTO anon_bank_transactions (account_id, game_id, anon_id, transaction_id, transaction, amount, time) ' .
							'VALUES (' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($account_num) . ', ' . $db->escapeNumber($trans_id) . ', \'Deposit\', ' . $db->escapeNumber($amount) . ', ' . $db->escapeNumber(TIME) . ')');
	$db->query('UPDATE anon_bank SET amount = amount + ' . $db->escapeNumber($amount) . ' WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND anon_id = ' . $db->escapeNumber($account_num));
	$player->update();

	// log action
	$account->log(LOG_TYPE_BANK, 'Deposits ' . $amount . ' credits in anonymous account #' . $account_num, $player->getSectorID());
}
else {
	$db->query('SELECT * FROM anon_bank WHERE anon_id = ' . $db->escapeNumber($account_num) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$db->nextRecord();
	if ($db->getInt('amount') < $amount)
		create_error('You don\'t have that much money on your account!');
	$db->query('SELECT transaction_id FROM anon_bank_transactions WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND anon_id = ' . $db->escapeNumber($account_num) . ' ORDER BY transaction_id DESC LIMIT 1');
	if ($db->nextRecord()) {
		$trans_id = $db->getInt('transaction_id') + 1;
	}
	else {
		$trans_id = 1;
	}
	$db->query('INSERT INTO anon_bank_transactions (account_id, game_id, anon_id, transaction_id, transaction, amount, time)
				VALUES (' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($account_num) . ', ' . $db->escapeNumber($trans_id) . ', \'Payment\', ' . $db->escapeNumber($amount) . ', ' . $db->escapeNumber(TIME) . ')');
	$db->query('UPDATE anon_bank SET amount = amount - ' . $db->escapeNumber($amount) . ' WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND anon_id = ' . $db->escapeNumber($account_num));
	$player->increaseCredits($amount);
	$player->update();

	// log action
	$account->log(LOG_TYPE_BANK, 'Takes ' . $amount . ' credits from anonymous account #' . $account_num, $player->getSectorID());
}

$container = create_container('skeleton.php', 'bank_anon_detail.php');
$container['account_num'] = $account_num;
$container['allowed'] = 'yes';
forward($container);
