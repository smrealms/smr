<?php
require_once('../htdocs/config.inc');
require_once(LIB . 'Default/Globals.class.inc');

require_once(get_file_loc('ChessGame.class.inc'));

SmrSession::$game_id = 44;

$db = new SmrMySqlDatabase();
$db->query('DELETE FROM player_hof WHERE type LIKE \'Chess%\'');
$db->query('SELECT chess_game_id FROM chess_game');
while($db->nextRecord()) {
	$chessGameID = $db->getInt('chess_game_id');
	$game = new ChessGame($chessGameID);
	echo 'Running game ' . $chessGameID . ' for white id "' . $game->getWhiteID() . '", black id "' . $game->getBlackID() .'", winner "' . $game->getWinner() . '"' . EOL;
	echo 'Moves: ' . $game->getMoves() . EOL . EOL;
	$game->rerunGame(true);
	echo 'Finished game ' . $chessGameID . ' for white id "' . $game->getWhiteID() . '", black id "' . $game->getBlackID() .'", winner "' . $game->getWinner() . '"' . EOL;
	echo 'Moves: ' . $game->getMoves() . EOL . EOL;
}

?>
