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

	/** @var ?array<string, array<string, int>> */
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
	 * @return ?array<string, array<string, int>>
	 */
	public function getLastMove(): ?array {
		$this->getMoves();
		return $this->lastMove;
	}

	/**
	 * Determines if a board square is part of the last move
	 * (returns true for both the 'To' and 'From' squares).
	 */
	public function isLastMoveSquare(int $x, int $y): bool {
		$lastMove = $this->getLastMove();
		if ($lastMove === null) {
			return false;
		}
		return ($x === $lastMove['From']['X'] && $y === $lastMove['From']['Y']) || ($x === $lastMove['To']['X'] && $y === $lastMove['To']['Y']);
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
			foreach ($dbResult->records() as $dbRecord) {
				$forColour = $this->board->getCurrentTurnColour();
				$promotionPieceID = $dbRecord->getNullableInt('promote_piece_id');
				$startX = $dbRecord->getInt('start_x');
				$startY = $dbRecord->getInt('start_y');
				$endX = $dbRecord->getInt('end_x');
				$endY = $dbRecord->getInt('end_y');
				// Update the board state
				$moveInfo = $this->board->movePiece(
					x: $startX,
					y: $startY,
					toX: $endX,
					toY: $endY,
					pawnPromotionPiece: $promotionPieceID ?? ChessPiece::QUEEN,
				);
				$this->moves[] = $this->createMove(
					pieceID: $dbRecord->getInt('piece_id'),
					startX: $startX,
					startY: $startY,
					endX: $endX,
					endY: $endY,
					pieceTaken: $moveInfo['PieceTaken']?->pieceID,
					checking: $dbRecord->getNullableString('checked'),
					playerColour: $forColour,
					castling: $moveInfo['Castling'],
					enPassant: $moveInfo['EnPassant'],
					promotionPieceID: $promotionPieceID,
				);
				$mate = $dbRecord->getNullableString('checked') === 'MATE';
			}
			if (!$mate && $this->hasEnded()) {
				if ($this->hasWinner()) {
					$this->moves[] = ($this->getWinner() === $this->getWhiteID() ? 'Black' : 'White') . ' Resigned.';
				} elseif (count($this->moves) < 2) {
					$this->moves[] = 'Game Cancelled.';
				} else {
					$this->moves[] = 'Game Drawn.';
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
			new ChessPiece(Colour::White, ChessPiece::ROOK, 0, 0),
			new ChessPiece(Colour::White, ChessPiece::KNIGHT, 1, 0),
			new ChessPiece(Colour::White, ChessPiece::BISHOP, 2, 0),
			new ChessPiece(Colour::White, ChessPiece::QUEEN, 3, 0),
			new ChessPiece(Colour::White, ChessPiece::KING, 4, 0),
			new ChessPiece(Colour::White, ChessPiece::BISHOP, 5, 0),
			new ChessPiece(Colour::White, ChessPiece::KNIGHT, 6, 0),
			new ChessPiece(Colour::White, ChessPiece::ROOK, 7, 0),

			new ChessPiece(Colour::White, ChessPiece::PAWN, 0, 1),
			new ChessPiece(Colour::White, ChessPiece::PAWN, 1, 1),
			new ChessPiece(Colour::White, ChessPiece::PAWN, 2, 1),
			new ChessPiece(Colour::White, ChessPiece::PAWN, 3, 1),
			new ChessPiece(Colour::White, ChessPiece::PAWN, 4, 1),
			new ChessPiece(Colour::White, ChessPiece::PAWN, 5, 1),
			new ChessPiece(Colour::White, ChessPiece::PAWN, 6, 1),
			new ChessPiece(Colour::White, ChessPiece::PAWN, 7, 1),

			new ChessPiece(Colour::Black, ChessPiece::PAWN, 0, 6),
			new ChessPiece(Colour::Black, ChessPiece::PAWN, 1, 6),
			new ChessPiece(Colour::Black, ChessPiece::PAWN, 2, 6),
			new ChessPiece(Colour::Black, ChessPiece::PAWN, 3, 6),
			new ChessPiece(Colour::Black, ChessPiece::PAWN, 4, 6),
			new ChessPiece(Colour::Black, ChessPiece::PAWN, 5, 6),
			new ChessPiece(Colour::Black, ChessPiece::PAWN, 6, 6),
			new ChessPiece(Colour::Black, ChessPiece::PAWN, 7, 6),

			new ChessPiece(Colour::Black, ChessPiece::ROOK, 0, 7),
			new ChessPiece(Colour::Black, ChessPiece::KNIGHT, 1, 7),
			new ChessPiece(Colour::Black, ChessPiece::BISHOP, 2, 7),
			new ChessPiece(Colour::Black, ChessPiece::QUEEN, 3, 7),
			new ChessPiece(Colour::Black, ChessPiece::KING, 4, 7),
			new ChessPiece(Colour::Black, ChessPiece::BISHOP, 5, 7),
			new ChessPiece(Colour::Black, ChessPiece::KNIGHT, 6, 7),
			new ChessPiece(Colour::Black, ChessPiece::ROOK, 7, 7),
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

	private function createMove(int $pieceID, int $startX, int $startY, int $endX, int $endY, ?int $pieceTaken, ?string $checking, Colour $playerColour, ?Castling $castling, bool $enPassant, ?int $promotionPieceID): string {
		// This move will be set as the most recent move
		$this->lastMove = [
			'From' => ['X' => $startX, 'Y' => $startY],
			'To' => ['X' => $endX, 'Y' => $endY],
		];

		$otherPlayerColour = $playerColour->opposite();
		$castlingSymbol = match ($castling) {
			Castling::Kingside => '0-0',
			Castling::Queenside => '0-0-0',
			null => '',
		};
		return $castlingSymbol
			. ChessPiece::getSymbolForPiece($pieceID, $playerColour)
			. chr(ord('a') + $startX)
			. ($startY + 1)
			. ' '
			. ($pieceTaken === null ? '' : ChessPiece::getSymbolForPiece($pieceTaken, $otherPlayerColour))
			. chr(ord('a') + $endX)
			. ($endY + 1)
			. ($promotionPieceID === null ? '' : ChessPiece::getSymbolForPiece($promotionPieceID, $playerColour))
			. ' '
			. ($checking === null ? '' : ($checking === 'CHECK' ? '+' : '++'))
			. ($enPassant ? ' e.p.' : '');
	}

	/**
	 * @return array{Type: Castling, X: int, ToX: int}|false
	 */
	public static function isCastling(int $x, int $toX): array|false {
		$movement = $toX - $x;
		return match ($movement) {
			-2 => ['Type' => Castling::Queenside, 'X' => 0, 'ToX' => 3],
			2 => ['Type' => Castling::Kingside, 'X' => 7, 'ToX' => 5],
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
		$file = $move[0];
		$rank = str2int($move[1]);
		$toFile = $move[2];
		$toRank = str2int($move[3]);

		$aVal = ord('a');
		$x = ord($file) - $aVal;
		$toX = ord($toFile) - $aVal;
		$y = $rank - 1;
		$toY = $toRank - 1;

		$pawnPromotionPiece = ChessPiece::QUEEN;
		if (isset($move[4])) {
			$pawnPromotionPiece = ChessPiece::getPieceForLetter($move[4]);
		}
		$this->tryMove($x, $y, $toX, $toY, $this->getCurrentTurnColour(), $pawnPromotionPiece);
	}

	public function tryMove(int $x, int $y, int $toX, int $toY, Colour $forColour, int $pawnPromotionPiece): string {
		if ($this->hasEnded()) {
			throw new UserError('This game is already over');
		}
		if ($this->getCurrentTurnColour() !== $forColour) {
			throw new UserError('It is not your turn to move');
		}

		$p = $this->board->getPiece($x, $y);
		if ($p->colour !== $forColour) {
			throw new UserError('That is not your piece to move!');
		}

		$moves = $p->getPossibleMoves($this->board);
		$moveIsLegal = false;
		foreach ($moves as $move) {
			if ($move[0] === $toX && $move[1] === $toY) {
				$moveIsLegal = true;
				break;
			}
		}
		if (!$moveIsLegal) {
			throw new UserError('That move is not legal');
		}

		$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
		$currentPlayer = $this->getCurrentTurnPlayer();

		$moveInfo = $this->board->movePiece($x, $y, $toX, $toY, $pawnPromotionPiece);

		if ($moveInfo['PawnPromotion']) {
			$pieceID = ChessPiece::PAWN;
			$promotionPieceID = $p->pieceID;
		} else {
			$pieceID = $p->pieceID;
			$promotionPieceID = null;
		}

		$checking = null;
		if ($this->board->isChecked($p->colour->opposite())) {
			$checking = 'CHECK';
		}
		if ($this->board->isCheckmated($p->colour->opposite())) {
			$checking = 'MATE';
		}

		$this->getMoves(); // make sure $this->moves is initialized
		$this->moves[] = $this->createMove(
			pieceID: $pieceID,
			startX: $x,
			startY: $y,
			endX: $toX,
			endY: $toY,
			pieceTaken: $moveInfo['PieceTaken']?->pieceID,
			checking: $checking,
			playerColour: $forColour,
			castling: $moveInfo['Castling'],
			enPassant: $moveInfo['EnPassant'],
			promotionPieceID: $promotionPieceID,
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
			'start_x' => $x,
			'start_y' => $y,
			'end_x' => $toX,
			'end_y' => $toY,
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
			$this->setWinner($this->getColourID($forColour));
			$otherPlayer->sendMessageFromCasino('You have just lost [chess=' . $this->getChessGameID() . '] against [player=' . $currentPlayer->getPlayerID() . '].');
		} else {
			$message = ''; // non-mating valid move, no message
			$otherPlayer->sendMessageFromCasino('It is now your turn in [chess=' . $this->getChessGameID() . '] against [player=' . $currentPlayer->getPlayerID() . '].');
			if ($checking === 'CHECK') {
				$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Check Given'], HOF_PUBLIC);
				$otherPlayer->increaseHOF(1, [$chessType, 'Moves', 'Check Received'], HOF_PUBLIC);
			}
		}
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

	/**
	 * @return array<string, AbstractPlayer>
	 */
	public function setWinner(int $accountID): array {
		$this->winner = $accountID;
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
		$winnerColour = $this->getColourForAccountID($accountID);
		$winningPlayer = $this->getColourPlayer($winnerColour);
		$losingPlayer = $this->getColourPlayer($winnerColour->opposite());
		$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
		$winningPlayer->increaseHOF(1, [$chessType, 'Games', 'Won'], HOF_PUBLIC);
		$losingPlayer->increaseHOF(1, [$chessType, 'Games', 'Lost'], HOF_PUBLIC);
		return ['Winner' => $winningPlayer, 'Loser' => $losingPlayer];
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
		$winnerAccountID = $this->getColourID($loserColour->opposite());
		$results = $this->setWinner($winnerAccountID);
		$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
		$results['Loser']->increaseHOF(1, [$chessType, 'Games', 'Resigned'], HOF_PUBLIC);
		$results['Winner']->sendMessageFromCasino('[player=' . $results['Loser']->getPlayerID() . '] just resigned against you in [chess=' . $this->getChessGameID() . '].');
		return self::END_RESIGN;
	}

	public function getPlayGameHREF(): string {
		return (new MatchPlay($this->chessGameID))->href();
	}

	public function getResignHREF(): string {
		return (new MatchResignProcessor($this->chessGameID))->href();
	}

}
