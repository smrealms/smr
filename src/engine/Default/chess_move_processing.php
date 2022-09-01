<?php declare(strict_types=1);

use Smr\Chess\ChessGame;
use Smr\Chess\ChessPiece;
use Smr\Exceptions\UserError;
use Smr\Request;

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$container = Page::create('chess_play.php');
$container->addVar('ChessGameID');

$chessGame = ChessGame::getChessGame($var['ChessGameID']);
$x = Request::getInt('x');
$y = Request::getInt('y');
$toX = Request::getInt('toX');
$toY = Request::getInt('toY');
$colour = $chessGame->getColourForAccountID($player->getAccountID());
try {
	$message = $chessGame->tryMove($x, $y, $toX, $toY, $colour, ChessPiece::QUEEN);
} catch (UserError $err) {
	$message = $err->getMessage();
}
$container['MoveMessage'] = $message;

$container->go();
