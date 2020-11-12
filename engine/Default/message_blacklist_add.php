<?php declare(strict_types=1);

$container = create_container('skeleton.php', 'message_blacklist.php');

if (isset($var['player_id'])) {
	$blacklisted = SmrPlayer::getPlayer($var['player_id'], $player->getGameID());
} else {
	try {
		$blacklisted = SmrPlayer::getPlayerByPlayerName(Request::get('PlayerName'), $player->getGameID());
	} catch (PlayerNotFoundException $e) {
		$container['msg'] = '<span class="red bold">ERROR: </span>Player does not exist.';
		forward($container);
	}
}

$db->query('SELECT account_id FROM message_blacklist WHERE ' . $player->getSQL() . ' AND blacklisted_id=' . $db->escapeNumber($blacklisted->getAccountID()) . ' LIMIT 1');

if ($db->nextRecord()) {
	$container['msg'] = '<span class="red bold">ERROR: </span>Player is already blacklisted.';
	forward($container);
}

$db->query('INSERT INTO message_blacklist (game_id, player_id, blacklisted_player_id) VALUES (' . $db->escapeNumber($player->getGameID()) . ',' . $db->escapeNumber($player->getPlayerID()) . ',' . $db->escapeNumber($blacklisted->getPlayerID()) . ')');

$container['msg'] = $blacklisted->getDisplayName() . ' has been added to your blacklist.';
forward($container);
