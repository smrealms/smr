<?php declare(strict_types=1);

// NOTE: this is only for history database games

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

$db = Smr\Database::getInstance();
$db->switchDatabases($var['HistoryDatabase']);

$template->assign('PageTopic', 'Hall of Fame : ' . $var['game_name']);
Menu::historyGames(2);

if (!isset($var['stat'])) {
	// Display a list of stats available to view
	$links = [];
	$dbResult = $db->read('SHOW COLUMNS FROM player_has_stats');
	foreach ($dbResult->records() as $dbRecord) {
		$stat = $dbRecord->getField('Field');
		if ($stat == 'account_id' || $stat == 'game_id') {
			continue;
		}
		$statDisplay = ucwords(str_replace('_', ' ', $stat));
		$container = Page::copy($var);
		$container['stat'] = $stat;
		$container['stat_display'] = $statDisplay;
		$links[] = create_link($container, $statDisplay);
	}
	$template->assign('Links', $links);
} else {
	// Link back to overview page
	$container = Page::copy($var);
	unset($container['stat']);
	unset($container['stat_display']);
	$template->assign('BackHREF', $container->href());

	$template->assign('StatName', $var['stat_display']);

	// Rankings display
	$oldAccountId = $account->getOldAccountID($var['HistoryDatabase']);
	$dbResult = $db->read('SELECT * FROM player_has_stats JOIN player USING(account_id, game_id) WHERE game_id=' . $db->escapeNumber($var['view_game_id']) . ' ORDER BY player_has_stats.' . $var['stat'] . ' DESC LIMIT 25');
	$rankings = [];
	foreach ($dbResult->records() as $dbRecord) {
		$rankings[] = [
			'bold' => $dbRecord->getInt('account_id') == $oldAccountId ? 'class="bold"' : '',
			'name' => $dbRecord->getField('player_name'),
			'stat' => $dbRecord->getInt($var['stat']),
		];
	}
	$template->assign('Rankings', $rankings);
}

$db->switchDatabaseToLive(); // restore database
