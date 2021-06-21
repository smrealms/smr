<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

Menu::history_games($var['selected_index']);

//offer a back button
$container = Page::copy($var);
$container['body'] = 'history_games.php';
$template->assign('BackHREF', $container->href());

$game_id = $var['view_game_id'];
$id = $var['alliance_id'];

$db = Smr\Database::getInstance();
$db->switchDatabases($var['HistoryDatabase']);
$dbResult = $db->read('SELECT alliance_name FROM alliance WHERE alliance_id = ' . $db->escapeNumber($id) . ' AND game_id = ' . $db->escapeNumber($game_id));
$template->assign('PageTopic', 'Alliance Roster - ' . htmlentities($dbResult->record()->getField('alliance_name')));

//get alliance members
$oldAccountID = $account->getOldAccountID($var['HistoryDatabase']);
$dbResult = $db->read('SELECT * FROM player WHERE alliance_id = ' . $db->escapeNumber($id) . ' AND game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY experience DESC');
$players = [];
foreach ($dbResult->records() as $dbRecord) {
	$players[] = [
		'bold' => $dbRecord->getInt('account_id') == $oldAccountID ? 'class="bold"' : '',
		'player_name' => $dbRecord->getField('player_name'),
		'experience' => number_format($dbRecord->getInt('experience')),
		'alignment' => number_format($dbRecord->getInt('alignment')),
		'race' => number_format($dbRecord->getInt('race')),
		'kills' => number_format($dbRecord->getInt('kills')),
		'deaths' => number_format($dbRecord->getInt('deaths')),
		'bounty' => number_format($dbRecord->getInt('bounty')),
	];
}
$template->assign('Players', $players);

$db->switchDatabaseToLive(); // restore database
