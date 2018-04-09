<?php
$alliance =& $player->getAlliance();
$template->assign('PageTopic',$alliance->getAllianceName() . ' (' . $alliance->getAllianceID() . ')');
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

// Get the current teams
require_once('alliance_pick.inc');
$teams = get_draft_teams($player->getGameID());
$template->assign('Teams', $teams);

// Add information about current player
$template->assign('PlayerID', $player->getPlayerID());
$template->assign('CanPick', $teams[$player->getAccountID()]['CanPick']);

// Get a list of players still in the pick pool
$players = array();
$db->query('SELECT * FROM player WHERE game_id='.$db->escapeNumber($player->getGameID()).' AND (alliance_id=0 OR alliance_id='.$db->escapeNumber(NHA_ID).') AND account_id NOT IN (SELECT account_id FROM draft_leaders WHERE draft_leaders.game_id=player.game_id) AND sector_id!=1 AND account_id != '.$db->escapeNumber(ACCOUNT_ID_NHL).';');
while($db->nextRecord()) {
	$pickPlayer =& SmrPlayer::getPlayer($db->getRow(), $player->getGameID());
	$players[] = array('Player' => &$pickPlayer,
						'HREF' => SmrSession::getNewHREF(create_container('alliance_pick_processing.php','',array('PickedAccountID'=>$pickPlayer->getAccountID()))));
}

$template->assign('PickPlayers', $players);

// Get the draft history
$history = array();
$db->query('SELECT * FROM draft_history WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' ORDER BY draft_id');
while ($db->nextRecord()) {
	$leader =& SmrPlayer::getPlayer($db->getInt('leader_account_id'), $player->getGameID());
	$pickedPlayer =& SmrPlayer::getPlayer($db->getInt('picked_account_id'), $player->getGameID());
	$history[] = array('Leader' => &$leader,
	                   'Player' => &$pickedPlayer,
	                   'Time'   => $db->getInt('time'));
}

$template->assign('History', $history);
