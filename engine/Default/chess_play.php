<?php
require_once(get_file_loc('ChessGame.class.inc'));
$template->assignByRef('ChessGame',ChessGame::getChessGame);
$template->assign('ChessMoveHREF',SmrSession::get_new_href(create_container('chess_move_processing.php','',array('AJAX' => true, 'ChessGameID' => $var['ChessGameID']))));
?>