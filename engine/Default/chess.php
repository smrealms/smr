<?php
require_once(get_file_loc('ChessGame.class.inc'));
//$chessGame = new ChessGame(0);
//$template->assignByRef('Board',$chessGame->getBoard());

$template->assign('ChessGames', ChessGame::getAccountGames($player->getAccountID()));

$db->query('SELECT player_id, player.player_name FROM player JOIN account USING(account_id) LEFT OUTER JOIN npc_logins USING(login) WHERE working IS NULL AND validated = ' . $db->escapeBoolean(true) . ' AND game_id = ' . $db->escapeString($player->getGameID()) . ' AND account_id != ' . $db->escapeNumber($player->getAccountID()) . ' ORDER BY player_name');
while ($db->nextRecord())
{
	$players[$db->getField('player_id')] = $db->getField('player_name');
}
$template->assignByRef('PlayerList',$players);

?>