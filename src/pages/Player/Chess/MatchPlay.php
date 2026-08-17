<?php declare(strict_types=1);

namespace Smr\Pages\Player\Chess;

use Smr\Chess\ChessGame;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class MatchPlay extends PlayerPage {

	public function __construct(
		private readonly int $chessGameID,
		private readonly string $moveMessage = '',
	) {}

	public function build(Player $player, Template $template): void {
		$chessGame = ChessGame::getChessGame($this->chessGameID);

		$topic = $chessGame->getWhitePlayer()->getPlayerName() . ' vs. ' . $chessGame->getBlackPlayer()->getPlayerName();
		$template->pageTopic = htmlentities($topic);

		// Board orientation depends on the player's color.
		$playerIsWhite = $chessGame->getWhiteID() === $player->getAccountID();
		$board = $chessGame->getBoard()->getBoardDisplay($playerIsWhite);

		// Check if there is a winner
		if ($chessGame->hasWinner()) {
			$winningPlayer = Player::getPlayer($chessGame->getWinner(), $player->getGameID());
			$winner = $winningPlayer->getLinkedDisplayName(false);
		} else {
			$winner = null;
		}

		// File coordinates depend on the player's color.
		// (So do row coordinates, but these are reversed automatically.)
		$fileCoords = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
		if (!$playerIsWhite) {
			$fileCoords = array_reverse($fileCoords);
		}

		$container = new MovePieceProcessor($this->chessGameID);
		$container->allowAjax = true;

		$template->pageRenderer = fn() => MatchPlayRenderer::render(
			template: $template,
			ChessGame: $chessGame,
			Board: $board,
			Ended: $chessGame->hasEnded(),
			Winner: $winner,
			FileCoords: $fileCoords,
			MoveMessage: $this->moveMessage,
			ChessMoveHREF: $container->href(),
			ThisAccount: $player->getAccount(),
			ThisPlayer: $player,
		);
	}

}
