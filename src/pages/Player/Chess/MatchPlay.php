<?php declare(strict_types=1);

namespace Smr\Pages\Player\Chess;

use AbstractSmrPlayer;
use Smr\Chess\ChessGame;
use Smr\Page\PlayerPage;
use Smr\Template;
use SmrPlayer;

class MatchPlay extends PlayerPage {

	public string $file = 'chess_play.php';

	public function __construct(
		private readonly int $chessGameID,
		private readonly string $moveMessage = ''
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$chessGame = ChessGame::getChessGame($this->chessGameID);
		$template->assign('ChessGame', $chessGame);

		// Board orientation depends on the player's color.
		$playerIsWhite = $chessGame->getWhiteID() == $player->getAccountID();
		if ($playerIsWhite) {
			$board = $chessGame->getBoard();
		} else {
			$board = $chessGame->getBoardReversed();
		}
		$template->assign('Board', $board);

		// Check if there is a winner
		if ($chessGame->hasEnded()) {
			$winningPlayer = SmrPlayer::getPlayer($chessGame->getWinner(), $player->getGameID());
			$template->assign('Winner', $winningPlayer->getLinkedDisplayName(false));
		}

		// File coordinates depend on the player's color.
		// (So do row coordinates, but these are reversed automatically.)
		$fileCoords = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
		if (!$playerIsWhite) {
			$fileCoords = array_reverse($fileCoords);
		}
		$template->assign('FileCoords', $fileCoords);

		$template->assign('MoveMessage', $this->moveMessage);
		$container = new MovePieceProcessor($this->chessGameID);
		$container->allowAjax = true;
		$template->assign('ChessMoveHREF', $container->href());
	}

}
