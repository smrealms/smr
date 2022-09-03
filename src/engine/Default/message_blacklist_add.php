<?php declare(strict_types=1);

use Smr\Database;
use Smr\Exceptions\PlayerNotFound;
use Smr\Request;

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$container = Page::create('message_blacklist.php');

if (isset($var['account_id'])) {
	$blacklisted = SmrPlayer::getPlayer($var['account_id'], $player->getGameID());
} else {
	try {
		$blacklisted = SmrPlayer::getPlayerByPlayerName(Request::get('PlayerName'), $player->getGameID());
	} catch (PlayerNotFound) {
		$container['msg'] = '<span class="red bold">ERROR: </span>Player does not exist.';
		$container->go();
	}
}

$db = Database::getInstance();
$dbResult = $db->read('SELECT 1 FROM message_blacklist WHERE ' . $player->getSQL() . ' AND blacklisted_id=' . $db->escapeNumber($blacklisted->getAccountID()) . ' LIMIT 1');

if ($dbResult->hasRecord()) {
	$container['msg'] = '<span class="red bold">ERROR: </span>Player is already blacklisted.';
	$container->go();
}

$db->insert('message_blacklist', [
	'game_id' => $db->escapeNumber($player->getGameID()),
	'account_id' => $db->escapeNumber($player->getAccountID()),
	'blacklisted_id' => $db->escapeNumber($blacklisted->getAccountID()),
]);

$container['msg'] = $blacklisted->getDisplayName() . ' has been added to your blacklist.';
$container->go();
