<?php declare(strict_types=1);
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id', $player->getAllianceID());
}
$alliance_id = $var['alliance_id'];
const WITHDRAW = 0;
const DEPOSIT = 1;

//get all transactions
$db->query('SELECT * FROM alliance_bank_transactions WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
if (!$db->getNumRows()) {
	create_error('Your alliance has no recorded transactions.');
}
$trans = array();
while ($db->nextRecord()) {
	$transType = ($db->getField('transaction') == 'Payment') ? WITHDRAW : DEPOSIT;
	$payeeId = ($db->getField('exempt')) ? 0 : $db->getInt('payee_id');
	// initialize payee if necessary
	if (!isset($trans[$payeeId])) {
		$trans[$payeeId] = array(WITHDRAW => 0, DEPOSIT => 0);
	}
	$trans[$payeeId][$transType] += $db->getInt('amount');
}

//ordering
$playerIDs = array_keys($trans);
foreach ($trans as $accId => $transArray) {
	$totals[$accId] = $transArray[DEPOSIT] - $transArray[WITHDRAW];
}
arsort($totals, SORT_NUMERIC);
$db->query('SELECT * FROM player WHERE account_id IN (' . $db->escapeArray($playerIDs) . ') AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY player_name');
$players[0] = 'Alliance Funds';
while ($db->nextRecord()) {
	$players[$db->getField('account_id')] = $db->getField('player_name');
}

//format it this way so its easy to send to the alliance MB if requested.
$text = '<table class="nobord centered" cellspacing="1">';
$text .= '<tr><th>Player</th><th>Deposits</th><th>Withdrawals</th><th>Total</th></tr>';
$balance = 0;
foreach ($totals as $accId => $total) {
	$balance += $total;
	$text .= '<tr>';
	$text .= '<td><span class="yellow">' . $players[$accId] . '</span></td>';
	$text .= '<td class="right">' . number_format($trans[$accId][DEPOSIT]) . '</td>';
	$text .= '<td class="right">-' . number_format($trans[$accId][WITHDRAW]) . '</td>';
	$text .= '<td class="right"><span class="';
	if ($total < 0) {
		$text .= 'red bold';
	} else {
		$text .= 'bold';
	}
	$text .= '">' . number_format($total) . '</span></td>';
	$text .= '</tr>';
}
$text .= '</table>';
$text = '<div class="center"><br />Ending Balance: ' . number_format($balance) . '</div><br />' . $text;
$template->assign('BankReport', $text);

if (!isset($var['sent_report'])) {
	$container = create_container('bank_report_processing.php');
	$container['alliance_id'] = $alliance_id;
	$container['text'] = $text;
	$template->assign('SendReportHREF', SmrSession::getNewHREF($container));
}

$template->assign('PageTopic', 'Alliance Bank Report');
Menu::bank();
