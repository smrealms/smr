<?php declare(strict_types=1);

// Get initial storage size
$initialBytes = $db->getDbBytes();

$endedGameIDs = [];
$db->query('SELECT game_id FROM game WHERE end_time < ' . $db->escapeNumber(TIME));
while ($db->nextRecord()) {
	$endedGameIDs[] = $db->getInt('game_id');
}
rsort($endedGameIDs);

$tablesToClean = [
	'combat_logs',
	'message',
	'player_visited_port',
	'player_visited_sector',
	'port_info_cache',
	'player_has_unread_messages',
	'player_read_thread',
	'route_cache',
	'weighted_random',
];

if ($var['action'] == 'delete') {
	$action = 'DELETE';
} else {
	$action = 'SELECT 1';
}

$rowsDeleted = [];
foreach ($tablesToClean as $table) {
	$db->query($action . ' FROM ' . $table . ' WHERE game_id IN (' . $db->escapeArray($endedGameIDs) . ')');
	$rowsDeleted[$table] = $db->getChangedRows();
}

$db->query($action . ' FROM npc_logs');
$rowsDeleted['npc_logs'] = $db->getChangedRows();

// Get difference in storage size
$diffBytes = $initialBytes - $db->getDbBytes();

$container = create_container('skeleton.php', 'db_cleanup.php');
$container['results'] = $rowsDeleted;
$container['diffBytes'] = $diffBytes;
$container['endedGames'] = $endedGameIDs;
transfer('action');
forward($container);
