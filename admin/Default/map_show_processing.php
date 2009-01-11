<?

$db2 = new SmrMySqlDatabase();
$account_id = $_REQUEST['account_id'];
if (!empty($account_id))
{
	require_once(get_file_loc('SmrPort.class.inc'));
	$game_id = $var['game_id'];

	// delete all entries from the player_visited_sector/port table
	$db->query('DELETE FROM player_visited_sector WHERE account_id = '.$account_id.' AND game_id = '.$game_id);
	$db->query('DELETE FROM player_visited_port WHERE account_id = '.$account_id.' AND game_id = '.$game_id);

	// add port infos
	$db->query('SELECT sector_id FROM port WHERE game_id = '.$game_id.' ORDER BY sector_id');
	while ($db->nextRecord())
	{
		SmrPort::getPort($game_id,$db->getField('sector_id'))->cachePort($account_id);
	}

}

forward(create_container('skeleton.php', 'game_play.php'))

?>