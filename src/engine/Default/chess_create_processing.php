<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$challengePlayer = SmrPlayer::getPlayerByPlayerID(Request::getInt('player_id'), $player->getGameID());
ChessGame::insertNewGame(Smr\Epoch::time(), null, $player, $challengePlayer);

Page::create('skeleton.php', 'chess.php')->go();
