<?php

// ********************************
// *
// * V a l i d a t e d ?
// *
// ********************************

// is account validated?
if (!$account->isValidated())
	create_error('You are not validated so you cannot use banks.');

if (!isset($var['alliance_id']))
	SmrSession::updateVar('alliance_id',$player->getAllianceID());

$alliance =& SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic','Bank');

require_once(get_file_loc('menu.inc'));
create_bank_menu();

$db->query('SELECT * FROM alliance_treaties WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND (alliance_id_1 = ' . $db->escapeNumber($player->getAllianceID()) . ' OR alliance_id_2 = ' . $db->escapeNumber($player->getAllianceID()) . ')
			AND aa_access = 1 AND official = \'TRUE\'');
$alliedAllianceBanks=array();
if($db->getNumRows() > 0)
{
	$alliedAllianceBanks[$player->getAllianceID()] = $player->getAlliance();
	while ($db->nextRecord())
	{
		if ($db->getInt('alliance_id_1') == $player->getAllianceID())
			$alliedAllianceBanks[$db->getInt('alliance_id_2')] = SmrAlliance::getAlliance($db->getInt('alliance_id_2'), $alliance->getGameID());
		else
			$alliedAllianceBanks[$db->getInt('alliance_id_1')] = SmrAlliance::getAlliance($db->getInt('alliance_id_1'), $alliance->getGameID());
	}
}
$template->assignByRef('AlliedAllianceBanks', $alliedAllianceBanks);

$db->query('SELECT transaction, sum(amount) as total FROM alliance_bank_transactions
			WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND payee_id = ' . $db->escapeNumber($player->getAccountID()) . '
			GROUP BY transaction');
while($db->nextRecord())
{
	$playerTrans[$db->getField('transaction')] = $db->getInt('total');
}

if ($alliance->getAllianceID() == $player->getAllianceID())
{
	$role_id = 0;
	$db->query('SELECT role_id FROM player_has_alliance_role WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()));
	if ($db->nextRecord()) $role_id = $db->getInt('role_id');
	$query = 'role_id = ' . $db->escapeNumber($role_id);
}
else
	$query = 'role = ' . $db->escapeString($player->getAllianceName());
$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND ' . $query);
$db->nextRecord();
$template->assign('CanExempt', $db->getBoolean('exempt_with'));
$withdrawalPerDay = $db->getInt('with_per_day');
if($withdrawalPerDay == -2)
{
	$db->assign('UnlimitedWithdrawal', true);
}
if($withdrawalPerDay == -1)
{
	$template->assign('PositiveWithdrawal', $playerTrans['Deposit'] - $playerTrans['Payment']);
}
else
{
	$template->assign('WithdrawalPerDay', $withdrawalPerDay);
	$db->query('SELECT sum(amount) as total FROM alliance_bank_transactions WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . '
				AND payee_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND transaction = \'Payment\' AND exempt = 0 AND time > ' . $db->escapeNumber(TIME - 86400));
	$totalWithdrawn = 0;
	if ($db->nextRecord())
	{
		$totalWithdrawn = $db->getInt('total');
	}
	$template->assign('WithdrawalPerDay', $withdrawalPerDay);
	$template->assign('RemainingWithdrawal', $withdrawalPerDay - $totalWithdrawn);
	$template->assign('TotalWithdrawn', $totalWithdrawn);
}
if (isset($_REQUEST['maxValue']))
	SmrSession::updateVar('maxValue',$_REQUEST['maxValue']);
if (isset($_REQUEST['minValue']))
	SmrSession::updateVar('minValue',$_REQUEST['minValue']);

if (isset($var['maxValue'])
	&& is_numeric($var['maxValue'])
	&& $var['maxValue'] > 0)
{
	$maxValue = $var['maxValue'];
}
else
{
	$db->query('SELECT MAX(transaction_id) FROM alliance_bank_transactions
				WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
				AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()));
	if($db->nextRecord())
	{
		$maxValue = $db->getInt('MAX(transaction_id)');
		$minValue = $maxValue - 5;
		if($minValue < 1)
		{
			$minValue = 1;
		}
	}
	else
	{
		$minValue = 1;
		$maxValue = 5;
	}
}

if(isset($var['minValue'])
	&& $var['minValue'] <= $maxValue
	&& $var['minValue'] > 0
	&& is_numeric($var['maxValue'])) {
	$minValue = $var['minValue'];
}

$query = 'SELECT time, transaction_id, transaction, amount, exempt, reason, payee_id
	FROM alliance_bank_transactions
	WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
	AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID());


if($maxValue > 0 && $minValue > 0)
{
	$query .= ' AND transaction_id>=' . $db->escapeNumber($minValue) . '
				AND transaction_id<=' . $db->escapeNumber($maxValue) . '
				ORDER BY time LIMIT ' . (1 + $maxValue - $minValue);
}
else
{
	$query .= ' ORDER BY time LIMIT 10';
}

$db->query($query);

// only if we have at least one result
if ($db->getNumRows() > 0)
{
	$bankTransactions = array();
	while ($db->nextRecord())
	{
		$bankTransactions[$db->getInt('transaction_id')] = array(
			'Time' => $db->getInt('time'),
			'Player' => SmrPlayer::getPlayer($db->getInt('payee_id'), $player->getGameID()),
			'Reason' => $db->getField('reason'),
			'TransactionType' => $db->getField('transaction'),
			'Withdrawal' => $db->getField('transaction') == 'Payment' ? $db->getInt('amount') : '',
			'Deposit' => $db->getField('transaction') == 'Deposit' ? $db->getInt('amount') : '',
			'Exempt' => $db->getInt('exempt') == 1
		);
	}
	$template->assignByRef('BankTransactions', $bankTransactions);
	
	$template->assign('MinValue', $minValue);
	$template->assign('MaxValue', $maxValue);
	$container = create_container('skeleton.php', 'bank_alliance.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$template->assign('FilterTransactionsFormHREF',SmrSession::get_new_href($container));

	$container=create_container('bank_alliance_exempt_processing.php');
	$container['minVal'] = $minValue;
	$container['maxVal'] = $maxValue;
	$template->assign('ExemptTransactionsFormHREF',SmrSession::get_new_href($container));

	$template->assignByRef('Alliance', $alliance);
}

$container=create_container('skeleton.php', 'bank_report.php');
$container['alliance_id'] = $alliance_id;
$template->assign('BankReportHREF', SmrSession::get_new_href($container));

$container=create_container('bank_alliance_processing.php');
$container['alliance_id'] = $alliance_id;
$template->assign('BankTransactionFormHREF', SmrSession::get_new_href($container));

?>