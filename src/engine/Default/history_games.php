<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

//topic
if (!isset($var['game_name']) || !isset($var['view_game_id'])) {
	create_error('No game specified!');
}
$game_name = $var['game_name'];
$game_id = $var['view_game_id'];
$template->assign('PageTopic', 'Old SMR Game : ' . $game_name);
Menu::historyGames(0);

$db = Smr\Database::getInstance();
$db->switchDatabases($var['HistoryDatabase']);
$dbResult = $db->read('SELECT start_date, type, end_date, game_name, speed, game_id ' .
	'FROM game WHERE game_id = ' . $db->escapeNumber($game_id));
$dbRecord = $dbResult->record();
$template->assign('GameName', $game_name);
$template->assign('Start', date($account->getDateFormat(), $dbRecord->getInt('start_date')));
$template->assign('End', date($account->getDateFormat(), $dbRecord->getInt('end_date')));
$template->assign('Type', $dbRecord->getString('type'));
$template->assign('Speed', $dbRecord->getFloat('speed'));

$dbResult = $db->read('SELECT count(*), max(experience), max(alignment), min(alignment), max(kills) FROM player WHERE game_id = ' . $db->escapeNumber($game_id));
if ($dbResult->hasRecord()) {
	$dbRecord = $dbResult->record();
	$template->assign('NumPlayers', $dbRecord->getInt('count(*)'));
	$template->assign('MaxExp', $dbRecord->getInt('max(experience)'));
	$template->assign('MaxAlign', $dbRecord->getInt('max(alignment)'));
	$template->assign('MinAlign', $dbRecord->getInt('min(alignment)'));
	$template->assign('MaxKills', $dbRecord->getInt('max(kills)'));
}
$dbResult = $db->read('SELECT count(*) FROM alliance WHERE game_id = ' . $db->escapeNumber($game_id));
$template->assign('NumAlliances', $dbResult->record()->getInt('count(*)'));

// Get linked player information, if available
$oldAccountID = $account->getOldAccountID($var['HistoryDatabase']);
$dbResult = $db->read('SELECT alliance_id FROM player WHERE game_id = ' . $db->escapeNumber($game_id) . ' AND account_id = ' . $db->escapeNumber($oldAccountID));
$oldAllianceID = $dbResult->hasRecord() ? $dbResult->record()->getInt('alliance_id') : 0;

$playerExp = [];
$dbResult = $db->read('SELECT * FROM player WHERE game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY experience DESC LIMIT 10');
foreach ($dbResult->records() as $dbRecord) {
	$playerExp[] = [
		'bold' => $dbRecord->getInt('account_id') == $oldAccountID ? 'class="bold"' : '',
		'exp' => $dbRecord->getInt('experience'),
		'name' => $dbRecord->getString('player_name'),
	];
}
$template->assign('PlayerExp', $playerExp);

$playerKills = [];
$dbResult = $db->read('SELECT * FROM player WHERE game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY kills DESC LIMIT 10');
foreach ($dbResult->records() as $dbRecord) {
	$playerKills[] = [
		'bold' => $dbRecord->getInt('account_id') == $oldAccountID ? 'class="bold"' : '',
		'kills' => $dbRecord->getInt('kills'),
		'name' => $dbRecord->getString('player_name'),
	];
}
$template->assign('PlayerKills', $playerKills);

$container = Page::create('history_alliance_detail.php', $var);
$container['selected_index'] = 0;

//now for the alliance stuff
$allianceExp = [];
$dbResult = $db->read('SELECT SUM(experience) as exp, alliance_name, alliance_id
			FROM player JOIN alliance USING (game_id, alliance_id)
			WHERE game_id = ' . $db->escapeNumber($game_id) . ' GROUP BY alliance_id ORDER BY exp DESC LIMIT 10');
foreach ($dbResult->records() as $dbRecord) {
	$alliance = htmlentities($dbRecord->getString('alliance_name'));
	$id = $dbRecord->getInt('alliance_id');
	$container['alliance_id'] = $id;
	$allianceExp[] = [
		'bold' => $dbRecord->getInt('alliance_id') == $oldAllianceID ? 'class="bold"' : '',
		'exp' => $dbRecord->getInt('exp'),
		'link' => create_link($container, $alliance),
	];
}
$template->assign('AllianceExp', $allianceExp);

$allianceKills = [];
$dbResult = $db->read('SELECT kills, alliance_name, alliance_id FROM alliance WHERE game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY kills DESC LIMIT 10');
foreach ($dbResult->records() as $dbRecord) {
	$alliance = htmlentities($dbRecord->getString('alliance_name'));
	$id = $dbRecord->getInt('alliance_id');
	$container['alliance_id'] = $id;
	$allianceKills[] = [
		'bold' => $dbRecord->getInt('alliance_id') == $oldAllianceID ? 'class="bold"' : '',
		'kills' => $dbRecord->getInt('kills'),
		'link' => create_link($container, $alliance),
	];
}
$template->assign('AllianceKills', $allianceKills);

$db->switchDatabaseToLive(); // restore database
