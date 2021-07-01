<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();
$player = $session->getPlayer();

// ********************************
// *
// * V a l i d a t e d ?
// *
// ********************************

// is account validated?
if (!$account->isValidated()) {
	create_error('You are not validated so you cannot use banks.');
}

if (!isset($var['alliance_id'])) {
	$session->updateVar('alliance_id', $player->getAllianceID());
}

$alliance = SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic', 'Bank');

Menu::bank();

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT * FROM alliance_treaties WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND (alliance_id_1 = ' . $db->escapeNumber($player->getAllianceID()) . ' OR alliance_id_2 = ' . $db->escapeNumber($player->getAllianceID()) . ')
			AND aa_access = 1 AND official = \'TRUE\'');
$alliedAllianceBanks = array();
foreach ($dbResult->records() as $dbRecord) {
	$alliedAllianceBanks[$dbRecord->getInt('alliance_id_2')] = SmrAlliance::getAlliance($dbRecord->getInt('alliance_id_2'), $alliance->getGameID());
	$alliedAllianceBanks[$dbRecord->getInt('alliance_id_1')] = SmrAlliance::getAlliance($dbRecord->getInt('alliance_id_1'), $alliance->getGameID());
}
$template->assign('AlliedAllianceBanks', $alliedAllianceBanks);

$dbResult = $db->read('SELECT transaction, sum(amount) as total FROM alliance_bank_transactions
			WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND payee_id = ' . $db->escapeNumber($player->getAccountID()) . '
			GROUP BY transaction');
$playerTrans = array('Deposit' => 0, 'Payment' => 0);
foreach ($dbResult->records() as $dbRecord) {
	$playerTrans[$dbRecord->getField('transaction')] = $dbRecord->getInt('total');
}

if ($alliance->getAllianceID() == $player->getAllianceID()) {
	$role_id = $player->getAllianceRole($alliance->getAllianceID());
	$query = 'role_id = ' . $db->escapeNumber($role_id);
} else {
	$query = 'role = ' . $db->escapeString($player->getAlliance()->getAllianceName());
}

$dbResult = $db->read('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND ' . $query);
$dbRecord = $dbResult->record();
$template->assign('CanExempt', $dbRecord->getBoolean('exempt_with'));
$withdrawalPerDay = $dbRecord->getInt('with_per_day');

if ($dbRecord->getBoolean('positive_balance')) {
	$template->assign('PositiveWithdrawal', $withdrawalPerDay + $playerTrans['Deposit'] - $playerTrans['Payment']);
} elseif ($withdrawalPerDay == ALLIANCE_BANK_UNLIMITED) {
	$template->assign('UnlimitedWithdrawal', true);
} else {
	$dbResult = $db->read('SELECT sum(amount) as total FROM alliance_bank_transactions WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . '
				AND payee_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND transaction = \'Payment\' AND exempt = 0 AND time > ' . $db->escapeNumber(Smr\Epoch::time() - 86400));
	if ($dbResult->hasRecord()) {
		$totalWithdrawn = $dbResult->record()->getInt('total');
	}
	$template->assign('WithdrawalPerDay', $withdrawalPerDay);
	$template->assign('RemainingWithdrawal', $withdrawalPerDay - $totalWithdrawn);
	$template->assign('TotalWithdrawn', $totalWithdrawn);
}

$maxValue = $session->getRequestVarInt('maxValue', 0);
$minValue = $session->getRequestVarInt('minValue', 0);

if ($maxValue <= 0) {
	$dbResult = $db->read('SELECT MAX(transaction_id) FROM alliance_bank_transactions
				WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
				AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()));
	if ($dbResult->hasRecord()) {
		$maxValue = $dbResult->record()->getInt('MAX(transaction_id)');
	}
}

if ($minValue <= 0 || $minValue > $maxValue) {
	$minValue = max(1, $maxValue - 5);
}

$query = 'SELECT time, transaction_id, transaction, amount, exempt, reason, payee_id
	FROM alliance_bank_transactions
	WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
	AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID());


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
	$bankTransactions = array();
	foreach ($dbResult->records() as $dbRecord) {
		$bankTransactions[$dbRecord->getInt('transaction_id')] = array(
			'Time' => $dbRecord->getInt('time'),
			'Player' => SmrPlayer::getPlayer($dbRecord->getInt('payee_id'), $player->getGameID()),
			'Reason' => $dbRecord->getField('reason'),
			'TransactionType' => $dbRecord->getField('transaction'),
			'Withdrawal' => $dbRecord->getField('transaction') == 'Payment' ? $dbRecord->getInt('amount') : '',
			'Deposit' => $dbRecord->getField('transaction') == 'Deposit' ? $dbRecord->getInt('amount') : '',
			'Exempt' => $dbRecord->getInt('exempt') == 1
		);
	}
	$template->assign('BankTransactions', $bankTransactions);

	$template->assign('MinValue', $minValue);
	$template->assign('MaxValue', $maxValue);
	$container = Page::create('skeleton.php', 'bank_alliance.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$template->assign('FilterTransactionsFormHREF', $container->href());

	$container = Page::create('bank_alliance_exempt_processing.php');
	$container['minVal'] = $minValue;
	$container['maxVal'] = $maxValue;
	$template->assign('ExemptTransactionsFormHREF', $container->href());

	$template->assign('Alliance', $alliance);
}

$container = Page::create('skeleton.php', 'bank_report.php');
$container['alliance_id'] = $alliance->getAllianceID();
$template->assign('BankReportHREF', $container->href());

$container = Page::create('bank_alliance_processing.php');
$container['alliance_id'] = $alliance->getAllianceID();
$template->assign('BankTransactionFormHREF', $container->href());
