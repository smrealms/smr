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

$container = Page::create('skeleton.php', 'hall_of_fame_player_detail.php');
if (isset($game_id)) {
	$container['game_id'] = $game_id;
}
$template->assign('PersonalHofHREF', $container->href());

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT type FROM hof_visibility WHERE visibility != ' . $db->escapeString(HOF_PRIVATE) . ' ORDER BY type');
const DONATION_NAME = 'Money Donated To SMR';
const USER_SCORE_NAME = 'User Score';
$hofTypes = [DONATION_NAME => true, USER_SCORE_NAME => true];
foreach ($dbResult->records() as $dbRecord) {
	$hof =& $hofTypes;
	$typeList = explode(':', $dbRecord->getString('type'));
	foreach ($typeList as $type) {
		if (!isset($hof[$type])) {
			$hof[$type] = [];
		}
		$hof =& $hof[$type];
	}
	$hof = true;
}
$template->assign('Breadcrumb', Smr\HallOfFame::buildBreadcrumb($var, $hofTypes, isset($game_id) ? 'Current HoF' : 'Global HoF'));

if (!isset($var['view'])) {
	$categories = Smr\HallOfFame::getHofCategories($hofTypes, $game_id, $account->getAccountID());
	$template->assign('Categories', $categories);
} else {
	$gameIDSql = ' AND game_id ' . (isset($game_id) ? '= ' . $db->escapeNumber($game_id) : 'IN (SELECT game_id FROM game WHERE end_time < ' . Smr\Epoch::time() . ' AND ignore_stats = ' . $db->escapeBoolean(false) . ')');

	$vis = HOF_PUBLIC;
	$rank = 1;
	$foundMe = false;

	$viewType = $var['type'];
	$viewType[] = $var['view'];
	$viewType = implode(':', $viewType);

	if ($var['view'] == DONATION_NAME) {
		$dbResult = $db->read('SELECT account_id, SUM(amount) as amount FROM account_donated
					GROUP BY account_id ORDER BY amount DESC, account_id ASC LIMIT 25');
	} elseif ($var['view'] == USER_SCORE_NAME) {
		$statements = SmrAccount::getUserScoreCaseStatement($db);
		$query = 'SELECT account_id, ' . $statements['CASE'] . ' amount FROM (SELECT account_id, type, SUM(amount) amount FROM player_hof WHERE type IN (' . $statements['IN'] . ')' . $gameIDSql . ' GROUP BY account_id,type) x GROUP BY account_id ORDER BY amount DESC, account_id ASC LIMIT 25';
		$dbResult = $db->read($query);
	} else {
		$dbResult = $db->read('SELECT visibility FROM hof_visibility WHERE type = ' . $db->escapeString($viewType) . ' LIMIT 1');
		if ($dbResult->hasRecord()) {
			$vis = $dbResult->record()->getString('visibility');
		}
		$dbResult = $db->read('SELECT account_id,SUM(amount) amount FROM player_hof WHERE type=' . $db->escapeString($viewType) . $gameIDSql . ' GROUP BY account_id ORDER BY amount DESC, account_id ASC LIMIT 25');
	}
	$rows = [];
	foreach ($dbResult->records() as $dbRecord) {
		$accountID = $dbRecord->getInt('account_id');
		if ($accountID == $account->getAccountID()) {
			$foundMe = true;
		}
		$amount = Smr\HallOfFame::applyHofVisibilityMask($dbRecord->getFloat('amount'), $vis, $game_id, $accountID);
		$rows[] = Smr\HallOfFame::displayHOFRow($rank++, $accountID, $amount);
	}
	if (!$foundMe) {
		$rank = Smr\HallOfFame::getHofRank($var['view'], $viewType, $account->getAccountID(), $game_id);
		$rows[] = Smr\HallOfFame::displayHOFRow($rank['Rank'], $account->getAccountID(), $rank['Amount']);
	}
	$template->assign('Rows', $rows);
}
