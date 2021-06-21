<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();

//view anon acct activity.
$template->assign('PageTopic', 'View Anonymous Account Info');

$container = Page::create('skeleton.php', 'anon_acc_view_select.php');
$template->assign('BackHREF', $container->href());

$anonID = $session->getRequestVarInt('anon_account');
$gameID = $session->getRequestVarInt('view_game_id');

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT *
			FROM anon_bank_transactions
			JOIN player USING(account_id, game_id)
			WHERE anon_id = '.$db->escapeNumber($anonID) . '
				AND game_id = '.$db->escapeNumber($gameID) . '
			ORDER BY transaction_id');
$rows = [];
foreach ($dbResult->records() as $dbRecord) {
	$rows[] = [
		'player_name' => $dbRecord->getField('player_name'),
		'transaction' => $dbRecord->getField('transaction'),
		'amount' => $dbRecord->getInt('amount'),
	];
}
if (!$rows) {
	$message = '<p><span class="red">Anon account #' . $anonID . ' in Game ' . $gameID . ' does NOT exist!</span></p>';
	$container['message'] = $message;
	$container->go();
}
$template->assign('Rows', $rows);
$template->assign('AnonID', $anonID);
$template->assign('ViewGameID', $gameID);
