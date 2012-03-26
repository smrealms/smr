<?php

$db2 = new SmrMySqlDatabase();
$account_id = $_REQUEST['account_id'];
if (!empty($account_id)) {
	require_once(get_file_loc('SmrPort.class.inc'));
	$game_id = $var['game_id'];
	if($account_id=='all') {
		$account_id=array();
		$db->query('SELECT account_id FROM player WHERE game_id='.$db->escapeNumber($game_id));
		while ($db->nextRecord()) {
			$account_id[] = $db->getField('account_id');
		}
	}
	else {
		$account_id = array($account_id);
	}

	// delete all entries from the player_visited_sector/port table
	$db->query('DELETE FROM player_visited_sector WHERE account_id IN ('.$db->escapeArray($account_id).') AND game_id = '.$db->escapeNumber($game_id));

	// add port infos
	$db->query('SELECT sector_id FROM port WHERE game_id = '.$game_id.' ORDER BY sector_id');
	while ($db->nextRecord()) {
		SmrPort::getPort($game_id,$db->getField('sector_id'))->addCachePorts($account_id);
	}

}

forward(create_container('skeleton.php', 'game_play.php'))

?>