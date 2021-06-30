<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();

$template->assign('PageTopic', 'Manage NPCs');

$selectedGameID = $session->getRequestVarInt('selected_game_id', 0);

$container = Page::create('skeleton.php', 'npc_manage.php');
$template->assign('SelectGameHREF', $container->href());

$games = [];
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT game_id FROM game WHERE end_time > ' . $db->escapeNumber(Smr\Epoch::time()) . ' AND enabled = ' . $db->escapeBoolean(true) . ' ORDER BY game_id DESC');
foreach ($dbResult->records() as $dbRecord) {
	$gameID = $dbRecord->getInt('game_id');
	if (empty($selectedGameID)) {
		$selectedGameID = $gameID;
	}
	$games[] = [
		'Name' => SmrGame::getGame($gameID)->getDisplayName(),
		'ID' => $gameID,
		'Selected' => $gameID == $selectedGameID,
	];
}
$template->assign('Games', $games);
$template->assign('SelectedGameID', $selectedGameID);

$container = Page::create('npc_manage_processing.php');
$container['selected_game_id'] = $selectedGameID;
$template->assign('AddAccountHREF', $container->href());

$npcs = [];
$dbResult = $db->read('SELECT * FROM npc_logins JOIN account USING(login)');
foreach ($dbResult->records() as $dbRecord) {
	$accountID = $dbRecord->getInt('account_id');
	$login = $dbRecord->getField('login');

	$container['login'] = $login;
	$container['accountID'] = $accountID;

	$npcs[$accountID] = [
		'login' => $login,
		'default_player_name' => htmlentities($dbRecord->getString('player_name')),
		'default_alliance' => htmlentities($dbRecord->getString('alliance_name')),
		'active' => $dbRecord->getBoolean('active'),
		'working' => $dbRecord->getBoolean('working'),
		'href' => $container->href(),
	];
}

// Set the login name for the next NPC to create
$nextNpcID = count($npcs) + 1;
$template->assign('NextLogin', 'npc' . $nextNpcID);

// Get the existing NPC players for the selected game
$dbResult = $db->read('SELECT * FROM player WHERE game_id=' . $db->escapeNumber($selectedGameID) . ' AND npc=' . $db->escapeBoolean(true));
foreach ($dbResult->records() as $dbRecord) {
	$accountID = $dbRecord->getInt('account_id');
	$npcs[$accountID]['player'] = SmrPlayer::getPlayer($accountID, $selectedGameID, false, $dbRecord);
}

$template->assign('Npcs', $npcs);
