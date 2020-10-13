<?php declare(strict_types=1);

$container = create_container('skeleton.php', 'message_blacklist.php');

$entry_ids = Request::getIntArray('entry_ids', []);
if (empty($entry_ids)) {
	$container['msg'] = '<span class="red bold">ERROR: </span>No entries selected for deletion.';
	forward($container);
}

// TODO: does this need game_id?
$db->query('DELETE FROM message_blacklist WHERE player_id=' . $db->escapeNumber($player->getPlayerID()) . ' AND entry_id IN (' . $db->escapeArray($entry_ids) . ')');
forward($container);
