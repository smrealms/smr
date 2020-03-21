<?php declare(strict_types=1);

$account_num = SmrSession::getRequestVarInt('account_num');
SmrSession::getRequestVarInt('maxValue', 0);
SmrSession::getRequestVarInt('minValue', 0);

$db->query('SELECT *
			FROM anon_bank
			WHERE anon_id=' . $db->escapeNumber($account_num) . '
			AND game_id=' . $db->escapeNumber($player->getGameID()) . ' LIMIT 1');

// if they didn't come from the creation screen we need to check if the pw is correct
if ($db->nextRecord()) {
	if (!isset($var['allowed']) || $var['allowed'] != 'yes') {
		SmrSession::getRequestVar('password');
		if ($db->getField('password') != $var['password']) {
			create_error('Invalid password!');
		}
	}
} else {
	create_error('This anonymous account does not exist!');
}

$balance = $db->getInt('amount');
$template->assign('Balance', $balance);

if ($var['maxValue'] > 0) {
	$maxValue = $var['maxValue'];
} else {
	$db->query('SELECT MAX(transaction_id) FROM anon_bank_transactions
				WHERE game_id=' . $db->escapeNumber($player->getGameID()) . '
				AND anon_id=' . $db->escapeNumber($account_num)
				);
	if ($db->nextRecord()) {
		$maxValue = $db->getInt('MAX(transaction_id)');
	} else {
		$maxValue = 5;
	}
	$minValue = max(1, $maxValue - 5);
}

if ($var['minValue'] <= $maxValue && $var['minValue'] > 0) {
	$minValue = $var['minValue'];
}

$query = 'SELECT time, player_name, player_id, alignment, transaction_id, transaction, amount
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

$db->query($query);

// only if we have at least one result
if ($db->getNumRows() > 0) {
	$template->assign('MinValue', $minValue);
	$template->assign('MaxValue', $maxValue);
	$container = create_container('skeleton.php', 'bank_anon_detail.php');
	$container['allowed'] = 'yes';
	$container['account_num'] = $account_num;
	$template->assign('ShowHREF', SmrSession::getNewHREF($container));

	$container = create_container('skeleton.php', 'trader_search_result.php');

	$transactions = [];
	while ($db->nextRecord()) {
		$container['player_id'] = $db->getInt('player_id');
		$link = create_link($container, get_colored_text($db->getInt('alignment'), $db->getField('player_name')));
		$transaction = $db->getField('transaction');
		$amount = number_format($db->getInt('amount'));
		$transactions[$db->getInt('transaction_id')] = [
			'date' => date(DATE_FULL_SHORT_SPLIT, $db->getInt('time')),
			'payment' => $transaction == 'Payment' ? $amount : '',
			'deposit' => $transaction == 'Deposit' ? $amount : '',
			'link' => $link,
		];
	}
	$template->assign('Transactions', $transactions);
}

$container = create_container('bank_anon_detail_processing.php');
$container['account_num'] = $account_num;
$template->assign('TransactionHREF', SmrSession::getNewHREF($container));

$template->assign('PageTopic', 'Anonymous Account #' . $account_num);
Menu::bank();
