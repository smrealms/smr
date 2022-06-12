<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$container = Page::create('chess_play.php');
$container->addVar('ChessGameID');

$chessGame = Smr\Chess\ChessGame::getChessGame($var['ChessGameID']);
$x = Smr\Request::getInt('x');
$y = Smr\Request::getInt('y');
$toX = Smr\Request::getInt('toX');
$toY = Smr\Request::getInt('toY');
$colour = $chessGame->getColourForAccountID($player->getAccountID());
try {
	$message = $chessGame->tryMove($x, $y, $toX, $toY, $colour, Smr\Chess\ChessPiece::QUEEN);
} catch (Smr\Exceptions\UserError $err) {
	$message = $err->getMessage();
}
$container['MoveMessage'] = $message;

$container->go();
