<?php declare(strict_types=1);

Menu::history_games($var['selected_index']);

//offer a back button
$container = $var;
$container['body'] = 'history_games.php';
$template->assign('BackHREF', SmrSession::getNewHREF($container));

$game_id = $var['view_game_id'];
$id = $var['alliance_id'];
$db->switchDatabases($var['HistoryDatabase']);
$db->query('SELECT * FROM alliance WHERE alliance_id = ' . $db->escapeNumber($id) . ' AND game_id = ' . $db->escapeNumber($game_id));
$db->requireRecord();
$template->assign('PageTopic', 'Alliance Roster - ' . htmlentities($db->getField('alliance_name')));

//get alliance members
$oldAccountID = $account->getOldAccountID($var['HistoryDatabase']);
$db->query('SELECT * FROM player WHERE alliance_id = ' . $db->escapeNumber($id) . ' AND game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY experience DESC');
$players = [];
while ($db->nextRecord()) {
	$players[] = [
		'bold' => $db->getInt('account_id') == $oldAccountID ? 'class="bold"' : '',
		'player_name' => $db->getField('player_name'),
		'experience' => number_format($db->getInt('experience')),
		'alignment' => number_format($db->getInt('alignment')),
		'race' => number_format($db->getInt('race')),
		'kills' => number_format($db->getInt('kills')),
		'deaths' => number_format($db->getInt('deaths')),
		'bounty' => number_format($db->getInt('bounty')),
	];
}
$template->assign('Players', $players);

$db->switchDatabaseToLive(); // restore database
