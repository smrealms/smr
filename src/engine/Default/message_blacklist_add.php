<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$container = Page::create('skeleton.php', 'message_blacklist.php');

if (isset($var['account_id'])) {
	$blacklisted = SmrPlayer::getPlayer($var['account_id'], $player->getGameID());
} else {
	try {
		$blacklisted = SmrPlayer::getPlayerByPlayerName(Smr\Request::get('PlayerName'), $player->getGameID());
	} catch (PlayerNotFoundException $e) {
		$container['msg'] = '<span class="red bold">ERROR: </span>Player does not exist.';
		$container->go();
	}
}

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT 1 FROM message_blacklist WHERE ' . $player->getSQL() . ' AND blacklisted_id=' . $db->escapeNumber($blacklisted->getAccountID()) . ' LIMIT 1');

if ($dbResult->hasRecord()) {
	$container['msg'] = '<span class="red bold">ERROR: </span>Player is already blacklisted.';
	$container->go();
}

$db->write('INSERT INTO message_blacklist (game_id,account_id,blacklisted_id) VALUES (' . $db->escapeNumber($player->getGameID()) . ',' . $db->escapeNumber($player->getAccountID()) . ',' . $db->escapeNumber($blacklisted->getAccountID()) . ')');

$container['msg'] = $blacklisted->getDisplayName() . ' has been added to your blacklist.';
$container->go();
