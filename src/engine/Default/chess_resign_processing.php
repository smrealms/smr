<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$chessGame = ChessGame::getChessGame($var['ChessGameID']);
$result = $chessGame->resign($player->getAccountID());

$container = Page::create('skeleton.php', 'current_sector.php');

$container['msg'] = match($result) {
	0 => '[color=green]Success:[/color] You have resigned from [chess=' . $var['ChessGameID'] . '].',
	1 => '[color=green]Success:[/color] [chess=' . $var['ChessGameID'] . '] has been cancelled.',
};

$container->go();
