<?php declare(strict_types=1);

namespace Smr\Pages\Player\Chess;

use AbstractSmrPlayer;
use Smr\Chess\ChessGame;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;
use SmrPlayer;

class MatchStartProcessor extends PlayerPageProcessor {

	public function build(AbstractSmrPlayer $player): never {
		$challengePlayer = SmrPlayer::getPlayerByPlayerID(Request::getInt('player_id'), $player->getGameID());
		ChessGame::insertNewGame(Epoch::time(), null, $player, $challengePlayer);

		(new MatchList())->go();
	}

}
