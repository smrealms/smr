<?php declare(strict_types=1);

use Smr\Database;
use Smr\Port;

require_once('../bootstrap.php');

$db = Database::getInstance();
$dbResult = $db->select('player_visited_port', [], ['account_id', 'sector_id', 'game_id']);
foreach ($dbResult->records() as $dbRecord) {
	Port::getCachedPort($dbRecord->getInt('game_id'), $dbRecord->getInt('sector_id'), $dbRecord->getInt('account_id'))->addCachePort($dbRecord->getInt('account_id'));
	Port::clearCache();
}
