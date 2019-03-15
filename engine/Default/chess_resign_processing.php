<?php

$chessGame = ChessGame::getChessGame($var['ChessGameID']);
$result = $chessGame->resign($player->getAccountID());

$container = create_container('skeleton.php', 'current_sector.php');

switch($result) {
	case 0:
		$container['msg'] = '[color=green]Success:[/color] You have resigned from [chess=' . $var['ChessGameID'] . '].';
	break;
	case 1:
		$container['msg'] = '[color=green]Success:[/color] [chess=' . $var['ChessGameID'] . '] has been cancelled.';
	break;
}

forward($container);
