<?php declare(strict_types=1);

use Smr\Chess\ChessGame;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$chessGame = ChessGame::getChessGame($var['ChessGameID']);
$result = $chessGame->resign($player->getAccountID());

$container = Page::create('current_sector.php');

$container['msg'] = match ($result) {
	ChessGame::END_RESIGN => '[color=green]Success:[/color] You have resigned from [chess=' . $var['ChessGameID'] . '].',
	ChessGame::END_CANCEL => '[color=green]Success:[/color] [chess=' . $var['ChessGameID'] . '] has been cancelled.',
};

$container->go();
