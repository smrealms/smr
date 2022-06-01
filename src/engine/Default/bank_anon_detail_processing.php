<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$action = Smr\Request::get('action');
if (!in_array($action, ['Deposit', 'Payment'])) {
	throw new Exception('Invalid action submitted: ' . $action);
}

$amount = Smr\Request::getInt('amount');
$account_num = $var['account_num'];
// no negative amounts are allowed
if ($amount <= 0) {
	create_error('You must actually enter an amount > 0!');
}

// Get the next transaction ID for this anon bank
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT IFNULL(MAX(transaction_id), 0) AS max_id FROM anon_bank_transactions WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND anon_id = ' . $db->escapeNumber($account_num));
$trans_id = $dbResult->record()->getInt('max_id') + 1;

// Update the credit amounts for the player and the bank
if ($action == 'Deposit') {
	if ($player->getCredits() < $amount) {
		create_error('You don\'t own that much money!');
	}

	// Does not handle overflow!
	$player->decreaseCredits($amount);
	$db->write('UPDATE anon_bank SET amount = amount + ' . $db->escapeNumber($amount) . ' WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND anon_id = ' . $db->escapeNumber($account_num));
} else {
	$dbResult = $db->read('SELECT * FROM anon_bank WHERE anon_id = ' . $db->escapeNumber($account_num) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	if ($dbResult->record()->getInt('amount') < $amount) {
		create_error('You don\'t have that much money on your account!');
	}

	$amount = $player->increaseCredits($amount); // handles overflow
	$db->write('UPDATE anon_bank SET amount = amount - ' . $db->escapeNumber($amount) . ' WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND anon_id = ' . $db->escapeNumber($account_num));
}

$player->update();

// Log the bank transaction
$db->insert('anon_bank_transactions', [
	'account_id' => $db->escapeNumber($player->getAccountID()),
	'game_id' => $db->escapeNumber($player->getGameID()),
	'anon_id' => $db->escapeNumber($account_num),
	'transaction_id' => $db->escapeNumber($trans_id),
	'transaction' => $db->escapeString($action),
	'amount' => $db->escapeNumber($amount),
	'time' => $db->escapeNumber(Smr\Epoch::time()),
]);

// Log the player action
$player->log(LOG_TYPE_BANK, $action . ' of ' . $amount . ' credits in anonymous account #' . $account_num);

$container = Page::create('skeleton.php', 'bank_anon_detail.php');
$container['account_num'] = $account_num;
$container['allowed'] = 'yes';
$container->go();
