<?php

//variables
if($player->hasAlliance()) {
	//get treaties
	$db->query('SELECT * FROM alliance_treaties WHERE (alliance_id_1 = ' . $db->escapeNumber($player->getAllianceID()) . ' OR alliance_id_2 = ' . $db->escapeNumber($player->getAllianceID()) . ')
				AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND forces_nap = 1 AND official = \'TRUE\'');
	$allied[] = $player->getAllianceID();
	while ($db->nextRecord()) {
		if ($db->getField('alliance_id_1') == $player->getAllianceID()) $allied[] = $db->getField('alliance_id_2');
		else $allied[] = $db->getField('alliance_id_1');
	}
	//populate alliance list
	$db->query('SELECT account_id FROM player
			JOIN sector_has_forces
				ON sector_has_forces.game_id = player.game_id AND sector_has_forces.owner_id = player.account_id
			WHERE sector_has_forces.sector_id = ' . $db->escapeNumber($player->getSectorID()) . '
			AND alliance_id IN (' . $db->escapeArray($allied) . ')
			AND player.game_id = ' . $db->escapeNumber($player->getGameID()));
	$time = TIME;
	$db2 = new SmrMySqlDatabase();
	while ($db->nextRecord()) {
		$time += SmrForce::REFRESH_ALL_TIME_PER_STACK;
		$db2->query('UPDATE sector_has_forces SET refresh_at=' . $db2->escapeNumber($time) . ', refresher=' . $db2->escapeNumber($player->getAccountID()) . '
					WHERE game_id = ' . $db2->escapeNumber($player->getGameID()) . '
						AND sector_id = ' . $db2->escapeNumber($player->getSectorID()) . ' AND owner_id=' . $db2->escapeNumber($db->getInt('account_id')) . ' LIMIT 1');
	}
}
else {
	$db->query('UPDATE sector_has_forces SET refresh_at=' . $db->escapeNumber(TIME + SmrForce::REFRESH_ALL_TIME_PER_STACK).', refresher=' . $db->escapeNumber($player->getAccountID()) . '
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND sector_id = ' . $db->escapeNumber($player->getSectorID()) . ' AND owner_id=' . $db->escapeNumber($db->getInt('account_id')) . ' LIMIT 1');
}
$message = '[Force Check]'; //this notifies the CS to look for info.
$container = create_container('skeleton.php', 'current_sector.php');
$container['msg'] = $message;
forward($container);
