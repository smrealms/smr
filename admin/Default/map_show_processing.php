<?php

$db2 = new SmrMySqlDatabase();
$account_id = $_REQUEST['account_id'];
if (!empty($account_id)) {
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
	$db->query('SELECT * FROM port WHERE game_id = '.$db->escapeNumber($game_id));
	while ($db->nextRecord()) {
		$port = SmrPort::getPort($game_id, $db->getField('sector_id'), false, $db);
		$port->addCachePorts($account_id);
	}

}

forward(create_container('skeleton.php', 'admin_tools.php'));
