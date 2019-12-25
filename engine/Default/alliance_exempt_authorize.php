<?php declare(strict_types=1);
$alliance = $player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID(), $alliance->getLeaderID());

//get rid of already approved entries
$db->query('UPDATE alliance_bank_transactions SET request_exempt = 0 WHERE exempt = 1');


$db->query('SELECT * FROM alliance_bank_transactions WHERE request_exempt = 1 ' . 
			'AND alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND exempt = 0');
$transactions = [];
if ($db->getNumRows()) {
	$container = create_container('bank_alliance_exempt_processing.php');
	$template->assign('ExemptHREF', SmrSession::getNewHREF($container));

	$players = $alliance->getMembers();
	while ($db->nextRecord()) {
		$transactions[] = [
			'type' => $db->getField('transaction') == 'Payment' ? 'Withdraw' : 'Deposit',
			'player' => $players[$db->getInt('payee_id')]->getPlayerName(),
			'reason' => $db->getField('reason'),
			'amount' => number_format($db->getInt('amount')),
			'transactionID' => $db->getInt('transaction_id'),
		];
	}
}
$template->assign('Transactions', $transactions);
