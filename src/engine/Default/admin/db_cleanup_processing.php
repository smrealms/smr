<?php declare(strict_types=1);

$var = Smr\Session::getInstance()->getCurrentVar();

// Get initial storage size
$db = Smr\Database::getInstance();
$initialBytes = $db->getDbBytes();

$endedGameIDs = [];
$dbResult = $db->read('SELECT game_id FROM game WHERE end_time < ' . $db->escapeNumber(Smr\Epoch::time()));
foreach ($dbResult->records() as $dbRecord) {
	$endedGameIDs[] = $dbRecord->getInt('game_id');
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
	$method = 'write';
} else {
	$action = 'SELECT 1';
	$method = 'read';
}

$rowsDeleted = [];
foreach ($tablesToClean as $table) {
	$db->$method($action . ' FROM ' . $table . ' WHERE game_id IN (' . $db->escapeArray($endedGameIDs) . ')');
	$rowsDeleted[$table] = $db->getChangedRows();
}

$db->$method($action . ' FROM npc_logs');
$rowsDeleted['npc_logs'] = $db->getChangedRows();

// Get difference in storage size
$diffBytes = $initialBytes - $db->getDbBytes();

$container = Page::create('admin/db_cleanup.php');
$container['results'] = $rowsDeleted;
$container['diffBytes'] = $diffBytes;
$container['endedGames'] = $endedGameIDs;
$container->addVar('action');
$container->go();
