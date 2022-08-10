<?php declare(strict_types=1);

use Smr\Chess\ChessGame;
use Smr\Epoch;
use Smr\Request;

		$session = Smr\Session::getInstance();
		$player = $session->getPlayer();

		$challengePlayer = SmrPlayer::getPlayerByPlayerID(Request::getInt('player_id'), $player->getGameID());
		ChessGame::insertNewGame(Epoch::time(), null, $player, $challengePlayer);

		Page::create('chess.php')->go();
