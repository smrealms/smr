<?php declare(strict_types=1);

// NOTE: this is only for history database games

$db->switchDatabases($var['HistoryDatabase']);

$template->assign('PageTopic', 'Hall of Fame : ' . $var['game_name']);
Menu::history_games(2);

if (!isset($var['stat'])) {
	// Display a list of stats available to view
	$db->query('SHOW COLUMNS FROM player_has_stats');
	while ($db->nextRecord()) {
		$stat = $db->getField('Field');
		if ($stat == 'account_id' || $stat == 'game_id') {
			continue;
		}
		$statDisplay = ucwords(str_replace('_', ' ', $stat));
		$container = $var;
		$container['stat'] = $stat;
		$container['stat_display'] = $statDisplay;
		$links[] = create_link($container, $statDisplay);
	}
	$template->assign('Links', $links);
} else {
	// Link back to overview page
	$container = $var;
	unset($container['stat']);
	unset($container['stat_display']);
	$template->assign('BackHREF', SmrSession::getNewHREF($container));

	$template->assign('StatName', $var['stat_display']);

	// Rankings display
	$oldAccountId = $account->getOldAccountID($var['HistoryDatabase']);
	$db->query('SELECT * FROM player_has_stats JOIN player USING(account_id, game_id) WHERE game_id=' . $db->escapeNumber($var['view_game_id']) . ' ORDER BY player_has_stats.' . $var['stat'] . ' DESC LIMIT 25');
	$rankings = [];
	while ($db->nextRecord()) {
		$rankings[] = [
			'bold' => $db->getInt('account_id') == $oldAccountId ? 'class="bold"' : '',
			'name' => $db->getField('player_name'),
			'stat' => $db->getInt($var['stat']),
		];
	}
	$template->assign('Rankings', $rankings);
}

$db->switchDatabaseToLive(); // restore database
