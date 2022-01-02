<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();
$player = $session->hasGame() ? $session->getPlayer() : null;

$account_id = $var['account_id'] ?? $account->getAccountID();
$game_id = $var['game_id'] ?? null;

if (isset($game_id)) {
	try {
		$hofPlayer = SmrPlayer::getPlayer($account_id, $game_id);
	} catch (Smr\Exceptions\PlayerNotFound) {
		create_error('That player has not yet joined this game.');
	}
	$template->assign('PageTopic', htmlentities($hofPlayer->getPlayerName()) . '\'s Personal Hall of Fame: ' . SmrGame::getGame($game_id)->getDisplayName());
} else {
	$hofName = SmrAccount::getAccount($account_id)->getHofDisplayName();
	$template->assign('PageTopic', $hofName . '\'s All Time Personal Hall of Fame');
}

$allowedVisibities = array(HOF_PUBLIC);
if ($account->getAccountID() == $account_id) {
	$allowedVisibities[] = HOF_ALLIANCE;
	$allowedVisibities[] = HOF_PRIVATE;
} elseif (isset($hofPlayer) && $hofPlayer->sameAlliance($player)) {
	$allowedVisibities[] = HOF_ALLIANCE;
}

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT type FROM hof_visibility WHERE visibility IN (' . $db->escapeArray($allowedVisibities) . ') ORDER BY type');
const DONATION_NAME = 'Money Donated To SMR';
const USER_SCORE_NAME = 'User Score';
$hofTypes = array(DONATION_NAME=>true, USER_SCORE_NAME=>true);
foreach ($dbResult->records() as $dbRecord) {
	$hof =& $hofTypes;
	$typeList = explode(':', $dbRecord->getString('type'));
	foreach ($typeList as $type) {
		if (!isset($hof[$type])) {
			$hof[$type] = array();
		}
		$hof =& $hof[$type];
	}
	$hof = true;
}
$template->assign('Breadcrumb', Smr\HallOfFame::buildBreadcrumb($var, $hofTypes, 'Personal HoF'));

if (!isset($var['view'])) {
	$categories = Smr\HallOfFame::getHofCategories($hofTypes, $game_id, $account_id);
	$template->assign('Categories', $categories);
} else {
	// Category rankings page
	$viewType = $var['type'];
	$viewType[] = $var['view'];

	$hofRank = Smr\HallOfFame::getHofRank($var['view'], $viewType, $account_id, $game_id);
	$rows = [Smr\HallOfFame::displayHOFRow($hofRank['Rank'], $account_id, $hofRank['Amount'])];

	if ($account->getAccountID() != $account_id) {
		//current player's score.
		$playerRank = Smr\HallOfFame::getHofRank($var['view'], $viewType, $account->getAccountID(), $game_id);
		$row = Smr\HallOfFame::displayHOFRow($playerRank['Rank'], $account->getAccountID(), $playerRank['Amount']);
		if ($playerRank['Rank'] >= $hofRank['Rank']) {
			$rows[] = $row;
		} else {
			array_unshift($rows, $row);
		}
	}
	$template->assign('Rows', $rows);
}
