<?php declare(strict_types=1);

//view anon acct activity.
$template->assign('PageTopic', 'View Anonymous Account Info');

$container = create_container('skeleton.php', 'anon_acc_view.php');
$template->assign('AnonViewHREF', SmrSession::getNewHREF($container));

$anonID = SmrSession::getRequestVar('anon_account');
$gameID = SmrSession::getRequestVar('view_game_id');
$haveIDs = (!empty($anonID) && !empty($gameID));

//do we have an acct?
if ($haveIDs) {
	$db->query('SELECT *
				FROM anon_bank_transactions
				JOIN player USING(account_id, game_id)
				WHERE anon_id = '.$db->escapeNumber($anonID) . '
					AND game_id = '.$db->escapeNumber($gameID) . '
				ORDER BY transaction_id');
	$rows = [];
	while ($db->nextRecord()) {
		$rows[] = [
			'player_name' => $db->getField('player_name'),
			'transaction' => $db->getField('transaction'),
			'amount' => $db->getField('amount'),
		];
	}
	$template->assign('Rows', $rows);
	$template->assign('AnonID', $anonID);
	$template->assign('ViewGameID', $gameID);


	if (!$rows) {
		$message = '<p><span class="red">Anon account #' . $anonID . ' in Game ' . $gameID . ' does NOT exist!</span></p>';
		$template->assign('Message', $message);
	}
}
