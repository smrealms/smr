<?php declare(strict_types=1);

$template->assign('PageTopic', 'Anonymous Account Access');

$db->query('SELECT account_id FROM account_has_logs GROUP BY account_id');
$log_account_ids = [];
while ($db->nextRecord()) {
	$log_account_ids[] = $db->getInt('account_id');
}

// get all anon bank transactions that are logged in an array
$db->query('SELECT * FROM anon_bank_transactions
            JOIN account USING(account_id)
            WHERE account_id IN ('.$db->escapeArray($log_account_ids) . ')
            ORDER BY game_id DESC, anon_id ASC');
$anon_logs = [];
while ($db->nextRecord()) {
	$transaction = strtolower($db->getField('transaction'));
	$anon_logs[$db->getInt('game_id')][$db->getInt('anon_id')][] = [
		'login' => $db->getField('login'),
		'amount' => number_format($db->getInt('amount')),
		'date' => date(DATE_FULL_SHORT, $db->getField('time')),
		'type' => $transaction,
		'color' => $transaction == 'payment' ? 'tomato' : 'green',
	];
}
$template->assign('AnonLogs', $anon_logs);

$container = create_container('skeleton.php', 'log_console.php');
$template->assign('BackHREF', SmrSession::getNewHREF($container));
