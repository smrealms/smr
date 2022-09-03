<?php declare(strict_types=1);

use Smr\Database;

require_once('../bootstrap.php');

$db = Database::getInstance();
$dbResult = $db->read('SELECT account_id,sector_id,game_id FROM player_visited_port');
foreach ($dbResult->records() as $dbRecord) {
	SmrPort::getCachedPort($dbRecord->getInt('game_id'), $dbRecord->getInt('sector_id'), $dbRecord->getInt('account_id'))->addCachePort($dbRecord->getInt('account_id'));
	SmrPort::clearCache();
}
