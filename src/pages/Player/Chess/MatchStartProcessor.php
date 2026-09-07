<?php declare(strict_types=1);

namespace Smr\Pages\Player\Chess;

use Smr\Chess\ChessGame;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class MatchStartProcessor extends PlayerPageProcessor {

	public function build(Player $player): never {
		$challengePlayerNumber = Request::getInt('player_number');
		$challengePlayer = Player::getPlayerByPlayerNumber($challengePlayerNumber, $player->getGameID());
		ChessGame::insertNewGame(Epoch::time(), $player, $challengePlayer);

		new MatchList()->go();
	}

}
