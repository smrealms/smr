<?php declare(strict_types=1);

function error_on_page($message) {
	$message = '<span class="bold red">ERROR:</span> ' . $message;
	forward(create_container('skeleton.php', 'chat_sharing.php', array('message' => $message)));
}

// Process adding a "share to" account
if (Request::has('add')) {
	$addPlayerID = Request::getInt('add_player_id');
	if (empty($addPlayerID)) {
		error_on_page('You must specify a Player ID to share with!');
	}

	if ($addPlayerID == $player->getPlayerID()) {
		error_on_page('You do not need to share with yourself!');
	}

	try {
		$accountId = SmrPlayer::getPlayer($addPlayerID, $player->getGameID())->getAccountID();
	} catch (PlayerNotFoundException $e) {
		error_on_page($e->getMessage());
	}

	if (in_array($accountId, $var['share_to_ids'])) {
		error_on_page('You are already sharing with this player!');
	}

	$gameId = Request::has('all_games') ? '0' : $player->getGameID();
	$db->query('INSERT INTO account_shares_info (to_account_id, from_account_id, game_id) VALUES (' . $db->escapeNumber($accountId) . ',' . $db->escapeNumber($player->getAccountID()) . ',' . $db->escapeNumber($gameId) . ')');
}

// Process removing a "share to" account
if (Request::has('remove_share_to')) {
	$db->query('DELETE FROM account_shares_info WHERE to_account_id=' . $db->escapeNumber(Request::getInt('remove_share_to')) . ' AND from_account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND game_id=' . $db->escapeNumber(Request::getInt('game_id')));
}

// Process removing a "share from" account
if (Request::has('remove_share_from')) {
	$db->query('DELETE FROM account_shares_info WHERE to_account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND from_account_id=' . $db->escapeNumber(Request::getInt('remove_share_from')) . ' AND game_id=' . $db->escapeNumber(Request::getInt('game_id')));
}

forward(create_container('skeleton.php', 'chat_sharing.php'));
