#!/usr/bin/php -q
<?php

//config file
include( realpath(dirname(__FILE__)) . '/../htdocs/config.inc');

include(LIB . 'Default/SmrMySqlDatabase.class.inc');

$db = new SmrMySqlDatabase();
$db2 = new SmrMySqlDatabase();

$db->query('SELECT * FROM player_has_stats WHERE game_id = 22');
while ($db->nextRecord())
{
	$traded_exp = $db->getField('experience_traded');
	$db2->query('UPDATE player SET experience = experience - ' . $traded_exp . ' WHERE account_id = ' . 
$db->getField('account_id') . ' AND game_id = 22');
}

//$db->query('UPDATE player_has_stats SET experience_traded = 0 WHERE game_id = 22');
