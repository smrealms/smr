<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$container = Page::create('skeleton.php', 'chess_play.php');
$container->addVar('ChessGameID');

$chessGame = ChessGame::getChessGame($var['ChessGameID']);
$x = Request::getInt('x');
$y = Request::getInt('y');
$toX = Request::getInt('toX');
$toY = Request::getInt('toY');
if (!$chessGame->hasEnded()) {
	if ($chessGame->isCurrentTurn($player->getAccountID())) {
		$board = $chessGame->getBoard();
		if ($board[$y][$x] != null) {
			$result = $chessGame->tryMove($x, $y, $toX, $toY, $player->getAccountID(), ChessPiece::QUEEN);
			$container['MoveMessage'] = match($result) {
				0 => '', // valid move, no message
				1 => 'You have just checkmated your opponent, congratulations!',
				2 => 'There is no piece in that square.',
				3 => 'You cannot end your turn in check.',
				4 => 'It is not your turn to move.',
				5 => 'The game is over.',
				6 => 'That is not a valid move!',
			};
		}
	} else {
		$container['MoveMessage'] = 'It is not your turn to move.';
	}
} else {
	$container['MoveMessage'] = 'This game is over.';
}

$container->go();
