<?php declare(strict_types=1);

use Smr\Exceptions\PlayerNotFound;
use Smr\HallOfFame;

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
	} catch (PlayerNotFound) {
		create_error('That player has not yet joined this game.');
	}
	$template->assign('PageTopic', htmlentities($hofPlayer->getPlayerName()) . '\'s Personal Hall of Fame: ' . SmrGame::getGame($game_id)->getDisplayName());
} else {
	$hofName = SmrAccount::getAccount($account_id)->getHofDisplayName();
	$template->assign('PageTopic', $hofName . '\'s All Time Personal Hall of Fame');
}

$breadcrumb = HallOfFame::buildBreadcrumb($var, 'Personal HoF');
$template->assign('Breadcrumb', $breadcrumb);

$viewType = $var['viewType'] ?? '';
$hofVis = SmrPlayer::getHOFVis();

if (!isset($hofVis[$viewType])) {
	// Not a complete HOF type, so continue to show categories
	$allowedVis = [HOF_PUBLIC];
	if ($account->getAccountID() == $account_id) {
		$allowedVis[] = HOF_ALLIANCE;
		$allowedVis[] = HOF_PRIVATE;
	} elseif (isset($hofPlayer) && $hofPlayer->sameAlliance($player)) {
		$allowedVis[] = HOF_ALLIANCE;
	}
	$categories = HallOfFame::getHofCategories($allowedVis, $game_id, $account_id);
	$template->assign('Categories', $categories);

} else {
	// Rankings page
	$hofRank = HallOfFame::getHofRank($viewType, $account_id, $game_id);
	$rows = [HallOfFame::displayHOFRow($hofRank['Rank'], $account_id, $hofRank['Amount'])];

	if ($account->getAccountID() != $account_id) {
		//current player's score.
		$playerRank = HallOfFame::getHofRank($viewType, $account->getAccountID(), $game_id);
		$row = HallOfFame::displayHOFRow($playerRank['Rank'], $account->getAccountID(), $playerRank['Amount']);
		if ($playerRank['Rank'] >= $hofRank['Rank']) {
			$rows[] = $row;
		} else {
			array_unshift($rows, $row);
		}
	}
	$template->assign('Rows', $rows);
}
