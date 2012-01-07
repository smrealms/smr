<?php
require_once('../htdocs/config.inc');
require_once(LIB . 'Default/Globals.class.inc');

require_once(get_file_loc('ChessGame.class.inc'));

SmrSession::$game_id = 2;

$db = new SmrMySqlDatabase();
$db->query('DELETE FROM player_hof WHERE type LIKE \'Chess%\'');
$db->query('SELECT chess_game_id FROM chess_game');
while($db->nextRecord()) {
	$game = new ChessGame($db->getInt('chess_game_id'));
	$game->rerunGame();
}

?>
