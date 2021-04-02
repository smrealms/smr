<?php declare(strict_types=1);
$alliance = $player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

// Get the current teams
require_once(get_file_loc('alliance_pick.inc.php'));
$teams = get_draft_teams($player->getGameID());
$template->assign('Teams', $teams);

// Add information about current player
$template->assign('PlayerID', $player->getPlayerID());
$template->assign('CanPick', $teams[$player->getAccountID()]['CanPick']);

// Get a list of players still in the pick pool
$players = array();
$db->query('SELECT * FROM player WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' AND (alliance_id=0 OR alliance_id=' . $db->escapeNumber(NHA_ID) . ') AND account_id NOT IN (SELECT account_id FROM draft_leaders WHERE draft_leaders.game_id=player.game_id) AND account_id NOT IN (SELECT picked_account_id FROM draft_history WHERE draft_history.game_id=player.game_id) AND account_id != ' . $db->escapeNumber(ACCOUNT_ID_NHL) . ';');
while ($db->nextRecord()) {
	$pickPlayer = SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID(), false, $db);
	$players[] = array('Player' => $pickPlayer,
						'HREF' => Page::create('alliance_pick_processing.php', '', array('PickedAccountID'=>$pickPlayer->getAccountID()))->href());
}

$template->assign('PickPlayers', $players);

// Get the draft history
$history = array();
$db->query('SELECT * FROM draft_history WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' ORDER BY draft_id');
while ($db->nextRecord()) {
	$leader = SmrPlayer::getPlayer($db->getInt('leader_account_id'), $player->getGameID());
	$pickedPlayer = SmrPlayer::getPlayer($db->getInt('picked_account_id'), $player->getGameID());
	$history[] = [
		'Leader' => $leader,
		'Player' => $pickedPlayer,
		'Time'   => $db->getInt('time'),
	];
}

$template->assign('History', $history);
