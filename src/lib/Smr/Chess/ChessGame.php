<?php declare(strict_types=1);

namespace Smr\Chess;

use Exception;
use Smr\AbstractPlayer;
use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Exceptions\UserError;
use Smr\Pages\Player\Chess\MatchPlay;
use Smr\Pages\Player\Chess\MatchResignProcessor;
use Smr\Player;

class ChessGame {

	public const int END_RESIGN = 0;
	public const int END_CANCEL = 1;

	/** @var array<int, self> */
	protected static array $CACHE_CHESS_GAMES = [];

	private readonly int $whiteID;
	private readonly int $blackID;
	private readonly int $gameID;
	private readonly int $startDate;
	private ?int $endDate;
	private int $winner;

	private Board $board;
	/** @var array<string> */
	private array $moves;

	/** @var ?array{From: Loc, To: Loc} */
	private ?array $lastMove = null;

	/**
	 * @return array<self>
	 */
	public static function getNPCMoveGames(bool $forceUpdate = false): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT chess_game_id
					FROM npc_logins
					JOIN account USING(login)
					JOIN chess_game ON account_id = black_id OR account_id = white_id
					WHERE end_time > :now OR end_time IS NULL', [
			'now' => Epoch::time(),
		]);
		$games = [];
		foreach ($dbResult->records() as $dbRecord) {
			$game = self::getChessGame($dbRecord->getInt('chess_game_id'), $forceUpdate);
			if ($game->getCurrentTurnAccount()->isNPC()) {
				$games[] = $game;
			}
		}
		return $games;
	}

	/**
	 * @return array<self>
	 */
	public static function getOngoingPlayerGames(AbstractPlayer $player): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT chess_game_id FROM chess_game WHERE game_id = :game_id AND (black_id = :account_id OR white_id = :account_id) AND (end_time > :now OR end_time IS NULL)', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'account_id' => $db->escapeNumber($player->getAccountID()),
			'now' => Epoch::time(),
		]);
		$games = [];
		foreach ($dbResult->records() as $dbRecord) {
			$games[] = self::getChessGame($dbRecord->getInt('chess_game_id'));
		}
		return $games;
	}

	public static function getChessGame(int $chessGameID, bool $forceUpdate = false): self {
		if ($forceUpdate || !isset(self::$CACHE_CHESS_GAMES[$chessGameID])) {
			self::$CACHE_CHESS_GAMES[$chessGameID] = new self($chessGameID);
		}
		return self::$CACHE_CHESS_GAMES[$chessGameID];
	}

	public function __construct(private readonly int $chessGameID) {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT *
						FROM chess_game
						WHERE chess_game_id = :chess_game_id', [
			'chess_game_id' => $db->escapeNumber($chessGameID),
		]);
		if (!$dbResult->hasRecord()) {
			throw new Exception('Chess game not found: ' . $chessGameID);
		}
		$dbRecord = $dbResult->record();
		$this->gameID = $dbRecord->getInt('game_id');
		$this->startDate = $dbRecord->getInt('start_time');
		$this->endDate = $dbRecord->getNullableInt('end_time');
		$this->whiteID = $dbRecord->getInt('white_id');
		$this->blackID = $dbRecord->getInt('black_id');
		$this->winner = $dbRecord->getInt('winner_id');
	}

	public function getBoard(): Board {
		if (!isset($this->board)) {
			$this->getMoves();
		}
		return $this->board;
	}

	/**
	 * @return ?array{From: Loc, To: Loc}
	 */
	public function getLastMove(): ?array {
		$this->getMoves();
		return $this->lastMove;
	}

	/**
	 * Determines if a board square is part of the last move
	 * (returns true for both the 'To' and 'From' squares).
	 */
	public function isLastMoveSquare(Loc $loc): bool {
		$lastMove = $this->getLastMove();
		if ($lastMove === null) {
			return false;
		}
		return $loc->same($lastMove['From']) || $loc->same($lastMove['To']);
	}

	/**
	 * @return array<string>
	 */
	public function getMoves(): array {
		if (!isset($this->moves)) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM chess_game_moves WHERE chess_game_id = :chess_game_id ORDER BY move_id', [
				'chess_game_id' => $db->escapeNumber($this->chessGameID),
			]);
			$this->moves = [];
			$this->board = new Board();
			$mate = false;
			$draw = false;
			foreach ($dbResult->records() as $dbRecord) {
				$forColour = $this->board->getCurrentTurnColour();
				$promotionPieceID = $dbRecord->getNullableInt('promote_piece_id');
				$startLoc = Loc::validate(
					x: $dbRecord->getInt('start_x'),
					y: $dbRecord->getInt('start_y'),
				);
				$endLoc = Loc::validate(
					x: $dbRecord->getInt('end_x'),
					y: $dbRecord->getInt('end_y'),
				);
				// Update the board state
				$moveInfo = $this->board->movePiece(
					loc: $startLoc,
					toLoc: $endLoc,
					pawnPromotionPiece: $promotionPieceID ?? ChessPiece::QUEEN,
				);
				$draw = $this->board->isDraw($forColour->opposite());
				$this->moves[] = $this->createMove(
					pieceID: $dbRecord->getInt('piece_id'),
					startLoc: $startLoc,
					endLoc: $endLoc,
					pieceTaken: $moveInfo['PieceTaken']?->pieceID,
					checking: $dbRecord->getNullableString('checked'),
					playerColour: $forColour,
					castling: $moveInfo['Castling'],
					enPassant: $moveInfo['EnPassant'],
					promotionPieceID: $promotionPieceID,
					draw: $draw,
				);
				$mate = $dbRecord->getNullableString('checked') === 'MATE';
			}
			if (!$mate && $this->hasEnded()) {
				if ($this->hasWinner()) {
					$this->moves[] = ($this->getWinner() === $this->getWhiteID() ? 'Black' : 'White') . ' Resigned.';
				} elseif (count($this->moves) < 2) {
					$this->moves[] = 'Game Cancelled.';
				} elseif ($draw) {
					$this->moves[] = 'Game Drawn.';
				} else {
					throw new Exception('Game end state unknown!');
				}
			}
		}
		return $this->moves;
	}

	/**
	 * @return array<ChessPiece>
	 */
	public static function getStandardGame(): array {
		return [
			new ChessPiece(Colour::White, ChessPiece::ROOK, new Loc(0, 0)),
			new ChessPiece(Colour::White, ChessPiece::KNIGHT, new Loc(1, 0)),
			new ChessPiece(Colour::White, ChessPiece::BISHOP, new Loc(2, 0)),
			new ChessPiece(Colour::White, ChessPiece::QUEEN, new Loc(3, 0)),
			new ChessPiece(Colour::White, ChessPiece::KING, new Loc(4, 0)),
			new ChessPiece(Colour::White, ChessPiece::BISHOP, new Loc(5, 0)),
			new ChessPiece(Colour::White, ChessPiece::KNIGHT, new Loc(6, 0)),
			new ChessPiece(Colour::White, ChessPiece::ROOK, new Loc(7, 0)),

			new ChessPiece(Colour::White, ChessPiece::PAWN, new Loc(0, 1)),
			new ChessPiece(Colour::White, ChessPiece::PAWN, new Loc(1, 1)),
			new ChessPiece(Colour::White, ChessPiece::PAWN, new Loc(2, 1)),
			new ChessPiece(Colour::White, ChessPiece::PAWN, new Loc(3, 1)),
			new ChessPiece(Colour::White, ChessPiece::PAWN, new Loc(4, 1)),
			new ChessPiece(Colour::White, ChessPiece::PAWN, new Loc(5, 1)),
			new ChessPiece(Colour::White, ChessPiece::PAWN, new Loc(6, 1)),
			new ChessPiece(Colour::White, ChessPiece::PAWN, new Loc(7, 1)),

			new ChessPiece(Colour::Black, ChessPiece::PAWN, new Loc(0, 6)),
			new ChessPiece(Colour::Black, ChessPiece::PAWN, new Loc(1, 6)),
			new ChessPiece(Colour::Black, ChessPiece::PAWN, new Loc(2, 6)),
			new ChessPiece(Colour::Black, ChessPiece::PAWN, new Loc(3, 6)),
			new ChessPiece(Colour::Black, ChessPiece::PAWN, new Loc(4, 6)),
			new ChessPiece(Colour::Black, ChessPiece::PAWN, new Loc(5, 6)),
			new ChessPiece(Colour::Black, ChessPiece::PAWN, new Loc(6, 6)),
			new ChessPiece(Colour::Black, ChessPiece::PAWN, new Loc(7, 6)),

			new ChessPiece(Colour::Black, ChessPiece::ROOK, new Loc(0, 7)),
			new ChessPiece(Colour::Black, ChessPiece::KNIGHT, new Loc(1, 7)),
			new ChessPiece(Colour::Black, ChessPiece::BISHOP, new Loc(2, 7)),
			new ChessPiece(Colour::Black, ChessPiece::QUEEN, new Loc(3, 7)),
			new ChessPiece(Colour::Black, ChessPiece::KING, new Loc(4, 7)),
			new ChessPiece(Colour::Black, ChessPiece::BISHOP, new Loc(5, 7)),
			new ChessPiece(Colour::Black, ChessPiece::KNIGHT, new Loc(6, 7)),
			new ChessPiece(Colour::Black, ChessPiece::ROOK, new Loc(7, 7)),
		];
	}

	public static function insertNewGame(int $startDate, AbstractPlayer $whitePlayer, AbstractPlayer $blackPlayer): void {
		$db = Database::getInstance();
		$db->insert('chess_game', [
			'start_time' => $startDate,
			'white_id' => $whitePlayer->getAccountID(),
			'black_id' => $blackPlayer->getAccountID(),
			'game_id' => $whitePlayer->getGameID(),
		]);
	}

	private function createMove(
		int $pieceID,
		Loc $startLoc,
		Loc $endLoc,
		?int $pieceTaken,
		?string $checking,
		Colour $playerColour,
		?Castling $castling,
		bool $enPassant,
		?int $promotionPieceID,
		bool $draw,
	): string {
		// This move will be set as the most recent move
		$this->lastMove = [
			'From' => $startLoc,
			'To' => $endLoc,
		];

		$otherPlayerColour = $playerColour->opposite();
		$castlingSymbol = match ($castling) {
			Castling::Kingside => '0-0',
			Castling::Queenside => '0-0-0',
			null => '',
		};
		return ($castlingSymbol
			. ChessPiece::getSymbolForPiece($pieceID, $playerColour)
			. $startLoc->algebraic()
			. ' '
			. ($pieceTaken === null ? '' : ChessPiece::getSymbolForPiece($pieceTaken, $otherPlayerColour))
			. $endLoc->algebraic()
			. ($promotionPieceID === null ? '' : ChessPiece::getSymbolForPiece($promotionPieceID, $playerColour))
			. ' '
			. ($checking === null ? '' : ($checking === 'CHECK' ? '+' : '++'))
			. ($enPassant ? ' e.p.' : '')
			. ($draw ? ' =' : '')
		);
	}

	/**
	 * @return array{Type: Castling, RookFrom: Loc, RookTo: Loc}|false
	 */
	public static function isCastling(Loc $loc, Loc $toLoc): array|false {
		if ($loc->y !== $toLoc->y) {
			return false;
		}
		$movement = $toLoc->x - $loc->x;
		return match ($movement) {
			-2 => ['Type' => Castling::Queenside, 'RookFrom' => new Loc(0, $loc->y), 'RookTo' => new Loc(3, $loc->y)],
			2 => ['Type' => Castling::Kingside, 'RookFrom' => new Loc(7, $loc->y), 'RookTo' => new Loc(5, $loc->y)],
			default => false,
		};
	}

	/**
	 * @param string $move Algebraic notation like "b2b4"
	 */
	public function tryAlgebraicMove(string $move): void {
		if (strlen($move) !== 4 && strlen($move) !== 5) {
			throw new Exception('Move of length "' . strlen($move) . '" is not valid, full move: ' . $move);
		}
		$loc = Loc::at(substr($move, 0, 2));
		$toLoc = Loc::at(substr($move, 2, 2));

		$pawnPromotionPiece = ChessPiece::QUEEN;
		if (isset($move[4])) {
			$pawnPromotionPiece = ChessPiece::getPieceForLetter($move[4]);
		}
		$this->tryMove($loc, $toLoc, $this->getCurrentTurnColour(), $pawnPromotionPiece);
	}

	public function tryMove(Loc $loc, Loc $toLoc, Colour $forColour, int $pawnPromotionPiece): string {
		if ($this->hasEnded()) {
			throw new UserError('This game is already over');
		}
		if ($this->getCurrentTurnColour() !== $forColour) {
			throw new UserError('It is not your turn to move');
		}

		$p = $this->board->getPiece($loc);
		if ($p->colour !== $forColour) {
			throw new UserError('That is not your piece to move!');
		}
		$otherColour = $forColour->opposite();

		$moves = $p->getPossibleMoves($this->board);
		$moveIsLegal = false;
		foreach ($moves as $move) {
			if ($move->same($toLoc)) {
				$moveIsLegal = true;
				break;
			}
		}
		if (!$moveIsLegal) {
			throw new UserError('That move is not legal');
		}

		$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
		$currentPlayer = $this->getCurrentTurnPlayer();

		$moveInfo = $this->board->movePiece($loc, $toLoc, $pawnPromotionPiece);

		if ($moveInfo['PawnPromotion']) {
			$pieceID = ChessPiece::PAWN;
			$promotionPieceID = $p->pieceID;
		} else {
			$pieceID = $p->pieceID;
			$promotionPieceID = null;
		}

		$checking = null;
		$draw = false;
		if ($this->board->isCheckmated($otherColour)) {
			$checking = 'MATE';
		} elseif ($this->board->isChecked($otherColour)) {
			$checking = 'CHECK';
		} elseif ($this->board->isDraw($otherColour)) {
			$draw = true;
		}

		$this->getMoves(); // make sure $this->moves is initialized
		$this->moves[] = $this->createMove(
			pieceID: $pieceID,
			startLoc: $loc,
			endLoc: $toLoc,
			pieceTaken: $moveInfo['PieceTaken']?->pieceID,
			checking: $checking,
			playerColour: $forColour,
			castling: $moveInfo['Castling'],
			enPassant: $moveInfo['EnPassant'],
			promotionPieceID: $promotionPieceID,
			draw: $draw,
		);
		if ($this->board->isChecked($forColour)) {
			throw new UserError('You cannot end your turn in check');
		}

		$otherPlayer = $this->getCurrentTurnPlayer();
		if ($moveInfo['PawnPromotion'] !== false) {
			$piecePromotedSymbol = $p->getPieceSymbol();
			$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Own Pawns Promoted', 'Total'], HOF_PUBLIC);
			$otherPlayer->increaseHOF(1, [$chessType, 'Moves', 'Opponent Pawns Promoted', 'Total'], HOF_PUBLIC);
			$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Own Pawns Promoted', $piecePromotedSymbol], HOF_PUBLIC);
			$otherPlayer->increaseHOF(1, [$chessType, 'Moves', 'Opponent Pawns Promoted', $piecePromotedSymbol], HOF_PUBLIC);
		}

		$db = Database::getInstance();
		$db->insert('chess_game_moves', [
			'chess_game_id' => $this->chessGameID,
			'piece_id' => $pieceID,
			'start_x' => $loc->x,
			'start_y' => $loc->y,
			'end_x' => $toLoc->x,
			'end_y' => $toLoc->y,
			'checked' => $checking,
			'piece_taken' => $moveInfo['PieceTaken']?->pieceID,
			'castling' => $moveInfo['Castling']?->value,
			'en_passant' => $db->escapeBoolean($moveInfo['EnPassant']),
			'promote_piece_id' => $promotionPieceID,
		]);

		$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Total Taken'], HOF_PUBLIC);
		if ($moveInfo['PieceTaken'] !== null) {
			$pieceTakenSymbol = $moveInfo['PieceTaken']->getPieceSymbol();
			$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Opponent Pieces Taken', 'Total'], HOF_PUBLIC);
			$otherPlayer->increaseHOF(1, [$chessType, 'Moves', 'Own Pieces Taken', 'Total'], HOF_PUBLIC);
			$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Opponent Pieces Taken', $pieceTakenSymbol], HOF_PUBLIC);
			$otherPlayer->increaseHOF(1, [$chessType, 'Moves', 'Own Pieces Taken', $pieceTakenSymbol], HOF_PUBLIC);
		}

		if ($checking === 'MATE') {
			$message = 'You have checkmated your opponent, congratulations!';
			$otherPlayerMsgPrefix = 'You have lost';
			$this->setWinner($forColour);
		} elseif ($draw) {
			$message = 'You have drawn your opponent.';
			$otherPlayerMsgPrefix = 'You have drawn';
			$this->setDraw();
		} else {
			$message = ''; // non-mating valid move, no message
			$otherPlayerMsgPrefix = 'It is now your turn in';
			if ($checking === 'CHECK') {
				$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Check Given'], HOF_PUBLIC);
				$otherPlayer->increaseHOF(1, [$chessType, 'Moves', 'Check Received'], HOF_PUBLIC);
			}
		}
		$otherPlayer->sendMessageFromCasino($otherPlayerMsgPrefix . ' [chess=' . $this->getChessGameID() . '] against [player=' . $currentPlayer->getPlayerID() . '].');
		$currentPlayer->saveHOF();
		$otherPlayer->saveHOF();
		return $message;
	}

	public function getChessGameID(): int {
		return $this->chessGameID;
	}

	public function getStartDate(): int {
		return $this->startDate;
	}

	public function getGameID(): int {
		return $this->gameID;
	}

	public function getWhitePlayer(): AbstractPlayer {
		return Player::getPlayer($this->whiteID, $this->getGameID());
	}

	public function getWhiteID(): int {
		return $this->whiteID;
	}

	public function getBlackPlayer(): AbstractPlayer {
		return Player::getPlayer($this->blackID, $this->getGameID());
	}

	public function getBlackID(): int {
		return $this->blackID;
	}

	public function getColourID(Colour $colour): int {
		return match ($colour) {
			Colour::White => $this->getWhiteID(),
			Colour::Black => $this->getBlackID(),
		};
	}

	public function getColourPlayer(Colour $colour): AbstractPlayer {
		return Player::getPlayer($this->getColourID($colour), $this->getGameID());
	}

	public function getColourForAccountID(int $accountID): Colour {
		return match ($accountID) {
			$this->getWhiteID() => Colour::White,
			$this->getBlackID() => Colour::Black,
			default => throw new Exception('Account ID is not in this chess game: ' . $accountID),
		};
	}

	/**
	 * Is the given account ID one of the two players of this game?
	 */
	public function isPlayer(int $accountID): bool {
		return $accountID === $this->getWhiteID() || $accountID === $this->getBlackID();
	}

	public function hasEnded(): bool {
		return $this->endDate !== null && $this->endDate <= Epoch::time();
	}

	public function hasWinner(): bool {
		return $this->winner !== 0;
	}

	public function getWinner(): int {
		return $this->winner;
	}

	public function setWinner(Colour $winnerColour): void {
		$winnerAccountID = $this->getColourID($winnerColour);
		$this->updateEndedGame($winnerAccountID);
	}

	public function setDraw(): void {
		$this->updateEndedGame(0); // no winner
	}

	private function updateEndedGame(int $winnerAccountID): void {
		$this->winner = $winnerAccountID;
		$this->endDate = Epoch::time();
		$db = Database::getInstance();
		$db->update(
			'chess_game',
			[
				'end_time' => Epoch::time(),
				'winner_id' => $this->winner,
			],
			['chess_game_id' => $this->chessGameID],
		);

		// Update HOF
		$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
		$results = [];
		if ($this->winner !== 0) {
			$winnerColour = $this->getColourForAccountID($this->winner);
			$results['Won'] = [$this->getColourPlayer($winnerColour)];
			$results['Lost'] = [$this->getColourPlayer($winnerColour->opposite())];
		} else {
			$results['Draw'] = [$this->getWhitePlayer(), $this->getBlackPlayer()];
		}
		foreach ($results as $result => $players) {
			foreach ($players as $player) {
				$player->increaseHOF(1, [$chessType, 'Games', $result], HOF_PUBLIC);
			}
		}
	}

	public function getCurrentTurnColour(): Colour {
		return $this->getBoard()->getCurrentTurnColour();
	}

	public function getCurrentTurnAccountID(): int {
		return match ($this->getCurrentTurnColour()) {
			Colour::White => $this->whiteID,
			Colour::Black => $this->blackID,
		};
	}

	public function getCurrentTurnPlayer(): AbstractPlayer {
		return Player::getPlayer($this->getCurrentTurnAccountID(), $this->getGameID());
	}

	public function getCurrentTurnAccount(): Account {
		return Account::getAccount($this->getCurrentTurnAccountID());
	}

	public function getWhiteAccount(): Account {
		return Account::getAccount($this->getWhiteID());
	}

	public function getBlackAccount(): Account {
		return Account::getAccount($this->getBlackID());
	}

	public function isCurrentTurn(int $accountID): bool {
		return $accountID === $this->getCurrentTurnAccountID();
	}

	public function isNPCGame(): bool {
		return $this->getWhiteAccount()->isNPC() || $this->getBlackAccount()->isNPC();
	}

	/**
	 * @return self::END_*
	 */
	public function resign(int $accountID): int {
		if ($this->hasEnded() || !$this->isPlayer($accountID)) {
			throw new Exception('Invalid resign conditions');
		}

		// If only 1 person has moved then just end the game.
		if (count($this->getMoves()) < 2) {
			$this->endDate = Epoch::time();
			$db = Database::getInstance();
			$db->update(
				'chess_game',
				['end_time' => Epoch::time()],
				['chess_game_id' => $this->chessGameID],
			);
			return self::END_CANCEL;
		}

		$loserColour = $this->getColourForAccountID($accountID);
		$winnerColour = $loserColour->opposite();
		$this->setWinner($winnerColour);

		$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
		$winnerPlayer = $this->getColourPlayer($winnerColour);
		$loserPlayer = $this->getColourPlayer($loserColour);
		$loserPlayer->increaseHOF(1, [$chessType, 'Games', 'Resigned'], HOF_PUBLIC);
		$winnerPlayer->sendMessageFromCasino('[player=' . $loserPlayer->getPlayerID() . '] just resigned against you in [chess=' . $this->getChessGameID() . '].');
		return self::END_RESIGN;
	}

	public function getPlayGameHREF(): string {
		return (new MatchPlay($this->chessGameID))->href();
	}

	public function getResignHREF(): string {
		return (new MatchResignProcessor($this->chessGameID))->href();
	}

}
