<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$chessGames = ChessGame::getOngoingPlayerGames($player);
$template->assign('ChessGames', $chessGames);
$template->assign('PageTopic', 'Casino');

$playersChallenged = array($player->getAccountID() => true);
foreach ($chessGames as $chessGame) {
	$playersChallenged[$chessGame->getWhiteID()] = true;
	$playersChallenged[$chessGame->getBlackID()] = true;
}

$players = array();
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT player_id, player.player_name FROM player JOIN account USING(account_id) WHERE npc = ' . $db->escapeBoolean(false) . ' AND validated = ' . $db->escapeBoolean(true) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id NOT IN (' . $db->escapeArray(array_keys($playersChallenged)) . ') ORDER BY player_name');
foreach ($dbResult->records() as $dbRecord) {
	$players[$dbRecord->getInt('player_id')] = htmlentities($dbRecord->getString('player_name'));
}
$template->assign('PlayerList', $players);

if (ENABLE_NPCS_CHESS) {
	$npcs = array();
	$dbResult = $db->read('SELECT player_id, player.player_name FROM player WHERE npc = ' . $db->escapeBoolean(true) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id NOT IN (' . $db->escapeArray(array_keys($playersChallenged)) . ') ORDER BY player_name');
	foreach ($dbResult->records() as $dbRecord) {
		$npcs[$dbRecord->getInt('player_id')] = htmlentities($dbRecord->getString('player_name'));
	}
	$template->assign('NPCList', $npcs);
}
