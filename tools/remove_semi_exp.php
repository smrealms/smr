#!/usr/bin/php -q
<?php

//config file
include( realpath(dirname(__FILE__)) . '/../htdocs/config.inc');

include(LIB . 'global/smr_db.inc');

$db = new SMR_DB();
$db2 = new SMR_DB();

$db->query('SELECT * FROM player_has_stats WHERE game_id = 22');
while ($db->next_record())
{
	$traded_exp = $db->f('experience_traded');
	$db2->query('UPDATE player SET experience = experience - ' . $traded_exp . ' WHERE account_id = ' . 
$db->f('account_id') . ' AND game_id = 22');
}

//$db->query('UPDATE player_has_stats SET experience_traded = 0 WHERE game_id = 22');

?>
