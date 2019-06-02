<?php

$container = create_container('skeleton.php', 'chess_play.php');
transfer('ChessGameID');

$chessGame = ChessGame::getChessGame($var['ChessGameID']);
if (is_numeric($_REQUEST['x']) && is_numeric($_REQUEST['y']) && is_numeric($_REQUEST['toX']) && is_numeric($_REQUEST['toY'])) {
	$x = $_REQUEST['x'];
	$y = $_REQUEST['y'];
	$toX = $_REQUEST['toX'];
	$toY = $_REQUEST['toY'];
	if (!$chessGame->hasEnded()) {
		if ($chessGame->isCurrentTurn($account->getAccountID())) {
			$board = $chessGame->getBoard();
			if ($board[$y][$x] != null) {
				switch ($chessGame->tryMove($x, $y, $toX, $toY, $account->getAccountID(), ChessPiece::QUEEN)) {
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
}

forward($container);
