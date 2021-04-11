<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$chessGame = ChessGame::getChessGame($var['ChessGameID']);
$template->assign('ChessGame', $chessGame);

// Board orientation depends on the player's color.
$playerIsWhite = $chessGame->getWhiteID() == $player->getAccountID();
if ($playerIsWhite) {
	$board = $chessGame->getBoard();
} else {
	$board = $chessGame->getBoardReversed();
}
$template->assign('Board', $board);

// File coordinates depend on the player's color.
// (So do row coordinates, but these are reversed automatically.)
$fileCoords = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
if (!$playerIsWhite) {
	$fileCoords = array_reverse($fileCoords);
}
$template->assign('FileCoords', $fileCoords);

$template->assign('MoveMessage', $var['MoveMessage'] ?? '');
$template->assign('ChessMoveHREF', Page::create('chess_move_processing.php', '', array('AJAX' => true, 'ChessGameID' => $var['ChessGameID']))->href());
