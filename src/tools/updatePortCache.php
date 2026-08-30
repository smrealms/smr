<?php declare(strict_types=1);

use Smr\Database;
use Smr\Port;

require_once('../bootstrap.php');

$db = Database::getInstance();
$dbResult = $db->select('player_visited_port', [], ['player_id', 'sector_id', 'game_id']);
foreach ($dbResult->records() as $dbRecord) {
	Port::getCachedPort(
		gameID: $dbRecord->getInt('game_id'),
		sectorID: $dbRecord->getInt('sector_id'),
		playerID: $dbRecord->getInt('player_id'),
	)->addCachePort($dbRecord->getInt('player_id'));
	Port::clearCache();
}
