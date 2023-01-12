<?php declare(strict_types=1);

namespace Smr\Pages\Player\Chess;

use Smr\AbstractPlayer;
use Smr\Chess\ChessGame;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class MatchStartProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		$challengePlayer = Player::getPlayerByPlayerID(Request::getInt('player_id'), $player->getGameID());
		ChessGame::insertNewGame(Epoch::time(), null, $player, $challengePlayer);

		(new MatchList())->go();
	}

}
