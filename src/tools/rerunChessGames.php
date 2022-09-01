<?php declare(strict_types=1);

use Smr\Chess\ChessGame;
use Smr\Database;
use Smr\Session;

require_once('../bootstrap.php');

Session::getInstance()->updateGame(44);

$db = Database::getInstance();
$db->write('DELETE FROM player_hof WHERE type LIKE \'Chess%\'');
$dbResult = $db->read('SELECT chess_game_id FROM chess_game');
foreach ($dbResult->records() as $dbRecord) {
	$chessGameID = $dbRecord->getInt('chess_game_id');
	$game = ChessGame::getChessGame($chessGameID);
	echo 'Running game ' . $chessGameID . ' for white id "' . $game->getWhiteID() . '", black id "' . $game->getBlackID() . '", winner "' . $game->getWinner() . '"' . EOL;
	echoChessMoves($game);

	$game->rerunGame(true);
	echo 'Finished game ' . $chessGameID . ' for white id "' . $game->getWhiteID() . '", black id "' . $game->getBlackID() . '", winner "' . $game->getWinner() . '"' . EOL;
	echoChessMoves($game);
}

function echoChessMoves(ChessGame $game): void {
	echo 'Moves: ' . EOL;
	$moves = $game->getMoves();
	foreach ($moves as $move) {
		echo $move . EOL;
	}
	echo EOL;
}
