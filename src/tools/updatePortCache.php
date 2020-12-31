<?php declare(strict_types=1);
require_once('../htdocs/config.inc');

$db = MySqlDatabase::getInstance();
$db->query('SELECT account_id,sector_id,game_id FROM player_visited_port');
while ($db->nextRecord()) {
	SmrPort::getCachedPort($db->getInt('game_id'), $db->getInt('sector_id'), $db->getInt('account_id'))->addCachePort($db->getInt('account_id'));
	SmrPort::clearCache();
}
