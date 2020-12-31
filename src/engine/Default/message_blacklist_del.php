<?php declare(strict_types=1);

$container = create_container('skeleton.php', 'message_blacklist.php');

$entry_ids = Request::getIntArray('entry_ids', []);
if (empty($entry_ids)) {
	$container['msg'] = '<span class="red bold">ERROR: </span>No entries selected for deletion.';
	forward($container);
}

$db->query('DELETE FROM message_blacklist WHERE account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND entry_id IN (' . $db->escapeArray($entry_ids) . ')');
forward($container);
