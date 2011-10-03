<?php
require_once(get_file_loc('ChessGame.class.inc'));
if(!is_numeric($_REQUEST['player_id']))
	create_error('You must select a player.');
ChessGame::insertNewGame(TIME, NULL, $player, SmrPlayer::getPlayerByPlayerID($_REQUEST['player_id'],$player->getGameID()));

forward(create_container('skeleton.php','chess.php'));
?>