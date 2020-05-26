<?php declare(strict_types=1);

//topic
if (!isset($var['game_name']) || !isset($var['view_game_id'])) {
	create_error('No game specified!');
}
$game_name = $var['game_name'];
$game_id = $var['view_game_id'];
$template->assign('PageTopic', 'Old SMR Game : ' . $game_name);
Menu::history_games(0);

$db = new $var['HistoryDatabase']();
$db->query('SELECT start_date, type, end_date, game_name, speed, game_id ' .
           'FROM game WHERE game_id = ' . $db->escapeNumber($game_id));
$db->requireRecord();
$template->assign('GameName', $game_name);
$template->assign('Start', date(DATE_DATE_SHORT, $db->getInt('start_date')));
$template->assign('End', date(DATE_DATE_SHORT, $db->getInt('end_date')));
$template->assign('Type', $db->getField('type'));
$template->assign('Speed', $db->getFloat('speed'));

$db->query('SELECT count(*), max(experience), max(alignment), min(alignment), max(kills) FROM player WHERE game_id = ' . $db->escapeNumber($game_id));
if ($db->nextRecord()) {
	$template->assign('NumPlayers', $db->getInt('count(*)'));
	$template->assign('MaxExp', $db->getInt('max(experience)'));
	$template->assign('MaxAlign', $db->getInt('max(alignment)'));
	$template->assign('MinAlign', $db->getInt('min(alignment)'));
	$template->assign('MaxKills', $db->getInt('max(kills)'));
}
$db->query('SELECT count(*) FROM alliance WHERE game_id = ' . $db->escapeNumber($game_id));
$db->requireRecord();
$template->assign('NumAlliances', $db->getInt('count(*)'));

$playerExp = [];
$db->query('SELECT * FROM player WHERE game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY experience DESC LIMIT 10');
while ($db->nextRecord()) {
	$playerExp[] = [
		'exp' => $db->getInt('experience'),
		'name' => stripslashes($db->getField('player_name')),
	];
}
$template->assign('PlayerExp', $playerExp);

$playerKills = [];
$db->query('SELECT * FROM player WHERE game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY kills DESC LIMIT 10');
while ($db->nextRecord()) {
	$playerKills[] = [
		'kills' => $db->getInt('kills'),
		'name' => stripslashes($db->getField('player_name')),
	];
}
$template->assign('PlayerKills', $playerKills);

$container = $var;
$container['body'] = 'history_alliance_detail.php';
$container['selected_index'] = 0;

//now for the alliance stuff
$allianceExp = [];
$db->query('SELECT SUM(experience) as exp, alliance_name, alliance_id
			FROM player JOIN alliance USING (game_id, alliance_id)
			WHERE game_id = '.$db->escapeNumber($game_id) . ' GROUP BY alliance_id ORDER BY exp DESC LIMIT 10');
while ($db->nextRecord()) {
	$alliance = htmlentities($db->getField('alliance_name'));
	$id = $db->getInt('alliance_id');
	$container['alliance_id'] = $id;
	$allianceExp[] = [
		'exp' => $db->getInt('exp'),
		'link' => create_link($container, $alliance),
	];
}
$template->assign('AllianceExp', $allianceExp);

$allianceKills = [];
$db->query('SELECT kills, alliance_name, alliance_id FROM alliance WHERE game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY kills DESC LIMIT 10');
while ($db->nextRecord()) {
	$alliance = htmlentities($db->getField('alliance_name'));
	$id = $db->getInt('alliance_id');
	$container['alliance_id'] = $id;
	$allianceKills[] = [
		'kills' => $db->getInt('kills'),
		'link' => create_link($container, $alliance),
	];
}
$template->assign('AllianceKills', $allianceKills);

//to stop errors on the following scripts
$db = new SmrMySqlDatabase();
