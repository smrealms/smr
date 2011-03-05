<?php

$db->query('SELECT alliance_id,alliance_name,leader_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $player->getAllianceID() . ' LIMIT 1');
$db->nextRecord();
$template->assign('PageTopic',stripslashes($db->getField('alliance_name')) . ' (' . $db->getField('alliance_id') . ')');
include(get_file_loc('menue.inc'));
create_alliance_menue($alliance_id,$db->getField('leader_id'));

$players = array();
$db->query('SELECT * FROM player WHERE game_id='.$db->escapeNumber($player->getGameID()).' AND alliance_id=0;');
while($db->nextRecord())
{
	$players[] =& SmrPlayer::getPlayer($db->getRow(), $player->getGameID());
}

$template->assign('PlayerPickHREF', SmrSession::get_new_href(create_container('alliance_pick_processing.php')));
$template->assignByRef('PickPlayers', $players);
?>