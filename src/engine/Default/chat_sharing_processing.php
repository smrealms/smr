<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

function error_on_page(string $message): never {
	$message = '<span class="bold red">ERROR:</span> ' . $message;
	Page::create('chat_sharing.php', ['message' => $message])->go();
}

// Process adding a "share to" account
if (Smr\Request::has('add')) {
	$addPlayerID = Smr\Request::getInt('add_player_id');
	if (empty($addPlayerID)) {
		error_on_page('You must specify a Player ID to share with!');
	}

	if ($addPlayerID == $player->getPlayerID()) {
		error_on_page('You do not need to share with yourself!');
	}

	try {
		$accountId = SmrPlayer::getPlayerByPlayerID($addPlayerID, $player->getGameID())->getAccountID();
	} catch (Smr\Exceptions\PlayerNotFound $e) {
		error_on_page($e->getMessage());
	}

	$var = $session->getCurrentVar();
	if (in_array($accountId, $var['share_to_ids'])) {
		error_on_page('You are already sharing with this player!');
	}

	$gameId = Smr\Request::has('all_games') ? '0' : $player->getGameID();
	$db->insert('account_shares_info', [
		'to_account_id' => $db->escapeNumber($accountId),
		'from_account_id' => $db->escapeNumber($player->getAccountID()),
		'game_id' => $db->escapeNumber($gameId),
	]);
}

// Process removing a "share to" account
if (Smr\Request::has('remove_share_to')) {
	$db->write('DELETE FROM account_shares_info WHERE to_account_id=' . $db->escapeNumber(Smr\Request::getInt('remove_share_to')) . ' AND from_account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND game_id=' . $db->escapeNumber(Smr\Request::getInt('game_id')));
}

// Process removing a "share from" account
if (Smr\Request::has('remove_share_from')) {
	$db->write('DELETE FROM account_shares_info WHERE to_account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND from_account_id=' . $db->escapeNumber(Smr\Request::getInt('remove_share_from')) . ' AND game_id=' . $db->escapeNumber(Smr\Request::getInt('game_id')));
}

Page::create('chat_sharing.php')->go();
