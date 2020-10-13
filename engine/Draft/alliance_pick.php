<?php declare(strict_types=1);
$alliance = $player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID(), $alliance->getLeaderID());

// Get the current teams
require_once('alliance_pick.inc');
$teams = get_draft_teams($player->getGameID());
$template->assign('Teams', $teams);

// Add information about current player
$template->assign('PlayerID', $player->getPlayerID());
$template->assign('CanPick', $teams[$player->getPlayerID()]['CanPick']);

// Get a list of players still in the pick pool
$players = array();
$db->query('SELECT * FROM player WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' AND (alliance_id=0 OR alliance_id=' . $db->escapeNumber(NHA_ID) . ') AND player_id NOT IN (SELECT player_id FROM draft_leaders WHERE draft_leaders.game_id=player.game_id) AND player_id NOT IN (SELECT picked_player_id FROM draft_history WHERE draft_history.game_id=player.game_id) AND player_id != ' . $db->escapeNumber(PLAYER_ID_NHL) . ';');
while ($db->nextRecord()) {
	$pickPlayer = SmrPlayer::getPlayer($db->getInt('player_id'), $player->getGameID(), false, $db);
	$players[] = array('Player' => $pickPlayer,
						'HREF' => SmrSession::getNewHREF(create_container('alliance_pick_processing.php', '', array('PickedPlayerID'=>$pickPlayer->getPlayerID()))));
}

$template->assign('PickPlayers', $players);

// Get the draft history
$history = array();
$db->query('SELECT * FROM draft_history WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' ORDER BY draft_id');
while ($db->nextRecord()) {
	$leader = SmrPlayer::getPlayer($db->getInt('leader_player_id'), $player->getGameID());
	$pickedPlayer = SmrPlayer::getPlayer($db->getInt('picked_player_id'), $player->getGameID());
	$history[] = array('Leader' => $leader,
	                   'Player' => $pickedPlayer,
	                   'Time'   => $db->getInt('time'));
}

$template->assign('History', $history);
