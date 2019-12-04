<?php declare(strict_types=1);

if (!is_numeric($_REQUEST['player_id'])) {
	create_error('You must select a player.');
}
ChessGame::insertNewGame(TIME, null, $player, SmrPlayer::getPlayerByPlayerID($_REQUEST['player_id'], $player->getGameID()));

forward(create_container('skeleton.php', 'chess.php'));
