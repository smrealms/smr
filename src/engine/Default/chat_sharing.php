<?php declare(strict_types=1);
$template->assign('PageTopic', 'Chat Sharing Settings');

if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
}

$shareFrom = array();
$db->query('SELECT * FROM account_shares_info WHERE to_account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND (game_id=0 OR game_id=' . $db->escapeNumber($player->getGameID()) . ')');
while ($db->nextRecord()) {
	$fromAccountId = $db->getInt('from_account_id');
	$gameId = $db->getInt('game_id');
	try {
		$otherPlayer = SmrPlayer::getPlayer($fromAccountId, $player->getGameID());
	} catch (PlayerNotFoundException $e) {
		// Player has not joined this game yet
		$otherPlayer = null;
	}
	$shareFrom[$fromAccountId] = array(
		'Player ID'   => $otherPlayer == null ? '-' : $otherPlayer->getPlayerID(),
		'Player Name' => $otherPlayer == null ?
		                 '<b>Account</b>: ' . SmrAccount::getAccount($fromAccountId)->getHofDisplayName() :
		                 $otherPlayer->getDisplayName(),
		'All Games'   => $gameId == 0 ? '<span class="green">YES</span>' : '<span class="red">NO</span>',
		'Game ID'     => $gameId,
	);
}

$shareTo = array();
$db->query('SELECT * FROM account_shares_info WHERE from_account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND (game_id=0 OR game_id=' . $db->escapeNumber($player->getGameID()) . ')');
while ($db->nextRecord()) {
	$gameId = $db->getInt('game_id');
	$toAccountId = $db->getInt('to_account_id');
	try {
		$otherPlayer = SmrPlayer::getPlayer($toAccountId, $player->getGameID());
	} catch (PlayerNotFoundException $e) {
		// Player has not joined this game yet
		$otherPlayer = null;
	}
	$shareTo[$toAccountId] = array(
		'Player ID'   => $otherPlayer == null ? '-' : $otherPlayer->getPlayerID(),
		'Player Name' => $otherPlayer == null ?
		                 '<b>Account</b>: ' . SmrAccount::getAccount($toAccountId)->getHofDisplayName() :
		                 $otherPlayer->getDisplayName(),
		'All Games'   => $gameId == 0 ? '<span class="green">YES</span>' : '<span class="red">NO</span>',
		'Game ID'     => $gameId,
	);
}

$template->assign('ShareFrom', $shareFrom);
$template->assign('ShareTo', $shareTo);

$template->assign('ProcessingHREF', SmrSession::getNewHREF(create_container('chat_sharing_processing.php', '', array('share_to_ids' => array_keys($shareTo)))));
