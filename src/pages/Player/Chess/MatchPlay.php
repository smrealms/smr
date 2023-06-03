<?php declare(strict_types=1);

namespace Smr\Pages\Player\Chess;

use Smr\AbstractPlayer;
use Smr\Chess\ChessGame;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class MatchPlay extends PlayerPage {

	public string $file = 'chess_play.php';

	public function __construct(
		private readonly int $chessGameID,
		private readonly string $moveMessage = '',
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$chessGame = ChessGame::getChessGame($this->chessGameID);
		$template->assign('ChessGame', $chessGame);

		$topic = $chessGame->getWhitePlayer()->getPlayerName() . ' vs. ' . $chessGame->getBlackPlayer()->getPlayerName();
		$template->assign('PageTopic', htmlentities($topic));

		// Board orientation depends on the player's color.
		$playerIsWhite = $chessGame->getWhiteID() === $player->getAccountID();
		$board = $chessGame->getBoard()->getBoardDisplay($playerIsWhite);
		$template->assign('Board', $board);

		// Check if there is a winner
		if ($chessGame->hasWinner()) {
			$winningPlayer = Player::getPlayer($chessGame->getWinner(), $player->getGameID());
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
