<?php
require_once('../htdocs/config.inc');
require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
require_once(LIB . 'Default/Globals.class.inc');
require_once(LIB . 'Default/SmrPort.class.inc');

$db = new SmrMySqlDatabase();
$db->query('SELECT account_id,sector_id,game_id FROM player_visited_port');
while ($db->nextRecord()) {
	SmrPort::getCachedPort($db->getInt('game_id'),$db->getInt('sector_id'),$db->getInt('account_id'))->addCachePort($db->getInt('account_id'));
	SmrPort::clearCache();
}

?>