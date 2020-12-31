<?php declare(strict_types=1);

$template->assign('PageTopic', 'Send Admin Message');

$gameID = SmrSession::getRequestVarInt('SendGameID');
$container = create_container('admin_message_send_processing.php');
$container['SendGameID'] = $gameID;
$template->assign('AdminMessageSendFormHref', SmrSession::getNewHREF($container));
$template->assign('MessageGameID', $gameID);
$template->assign('ExpireTime', $var['expire'] ?? 0.5);

if ($gameID != 20000) {
	$game = SmrGame::getGame($gameID);
	$gamePlayers = [['AccountID' => 0, 'Name' => 'All Players (' . $game->getName() . ')']];
	$db->query('SELECT account_id,player_id,player_name FROM player WHERE game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY player_name');
	while ($db->nextRecord()) {
		$gamePlayers[] = [
			'AccountID' => $db->getInt('account_id'),
			'Name' => $db->getField('player_name') . ' (' . $db->getInt('player_id') . ')',
		];
	}
	$template->assign('GamePlayers', $gamePlayers);
	$template->assign('SelectedAccountID', $var['account_id'] ?? 0);
}
if (isset($var['preview'])) {
	$template->assign('Preview', $var['preview']);
}

$container = create_container('skeleton.php', 'admin_message_send_select.php');
$template->assign('BackHREF', SmrSession::getNewHREF($container));
