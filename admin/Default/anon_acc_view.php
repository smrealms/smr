<?php declare(strict_types=1);

//view anon acct activity.
$template->assign('PageTopic', 'View Anonymous Account Info');

$container = create_container('skeleton.php', 'anon_acc_view_select.php');
$template->assign('BackHREF', SmrSession::getNewHREF($container));

$anonID = SmrSession::getRequestVarInt('anon_account');
$gameID = SmrSession::getRequestVarInt('view_game_id');

$db->query('SELECT *
			FROM anon_bank_transactions
			JOIN player USING(player_id, game_id)
			WHERE anon_id = '.$db->escapeNumber($anonID) . '
				AND game_id = '.$db->escapeNumber($gameID) . '
			ORDER BY transaction_id');
$rows = [];
while ($db->nextRecord()) {
	$rows[] = [
		'player_name' => $db->getField('player_name'),
		'transaction' => $db->getField('transaction'),
		'amount' => $db->getInt('amount'),
	];
}
if (!$rows) {
	$message = '<p><span class="red">Anon account #' . $anonID . ' in Game ' . $gameID . ' does NOT exist!</span></p>';
	$container['message'] = $message;
	forward($container);
}
$template->assign('Rows', $rows);
$template->assign('AnonID', $anonID);
$template->assign('ViewGameID', $gameID);
