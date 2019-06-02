<?php
require_once(get_file_loc('hof.functions.inc'));

if (isset($var['account_id'])) {
	$account_id = $var['account_id'];
} else {
	$account_id = $account->getAccountID();
}
$game_id = null;
if (isset($var['game_id'])) {
	$game_id = $var['game_id'];
}

if (isset($var['game_id'])) {
	try {
		$hofPlayer = SmrPlayer::getPlayer($account_id, $var['game_id']);
	} catch (PlayerNotFoundException $e) {
		create_error('That player has not yet joined this game.');
	}
	$template->assign('PageTopic', $hofPlayer->getPlayerName() . '\'s Personal Hall of Fame For ' . Globals::getGameName($var['game_id']));
} else {
	$hofName = SmrAccount::getAccount($account_id)->getHofName();
	$template->assign('PageTopic', $hofName . '\'s All Time Personal Hall of Fame');
}

$allowedVisibities = array(HOF_PUBLIC);
if ($account->getAccountID() == $account_id) {
	$allowedVisibities[] = HOF_ALLIANCE;
	$allowedVisibities[] = HOF_PRIVATE;
} else if (isset($hofPlayer) && $hofPlayer->sameAlliance($player)) {
	$allowedVisibities[] = HOF_ALLIANCE;
}
$db->query('SELECT type FROM hof_visibility WHERE visibility IN (' . $db->escapeArray($allowedVisibities) . ') ORDER BY type');
const DONATION_NAME = 'Money Donated To SMR';
const USER_SCORE_NAME = 'User Score';
$hofTypes = array(DONATION_NAME=>true, USER_SCORE_NAME=>true);
while ($db->nextRecord()) {
	$hof =& $hofTypes;
	$typeList = explode(':', $db->getField('type'));
	foreach ($typeList as $type) {
		if (!isset($hof[$type])) {
			$hof[$type] = array();
		}
		$hof =& $hof[$type];
	}
	$hof = true;
}
$template->assign('Breadcrumb', buildBreadcrumb($var, $hofTypes, 'Personal HoF'));

if (!isset($var['view'])) {
	$categories = getHofCategories($hofTypes, $game_id, $account_id);
	$template->assign('Categories', $categories);
} else {
	// Category rankings page
	$viewType = $var['type'];
	$viewType[] = $var['view'];

	$hofRank = getHofRank($var['view'], $viewType, $account_id, $game_id);
	$rows = [displayHOFRow($hofRank['Rank'], $account_id, $hofRank['Amount'])];

	if ($account->getAccountID() != $account_id) {
		//current player's score.
		$playerRank = getHofRank($var['view'], $viewType, $account->getAccountID(), $game_id);
		$row = displayHOFRow($playerRank['Rank'], $account->getAccountID(), $playerRank['Amount']);
		if ($playerRank['Rank'] >= $hofRank['Rank']) {
			$rows[] = $row;
		} else {
			array_unshift($rows, $row);
		}
	}
	$template->assign('Rows', $rows);
}
