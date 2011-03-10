<?php

if(!$player->isPresident())
{
	create_error('Only the president can view the embassy.');
}

$action = $_REQUEST['action'];
$race_id = $var['race_id'];
$type = strtoupper($action);
$time = 259200;
//adjust for game speed
//adjust it correctly now
$time = $time / Globals::getGameSpeed($player->getGameID());
$time = TIME + $time;

$db->query('SELECT * FROM race_has_voting ' .
		   'WHERE game_id = '.$player->getGameID().' AND ' .
				 'race_id_1 = '.$player->getRaceID());
if ($db->getNumRows() > 2)
	create_error('You can\'t initiate more than 3 votes at a time!');

$db->query('REPLACE INTO race_has_voting ' .
		   '(game_id, race_id_1, race_id_2, type, end_time) ' .
		   'VALUES('.$player->getGameID().', '.$player->getRaceID().', '.$race_id.', '.$db->escapeString($type).', '.$time.')');

if ($type == 'PEACE')
	$db->query('REPLACE INTO race_has_voting ' .
			   '(game_id, race_id_1, race_id_2, type, end_time) ' .
			   'VALUES('.$player->getGameID().', '.$race_id.', '.$player->getRaceID().', '.$db->escapeString($type).', '.$time.')');

forward(create_container('skeleton.php', 'council_embassy.php'));

?>