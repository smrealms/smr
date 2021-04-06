<?php declare(strict_types=1);

$session = Smr\Session::getInstance();

$template->assign('PageTopic', 'Manage NPCs');

$selectedGameID = $session->getRequestVarInt('selected_game_id', 0);

$container = Page::create('skeleton.php', 'npc_manage.php');
$template->assign('SelectGameHREF', $container->href());

$games = [];
$db->query('SELECT game_id FROM game WHERE end_time > ' . $db->escapeNumber(Smr\Epoch::time()) . ' AND enabled = ' . $db->escapeBoolean(true) . ' ORDER BY game_id DESC');
while ($db->nextRecord()) {
	$gameID = $db->getInt('game_id');
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
$db->query('SELECT * FROM npc_logins JOIN account USING(login)');
while ($db->nextRecord()) {
	$accountID = $db->getInt('account_id');
	$login = $db->getField('login');

	$container['login'] = $login;
	$container['accountID'] = $accountID;

	$npcs[$accountID] = [
		'login' => $login,
		'default_player_name' => htmlentities($db->getField('player_name')),
		'default_alliance' => htmlentities($db->getField('alliance_name')),
		'active' => $db->getBoolean('active'),
		'working' => $db->getBoolean('working'),
		'href' => $container->href(),
	];
}

// Set the login name for the next NPC to create
$nextNpcID = count($npcs) + 1;
$template->assign('NextLogin', 'npc' . $nextNpcID);

// Get the existing NPC players for the selected game
$db->query('SELECT * FROM player WHERE game_id=' . $db->escapeNumber($selectedGameID) . ' AND npc=' . $db->escapeBoolean(true));
while ($db->nextRecord()) {
	$accountID = $db->getInt('account_id');
	$npcs[$accountID]['player'] = SmrPlayer::getPlayer($accountID, $selectedGameID, false, $db);
}

$template->assign('Npcs', $npcs);
