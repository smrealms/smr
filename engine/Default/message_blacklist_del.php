<?php

$container = create_container('skeleton.php', 'message_blacklist.php');

if (empty($_REQUEST['entry_ids'])) {
	$container['msg'] = '<span class="red bold">ERROR: </span>No entries selected for deletion.';
	forward($container);
}

$db->query('DELETE FROM message_blacklist WHERE account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND entry_id IN (' . $db->escapeArray($_REQUEST['entry_ids']) . ')');
forward($container);
