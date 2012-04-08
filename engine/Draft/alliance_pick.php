<?php
$alliance =& $player->getAlliance();
$template->assign('PageTopic',$alliance->getAllianceName() . ' (' . $alliance->getAllianceID() . ')');
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

$players = array();
$db->query('SELECT * FROM player WHERE game_id='.$db->escapeNumber($player->getGameID()).' AND (alliance_id=0 OR alliance_id='.$db->escapeNumber(NHA_ID).') AND account_id NOT IN (SELECT account_id FROM draft_leaders WHERE draft_leaders.account_id=player.account_id) AND sector_id!=1 AND account_id != '.$db->escapeNumber(ACCOUNT_ID_NHL).';');
while($db->nextRecord())
{
	$pickPlayer =& SmrPlayer::getPlayer($db->getRow(), $player->getGameID());
	$players[] = array('Player' => &$pickPlayer,
						'HREF' => SmrSession::getNewHREF(create_container('alliance_pick_processing.php','',array('PickedAccountID'=>$pickPlayer->getAccountID()))));
}

$template->assignByRef('PickPlayers', $players);
?>