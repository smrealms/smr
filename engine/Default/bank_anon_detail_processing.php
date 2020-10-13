<?php declare(strict_types=1);
$action = Request::get('action');
if (!in_array($action, ['Deposit', 'Payment'])) {
	throw new Exception('Invalid action submitted: ' . $action);
}

$amount = Request::getInt('amount');
$account_num = $var['account_num'];
// no negative amounts are allowed
if ($amount <= 0) {
	create_error('You must actually enter an amount > 0!');
}

// Get the next transaction ID for this anon bank
$db->query('SELECT transaction_id FROM anon_bank_transactions WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND anon_id = ' . $db->escapeNumber($account_num) . ' ORDER BY transaction_id DESC LIMIT 1');
if ($db->nextRecord()) {
	$trans_id = $db->getInt('transaction_id') + 1;
} else {
	$trans_id = 1;
}

// Update the credit amounts for the player and the bank
if ($action == 'Deposit') {
	if ($player->getCredits() < $amount) {
		create_error('You don\'t own that much money!');
	}

	// Does not handle overflow!
	$player->decreaseCredits($amount);
	$db->query('UPDATE anon_bank SET amount = amount + ' . $db->escapeNumber($amount) . ' WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND anon_id = ' . $db->escapeNumber($account_num));
} else {
	$db->query('SELECT * FROM anon_bank WHERE anon_id = ' . $db->escapeNumber($account_num) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$db->requireRecord();
	if ($db->getInt('amount') < $amount) {
		create_error('You don\'t have that much money on your account!');
	}

	$amount = $player->increaseCredits($amount); // handles overflow
	$db->query('UPDATE anon_bank SET amount = amount - ' . $db->escapeNumber($amount) . ' WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND anon_id = ' . $db->escapeNumber($account_num));
}

$player->update();

// Log the bank transaction
$db->query('INSERT INTO anon_bank_transactions (player_id, game_id, anon_id, transaction_id, transaction, amount, time)
			VALUES (' . $db->escapeNumber($player->getPlayerID()) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($account_num) . ', ' . $db->escapeNumber($trans_id) . ', ' . $db->escapeString($action) . ', ' . $db->escapeNumber($amount) . ', ' . $db->escapeNumber(TIME) . ')');

// Log the player action
$player->log(LOG_TYPE_BANK, $action . ' of ' . $amount . ' credits in anonymous account #' . $account_num);

$container = create_container('skeleton.php', 'bank_anon_detail.php');
$container['account_num'] = $account_num;
$container['allowed'] = 'yes';
forward($container);
