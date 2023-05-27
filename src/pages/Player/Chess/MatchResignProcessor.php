<?php declare(strict_types=1);

namespace Smr\Pages\Player\Chess;

use Smr\AbstractPlayer;
use Smr\Chess\ChessGame;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;

class MatchResignProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $chessGameID,
	) {}

	public function build(AbstractPlayer $player): never {
		$chessGame = ChessGame::getChessGame($this->chessGameID);
		$result = $chessGame->resign($player->getAccountID());

		$msg = match ($result) {
			ChessGame::END_RESIGN => '[color=green]Success:[/color] You have resigned from [chess=' . $this->chessGameID . '].',
			ChessGame::END_CANCEL => '[color=green]Success:[/color] [chess=' . $this->chessGameID . '] has been cancelled.',
		};

		$container = new CurrentSector(message: $msg);
		$container->go();
	}

}
