<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();
$alliance = $player->getAlliance();

$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

//get rid of already approved entries
$db = Smr\Database::getInstance();
$db->write('UPDATE alliance_bank_transactions SET request_exempt = 0 WHERE exempt = 1');


$dbResult = $db->read('SELECT * FROM alliance_bank_transactions WHERE request_exempt = 1 ' .
			'AND alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND exempt = 0');
$transactions = [];
if ($dbResult->hasRecord()) {
	$container = Page::create('bank_alliance_exempt_processing.php');
	$template->assign('ExemptHREF', $container->href());

	$players = $alliance->getMembers();
	foreach ($dbResult->records() as $dbRecord) {
		$transactions[] = [
			'type' => $dbRecord->getField('transaction') == 'Payment' ? 'Withdraw' : 'Deposit',
			'player' => $players[$dbRecord->getInt('payee_id')]->getDisplayName(),
			'reason' => $dbRecord->getField('reason'),
			'amount' => number_format($dbRecord->getInt('amount')),
			'transactionID' => $dbRecord->getInt('transaction_id'),
		];
	}
}
$template->assign('Transactions', $transactions);
