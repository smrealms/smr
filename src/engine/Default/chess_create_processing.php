<?php declare(strict_types=1);

$challengePlayer = SmrPlayer::getPlayerByPlayerID(Request::getInt('player_id'), $player->getGameID());
ChessGame::insertNewGame(Smr\Epoch::time(), null, $player, $challengePlayer);

Page::create('skeleton.php', 'chess.php')->go();
