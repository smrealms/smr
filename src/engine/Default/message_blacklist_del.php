<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$container = Page::create('message_blacklist.php');

$entry_ids = Smr\Request::getIntArray('entry_ids', []);
if (empty($entry_ids)) {
	$container['msg'] = '<span class="red bold">ERROR: </span>No entries selected for deletion.';
	$container->go();
}

$db = Smr\Database::getInstance();
$db->write('DELETE FROM message_blacklist WHERE account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND entry_id IN (' . $db->escapeArray($entry_ids) . ')');
$container->go();
