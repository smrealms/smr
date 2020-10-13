<?php declare(strict_types=1);

$container = create_container('skeleton.php', 'chess_play.php');
transfer('ChessGameID');

$chessGame = ChessGame::getChessGame($var['ChessGameID']);
$x = Request::getInt('x');
$y = Request::getInt('y');
$toX = Request::getInt('toX');
$toY = Request::getInt('toY');
if (!$chessGame->hasEnded()) {
	if ($chessGame->isCurrentTurn($player->getPlayerID())) {
		$board = $chessGame->getBoard();
		if ($board[$y][$x] != null) {
			switch ($chessGame->tryMove($x, $y, $toX, $toY, $player->getPlayerID(), ChessPiece::QUEEN)) {
				case 0:
					//Success
				break;
				case 1:
					$container['MoveMessage'] = 'You have just checkmated your opponent, congratulations!';
				break;
				case 2:
					$container['MoveMessage'] = 'There is no piece in that square.';
				break;
				case 3:
					$container['MoveMessage'] = 'You cannot end your turn in check.';
				break;
				case 4:
					$container['MoveMessage'] = 'It is not your turn to move.';
				break;
				case 5:
					$container['MoveMessage'] = 'The game is over.';
				break;
			}
		}
	} else {
		$container['MoveMessage'] = 'It is not your turn to move.';
	}
} else {
	$container['MoveMessage'] = 'This game is over.';
}

forward($container);
