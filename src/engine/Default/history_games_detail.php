<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$game_id = $var['view_game_id'];
$template->assign('PageTopic', 'Extended Stats : ' . $var['game_name']);
Menu::historyGames(1);

$oldAccountID = $session->getAccount()->getOldAccountID($var['HistoryDatabase']);

$container = Page::create('history_games_detail.php', $var);
if (isset($container['action'])) {
	unset($container['action']);
}
$template->assign('SelfHREF', $container->href());

// Default page has no category (action) selected yet
$action = $session->getRequestVar('action', '');
if (!empty($action)) {
	$rankings = [];
	$db = Smr\Database::getInstance();
	$db->switchDatabases($var['HistoryDatabase']);
	if (in_array($action, ['Top Mined Sectors', 'Most Dangerous Sectors'])) {
		[$sql, $header] = match ($action) {
			'Top Mined Sectors' => ['mines', 'Mines'],
			'Most Dangerous Sectors' => ['kills', 'Kills'],
		};
		$dbResult = $db->read('SELECT ' . $sql . ' as val, sector_id FROM sector WHERE game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY val DESC LIMIT 25');
		foreach ($dbResult->records() as $dbRecord) {
			$rankings[] = [
				'bold' => '',
				'data' => [
					$dbRecord->getInt('sector_id'),
					$dbRecord->getField('val'),
				],
			];
		}
		$headers = ['Sector', $header];
	} elseif (in_array($action, ['Top Alliance Kills', 'Top Alliance Deaths'])) {
		[$sql, $header] = match ($action) {
			'Top Alliance Kills' => ['kills', 'Kills'],
			'Top Alliance Deaths' => ['deaths', 'Deaths'],
		};
		// Determine which alliance this account was in
		$dbResult = $db->read('SELECT alliance_id FROM player WHERE game_id = ' . $db->escapeNumber($game_id) . ' AND account_id = ' . $db->escapeNumber($oldAccountID));
		$oldAllianceID = $dbResult->hasRecord() ? $dbResult->record()->getInt('alliance_id') : 0;
		// Get the top 25 alliance ordered by the requested stat
		$dbResult = $db->read('SELECT alliance_name, alliance_id, ' . $sql . ' as val FROM alliance WHERE game_id = ' . $db->escapeNumber($game_id) . ' AND alliance_id > 0 GROUP BY alliance_id ORDER BY val DESC, alliance_id LIMIT 25');
		$container = Page::create('history_alliance_detail.php', $var);
		$container['selected_index'] = 1;
		$container['previous_page'] = Page::copy($var);
		foreach ($dbResult->records() as $dbRecord) {
			$allianceID = $dbRecord->getInt('alliance_id');
			$name = htmlentities($dbRecord->getString('alliance_name'));
			$container['alliance_id'] = $allianceID;
			$rankings[] = [
				'bold' => $oldAllianceID == $allianceID ? 'class="bold"' : '',
				'data' => [
					create_link($container, $name),
					$dbRecord->getField('val'),
				],
			];
		}
		$headers = ['Alliance', $header];
	} elseif ($action == 'Top Planets') {
		$dbResult = $db->read('SELECT sector_id, owner_id, IFNULL(player_name, \'Unclaimed\') as player_name, IFNULL(alliance_name, \'None\') as alliance_name, IFNULL(player.alliance_id, 0) as alliance_id, ROUND((turrets + hangers + generators) / 3, 2) as level FROM planet LEFT JOIN player ON planet.owner_id = player.account_id AND planet.game_id = player.game_id LEFT JOIN alliance ON player.alliance_id = alliance.alliance_id AND planet.game_id = alliance.game_id WHERE planet.game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY level DESC LIMIT 25');
		$container = Page::create('history_alliance_detail.php', $var);
		$container['selected_index'] = 1;
		$container['previous_page'] = Page::copy($var);
		foreach ($dbResult->records() as $dbRecord) {
			$ownerID = $dbRecord->getInt('owner_id');
			$allianceID = $dbRecord->getInt('alliance_id');
			$allianceName = $dbRecord->getString('alliance_name');
			if ($allianceID != 0) {
				$container['alliance_id'] = $allianceID;
				$allianceName = create_link($container, $allianceName);
			}
			$rankings[] = [
				'bold' => $ownerID > 0 && $oldAccountID == $ownerID ? 'class="bold"' : '',
				'data' => [
					$dbRecord->getField('level'),
					$dbRecord->getString('player_name'),
					$allianceName,
					$dbRecord->getInt('sector_id'),
				],
			];
		}
		$headers = ['Level', 'Owner', 'Alliance', 'Sector'];
	} else {
		throw new Exception('Unknown action');
	}
	$db->switchDatabaseToLive(); // restore database
	$template->assign('Rankings', $rankings);
	$template->assign('Headers', $headers);
}
