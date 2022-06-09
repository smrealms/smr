<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();
$alliance = $player->getAlliance();

$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

// Get the current teams
require_once(LIB . 'Draft/alliance_pick.inc.php');
$teams = get_draft_teams($player->getGameID());
$template->assign('Teams', $teams);

// Add information about current player
$template->assign('PlayerID', $player->getPlayerID());
$template->assign('CanPick', $teams[$player->getAccountID()]['CanPick']);

// If players were placed into the NHA, they are still eligible to be picked
try {
	$NHA = SmrAlliance::getAllianceByName(NHA_ALLIANCE_NAME, $player->getGameID());
	$NHAID = $NHA->getAllianceID();
} catch (Smr\Exceptions\AllianceNotFound) {
	$NHAID = 0;
}

// Get a list of players still in the pick pool
$players = [];
$dbResult = $db->read('SELECT * FROM player WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' AND (alliance_id=0 OR alliance_id=' . $db->escapeNumber($NHAID) . ') AND account_id NOT IN (SELECT account_id FROM draft_leaders WHERE draft_leaders.game_id=player.game_id) AND account_id NOT IN (SELECT picked_account_id FROM draft_history WHERE draft_history.game_id=player.game_id) AND account_id != ' . $db->escapeNumber(ACCOUNT_ID_NHL) . ';');
foreach ($dbResult->records() as $dbRecord) {
	$pickPlayer = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), false, $dbRecord);
	$players[] = [
		'Player' => $pickPlayer,
		'HREF' => Page::create('alliance_pick_processing.php', ['PickedAccountID' => $pickPlayer->getAccountID()])->href(),
	];
}

$template->assign('PickPlayers', $players);

// Get the draft history
$history = [];
$dbResult = $db->read('SELECT * FROM draft_history WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' ORDER BY draft_id');
foreach ($dbResult->records() as $dbRecord) {
	$leader = SmrPlayer::getPlayer($dbRecord->getInt('leader_account_id'), $player->getGameID());
	$pickedPlayer = SmrPlayer::getPlayer($dbRecord->getInt('picked_account_id'), $player->getGameID());
	$history[] = [
		'Leader' => $leader,
		'Player' => $pickedPlayer,
		'Time' => $dbRecord->getInt('time'),
	];
}

$template->assign('History', $history);
