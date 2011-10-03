<?php
require_once(get_file_loc('ChessGame.class.inc'));
$template->assign('ChessGame',new ChessGame($var['ChessGameID']));
$template->assign('ChessMoveHREF',SmrSession::get_new_href(create_container('chess_move_processing.php','chess_move.php',array('AJAX' => true, 'ChessGameID' => $var['ChessGameID']))));
?>