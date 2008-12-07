<?

$db->query('SELECT * FROM player_has_stats WHERE game_id > 6');
$db2 = new SMR_DB();
$done = 0;
while ($db->next_record())
{
	$kills = $db->f('kills');
	$deaths = $db->f('deaths');
	$account_id = $db->f('account_id');
	$game_id = $db->f('game_id');
	$db2->query('UPDATE account_has_stats SET games_joined = games_joined - 1, deaths = deaths - '.$deaths.', kills = kills - '.$kills.' WHERE account_id = '.$account_id);
	$PHP_OUTPUT.=('UPDATE account_has_stats SET games_joined = games_joined - 1, deaths = deaths - '.$deaths.', kills = kills - '.$kills.' WHERE account_id = '.$account_id.'<br>');
	$db2->query('UPDATE player_has_stats SET deaths = 0, kills = 0 WHERE account_id = '.$account_id.' AND game_id = '.$game_id);
	$PHP_OUTPUT.=('UPDATE player_has_stats SET deaths = 0, kills = 0 WHERE account_id = '.$account_id.' AND game_id = '.$game_id.'<br>');
	$done++;
}
$PHP_OUTPUT.=('<br><br>Done '.$done);
?>