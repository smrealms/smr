<?php declare(strict_types=1);

namespace Smr\Pages\Player\Chess;

use Smr\AbstractPlayer;
use Smr\Chess\ChessGame;
use Smr\Chess\ChessPiece;
use Smr\Chess\Loc;
use Smr\Exceptions\UserError;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class MovePieceProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $chessGameID,
	) {}

	public function build(AbstractPlayer $player): never {
		$chessGame = ChessGame::getChessGame($this->chessGameID);
		$x = Request::getInt('x');
		$y = Request::getInt('y');
		$toX = Request::getInt('toX');
		$toY = Request::getInt('toY');
		$loc = Loc::validate($x, $y);
		$toLoc = Loc::validate($toX, $toY);
		$colour = $chessGame->getColourForAccountID($player->getAccountID());
		try {
			$message = $chessGame->tryMove($loc, $toLoc, $colour, ChessPiece::QUEEN);
		} catch (UserError $err) {
			$message = $err->getMessage();
		}

		$container = new MatchPlay($this->chessGameID, $message);
		$container->go();
	}

}
