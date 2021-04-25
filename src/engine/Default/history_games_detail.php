<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$game_id = $var['view_game_id'];
$template->assign('PageTopic', 'Extended Stats : ' . $var['game_name']);
Menu::history_games(1);

$container = Page::copy($var);
$container['body'] = 'history_games_detail.php';
if (isset($container['action'])) {
	unset($container['action']);
}
$template->assign('SelfHREF', $container->href());

// Default page has no category (action) selected yet
$action = $session->getRequestVar('action', '');
if (!empty($action)) {
	list($sql, $from, $dis) = match($action) {
		'Top Mined Sectors' => ['mines', 'sector', 'Mines'],
		'Sectors with most Forces' => ['mines + combat + scouts', 'sector', 'Forces'],
		'Top Killing Sectors' => ['kills', 'sector', 'Kills'],
		'Top Planets' => ['ROUND((turrets + hangers + generators) / 3, 2)', 'planet', 'Planet Level'],
		'Top Alliance Kills' => ['kills', 'alliance', 'Kills'],
		'Top Alliance Deaths' => ['deaths', 'alliance', 'Deaths'],
	};
	$template->assign('Description', $dis);

	$rankings = [];
	$db = Smr\Database::getInstance();
	$db->switchDatabases($var['HistoryDatabase']);
	if ($from != 'alliance') {
		$template->assign('Name', 'Sector ID');
		$db->query('SELECT ' . $sql . ' as val, sector_id FROM ' . $from . ' WHERE game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY val DESC LIMIT 25');
		while ($db->nextRecord()) {
			$rankings[] = [
				'name' => $db->getInt('sector_id'),
				'value' => $db->getField('val'),
			];
		}
	} else {
		$template->assign('Name', 'Alliance');
		$db->query('SELECT alliance_name, alliance_id, ' . $sql . ' as val FROM alliance WHERE game_id = ' . $db->escapeNumber($game_id) . ' AND alliance_id > 0 GROUP BY alliance_id ORDER BY val DESC, alliance_id LIMIT 25');
		$container = Page::copy($var);
		$container['body'] = 'history_alliance_detail.php';
		$container['selected_index'] = 1;
		while ($db->nextRecord()) {
			$name = htmlentities($db->getField('alliance_name'));
			$container['alliance_id'] = $db->getInt('alliance_id');
			$rankings[] = [
				'name' => create_link($container, $name),
				'value' => $db->getField('val'),
			];
		}
	}
	$db->switchDatabaseToLive(); // restore database
	$template->assign('Rankings', $rankings);
}
