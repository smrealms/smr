<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$challengePlayer = SmrPlayer::getPlayerByPlayerID(Smr\Request::getInt('player_id'), $player->getGameID());
Smr\Chess\ChessGame::insertNewGame(Smr\Epoch::time(), null, $player, $challengePlayer);

Page::create('chess.php')->go();
