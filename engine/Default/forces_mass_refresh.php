<?php

//variables
if($player->hasAlliance())
{
	//get treaties
	$db->query('SELECT * FROM alliance_treaties WHERE (alliance_id_1 = '.$player->getAllianceID().' OR alliance_id_2 = '.$player->getAllianceID().')
				AND game_id = '.$player->getGameID().'
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
			WHERE sector_has_forces.sector_id = '.$player->getSectorID().'
			AND alliance_id IN (' . $db->escapeArray($allied) . ')
			AND player.game_id = '.$player->getGameID());
	$time = TIME;
	$db2 = new SmrMySqlDatabase();
	while ($db->nextRecord())
	{
		$time += 2;
		$db2->query('UPDATE sector_has_forces SET refresh_at='.$time.', refresher='.$player->getAccountID() .' WHERE game_id = '.$player->getGameID().' AND sector_id = ' .
			$player->getSectorID().' AND owner_id='.$db->getField('account_id').' LIMIT 1');
	}
}
else
{
	$db->query('UPDATE sector_has_forces SET refresh_at='.(TIME+2).', refresher='.$player->getAccountID() .' WHERE game_id = '.$player->getGameID().' AND sector_id = ' .
			$player->getSectorID().' AND owner_id='.$player->getAccountID().' LIMIT 1');
}
$message = '[Force Check]'; //this notifies the CS to look for info.
/*$db->query('REPLACE INTO sector_message (account_id, game_id, message) VALUES ' .
			'($player->getAccountID(), $player->getGameID(), '.$db->escapeString($message')');*/
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'current_sector.php';
$container['msg'] = $message;
forward($container);

?>