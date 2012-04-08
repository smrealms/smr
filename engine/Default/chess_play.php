<?php
require_once(get_file_loc('ChessGame.class.inc'));
$template->assignByRef('ChessGame',ChessGame::getChessGame($var['ChessGameID']));
$template->assign('ChessMoveHREF',SmrSession::getNewHREF(create_container('chess_move_processing.php','',array('AJAX' => true, 'ChessGameID' => $var['ChessGameID']))));
?>