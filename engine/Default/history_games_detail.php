<?php

$game_id = $var['view_game_id'];
$template->assign('PageTopic', 'Extended Stats : '.$var['game_name']);
Menu::history_games(1);

$container = $var;
$container['body'] = 'history_games_detail.php';
unset($container['action']);
$template->assign('SelfHREF', SmrSession::getNewHREF($container));

$action = SmrSession::getRequestVar('action');
if (!empty($action)) {
	if ($action == 'Top Mined Sectors') {
		$sql = 'mines'; $from = 'sector'; $dis = 'Mines';
	} elseif ($action == 'Sectors with most Forces') {
		$sql = 'mines + combat + scouts'; $from = 'sector'; $dis = 'Forces';
	} elseif ($action == 'Top Killing Sectors') {
		$sql = 'kills'; $from = 'sector'; $dis = 'Kills';
	} elseif ($action == 'Top Planets') {
		$sql = 'ROUND((turrets + hangers + generators) / 3, 2)'; $from = 'planet'; $dis = 'Planet Level';
	} elseif ($action == 'Top Alliance Kills') {
		$sql = 'kills'; $from = 'alliance'; $dis = 'Kills';
	} elseif ($action == 'Top Alliance Deaths') {
		$sql = 'deaths'; $from = 'alliance'; $dis = 'Deaths';
	}
	$template->assign('Description', $dis);

	$rankings = [];
	$db = new $var['HistoryDatabase']();
	if ($from != 'alliance') {
		$template->assign('Name', 'Sector ID');
		$db->query('SELECT '.$sql.' as val, sector_id FROM '.$from.' WHERE game_id = '.$db->escapeNumber($game_id).' ORDER BY val DESC LIMIT 25');
		while ($db->nextRecord()) {
			$rankings[] = [
				'name' => $db->getInt('sector_id'),
				'value' => $db->getField('val'),
			];
		}
	}
	else {
		$template->assign('Name', 'Alliance');
		$db->query('SELECT alliance_name, alliance_id, '.$sql.' as val FROM alliance WHERE game_id = '.$db->escapeNumber($game_id).' AND alliance_id > 0 GROUP BY alliance_id ORDER BY val DESC, alliance_id LIMIT 25');
		$container = $var;
		$container['body'] = 'history_alliance_detail.php';
		$container['selected_index'] = 1;
		while ($db->nextRecord()) {
			$name = stripslashes($db->getField('alliance_name'));
			$container['alliance_id'] = $db->getInt('alliance_id');
			$rankings[] = [
				'name' => create_link($container, $name),
				'value' => $db->getField('val'),
			];
		}
	}
	$template->assign('Rankings', $rankings);
}

$db = new SmrMySqlDatabase();
