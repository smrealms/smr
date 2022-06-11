<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();
$player = $session->getPlayer();

$account_num = $session->getRequestVarInt('account_num');
$session->getRequestVarInt('maxValue', 0);
$session->getRequestVarInt('minValue', 0);

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT *
			FROM anon_bank
			WHERE anon_id=' . $db->escapeNumber($account_num) . '
			AND game_id=' . $db->escapeNumber($player->getGameID()));

if (!$dbResult->hasRecord()) {
	create_error('This anonymous account does not exist!');
}
$dbRecord = $dbResult->record();

// if they didn't come from the creation screen we need to check if the pw is correct
if (!isset($var['allowed']) || $var['allowed'] != 'yes') {
	$session->getRequestVar('password');
	if ($dbRecord->getString('password') != $var['password']) {
		create_error('Invalid password!');
	}
}

$balance = $dbRecord->getInt('amount');
$template->assign('Balance', $balance);

if ($var['maxValue'] > 0) {
	$maxValue = $var['maxValue'];
} else {
	$dbResult = $db->read('SELECT IFNULL(MAX(transaction_id), 5) as max_transaction_id FROM anon_bank_transactions
				WHERE game_id=' . $db->escapeNumber($player->getGameID()) . '
				AND anon_id=' . $db->escapeNumber($account_num));
	$maxValue = $dbResult->record()->getInt('max_transaction_id');
}

if ($var['minValue'] <= $maxValue && $var['minValue'] > 0) {
	$minValue = $var['minValue'];
} else {
	$minValue = max(1, $maxValue - 5);
}

$query = 'SELECT *
			FROM player
			JOIN anon_bank_transactions USING (game_id, account_id)
			WHERE player.game_id=' . $db->escapeNumber($player->getGameID()) . '
			AND anon_bank_transactions.anon_id=' . $db->escapeNumber($account_num);

if ($maxValue > 0 && $minValue > 0) {
	$query .= ' AND transaction_id>=' . $db->escapeNumber($minValue) . '
				AND transaction_id<=' . $db->escapeNumber($maxValue) . '
				ORDER BY time LIMIT ' . (1 + $maxValue - $minValue);
} else {
	$query .= ' ORDER BY time LIMIT 10';
}

$dbResult = $db->read($query);

// only if we have at least one result
if ($dbResult->hasRecord()) {
	$template->assign('MinValue', $minValue);
	$template->assign('MaxValue', $maxValue);
	$container = Page::create('bank_anon_detail.php');
	$container['allowed'] = 'yes';
	$container['account_num'] = $account_num;
	$template->assign('ShowHREF', $container->href());

	$transactions = [];
	foreach ($dbResult->records() as $dbRecord) {
		$transactionPlayer = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), false, $dbRecord);
		$transaction = $dbRecord->getString('transaction');
		$amount = number_format($dbRecord->getInt('amount'));
		$transactions[$dbRecord->getInt('transaction_id')] = [
			'date' => date($account->getDateTimeFormatSplit(), $dbRecord->getInt('time')),
			'payment' => $transaction == 'Payment' ? $amount : '',
			'deposit' => $transaction == 'Deposit' ? $amount : '',
			'link' => $transactionPlayer->getLinkedDisplayName(),
		];
	}
	$template->assign('Transactions', $transactions);
}

$container = Page::create('bank_anon_detail_processing.php');
$container['account_num'] = $account_num;
$template->assign('TransactionHREF', $container->href());

$template->assign('PageTopic', 'Anonymous Account #' . $account_num);
Menu::bank();
