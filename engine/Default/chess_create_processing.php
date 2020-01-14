<?php declare(strict_types=1);

$challengePlayer = SmrPlayer::getPlayerByPlayerID(Request::getInt('player_id'), $player->getGameID());
ChessGame::insertNewGame(TIME, null, $player, $challengePlayer);

forward(create_container('skeleton.php', 'chess.php'));
