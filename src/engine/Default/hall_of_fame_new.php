<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

$game_id = $var['game_id'] ?? null;

if (empty($game_id)) {
	$topic = 'All Time Hall of Fame';
} else {
	$topic = 'Hall of Fame: ' . SmrGame::getGame($game_id)->getDisplayName();
}
$template->assign('PageTopic', $topic);

$container = Page::create('hall_of_fame_player_detail.php');
if (isset($game_id)) {
	$container['game_id'] = $game_id;
}
$template->assign('PersonalHofHREF', $container->href());

$breadcrumb = Smr\HallOfFame::buildBreadcrumb($var, isset($game_id) ? 'Current HoF' : 'Global HoF');
$template->assign('Breadcrumb', $breadcrumb);

$viewType = $var['viewType'] ?? '';
$hofVis = SmrPlayer::getHOFVis();

if (!isset($hofVis[$viewType])) {
	// Not a complete HOF type, so continue to show categories
	$allowedVis = [HOF_PUBLIC, HOF_ALLIANCE];
	$categories = Smr\HallOfFame::getHofCategories($allowedVis, $game_id, $account->getAccountID());
	$template->assign('Categories', $categories);

} else {
	// Rankings page
	$db = Smr\Database::getInstance();
	$gameIDSql = ' AND game_id ' . (isset($game_id) ? '= ' . $db->escapeNumber($game_id) : 'IN (SELECT game_id FROM game WHERE end_time < ' . Smr\Epoch::time() . ' AND ignore_stats = ' . $db->escapeBoolean(false) . ')');

	$rank = 1;
	$foundMe = false;

	$viewTypeList = explode(':', $viewType);
	$view = end($viewTypeList);

	if ($view == HOF_TYPE_DONATION) {
		$dbResult = $db->read('SELECT account_id, SUM(amount) as amount FROM account_donated
					GROUP BY account_id ORDER BY amount DESC, account_id ASC LIMIT 25');
	} elseif ($view == HOF_TYPE_USER_SCORE) {
		$statements = SmrAccount::getUserScoreCaseStatement($db);
		$query = 'SELECT account_id, ' . $statements['CASE'] . ' amount FROM (SELECT account_id, type, SUM(amount) amount FROM player_hof WHERE type IN (' . $statements['IN'] . ')' . $gameIDSql . ' GROUP BY account_id,type) x GROUP BY account_id ORDER BY amount DESC, account_id ASC LIMIT 25';
		$dbResult = $db->read($query);
	} else {
		$dbResult = $db->read('SELECT account_id,SUM(amount) amount FROM player_hof WHERE type=' . $db->escapeString($viewType) . $gameIDSql . ' GROUP BY account_id ORDER BY amount DESC, account_id ASC LIMIT 25');
	}
	$rows = [];
	foreach ($dbResult->records() as $dbRecord) {
		$accountID = $dbRecord->getInt('account_id');
		if ($accountID == $account->getAccountID()) {
			$foundMe = true;
		}
		$amount = Smr\HallOfFame::applyHofVisibilityMask($dbRecord->getFloat('amount'), $hofVis[$viewType], $game_id, $accountID);
		$rows[] = Smr\HallOfFame::displayHOFRow($rank++, $accountID, $amount);
	}
	if (!$foundMe) {
		$rank = Smr\HallOfFame::getHofRank($viewType, $account->getAccountID(), $game_id);
		$rows[] = Smr\HallOfFame::displayHOFRow($rank['Rank'], $account->getAccountID(), $rank['Amount']);
	}
	$template->assign('Rows', $rows);
}
