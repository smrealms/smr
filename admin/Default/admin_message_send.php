<?php declare(strict_types=1);

$template->assign('PageTopic', 'Send Admin Message');

$gameID = SmrSession::getRequestVar('SendGameID');
// check if we know the game yet
if (empty($gameID)) {
	$template->assign('AdminMessageChooseGameFormHref', SmrSession::getNewHREF(create_container('skeleton.php', 'admin_message_send.php')));

	// Get a list of all games that have not yet ended
	$activeGames = array();
	$db->query('SELECT game_id FROM game WHERE end_time > ' . $db->escapeNumber(TIME) . ' ORDER BY end_time DESC');
	while ($db->nextRecord()) {
		$activeGames[] = SmrGame::getGame($db->getInt('game_id'));
	}
	$template->assign('ActiveGames', $activeGames);
} else {
	$container = create_container('admin_message_send_processing.php');
	$container['SendGameID'] = $gameID;
	$template->assign('AdminMessageSendFormHref', SmrSession::getNewHREF($container));
	$template->assign('MessageGameID', $gameID);
	$template->assign('ExpireTime', $var['expire'] ?? 0.5);

	if ($gameID != 20000) {
		$gamePlayers = array();
		$db->query('SELECT account_id,player_id,player_name FROM player WHERE game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY player_name');
		while ($db->nextRecord()) {
			$gamePlayers[] = array('AccountID' => $db->getField('account_id'), 'PlayerID' => $db->getField('player_id'), 'Name' => $db->getField('player_name'));
		}
		$template->assign('GamePlayers', $gamePlayers);
	}
	if (isset($var['preview'])) {
		$template->assign('Preview', $var['preview']);
	}
}
