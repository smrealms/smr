<?php declare(strict_types=1);

namespace Smr\Chess;

use Exception;

class ChessPiece {

	public const int KING = 1;
	public const int QUEEN = 2;
	public const int ROOK = 3;
	public const int BISHOP = 4;
	public const int KNIGHT = 5;
	public const int PAWN = 6;

	public function __construct(
		public readonly Colour $colour,
		public int $pieceID,
		public Loc $loc,
	) {}

	public function isSafeMove(Board $board, Loc $toLoc): bool {
		// Make a deep copy of the board so that we can inspect possible future
		// positions without actually changing the state of the real board.
		$boardCopy = $board->deepCopy();
		$boardCopy->movePiece($this->loc, $toLoc);
		return !$boardCopy->isChecked($this->colour);
	}

	/**
	 * Check if the piece is attacking a specific square
	 */
	public function isAttacking(Board $board, Loc $loc): bool {
		$moves = $this->getPossibleMoves($board, attackingCheck: true);
		foreach ($moves as $toLoc) {
			if ($toLoc->same($loc)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return array<Loc>
	 */
	public function getPossibleMoves(Board $board, bool $attackingCheck = false): array {
		$moves = [];
		if ($this->pieceID === self::PAWN) {
			$dirY = match ($this->colour) {
				Colour::White => 1,
				Colour::Black => -1,
			};
			//Pawn forward movement is not attacking - so don't check it if doing an attacking check.
			if (!$attackingCheck) {
				$move1 = $this->loc->relative(dy: $dirY);
				if (!$board->hasPiece($move1) && $this->isSafeMove($board, $move1)) {
					$moves[] = $move1;
				}
				$pawnStartY = match ($this->colour) {
					Colour::White => 1,
					Colour::Black => 6,
				};
				if ($this->loc->y === $pawnStartY) { // pawn can still double move
					$move2 = $this->loc->relative(dy: 2 * $dirY);
					if (!$board->hasPiece($move1) && !$board->hasPiece($move2) && $this->isSafeMove($board, $move2)) {
						$moves[] = $move2;
					}
				}
			}
			// Diagonal attack (including en passant)
			foreach ([-1, 1] as $dirX) {
				$moveDiag = $this->loc->relativeOrNull($dirX, $dirY);
				if ($moveDiag !== null) {
					$epTarget = new Loc($moveDiag->x, $this->loc->y);
					if ($attackingCheck ||
						(($board->getEnPassantPawn()?->same($epTarget) ||
						($board->getPieceOrNull($moveDiag)?->colour === $this->colour->opposite()))
						&& $this->isSafeMove($board, $moveDiag))) {
						$moves[] = $moveDiag;
					}
				}
			}
		}
		if ($this->pieceID === self::KING) {
			foreach ([-1, 0, 1] as $moveX) {
				foreach ([-1, 0, 1] as $moveY) {
					if ($moveX !== 0 || $moveY !== 0) {
						$this->addMove($moveX, $moveY, $board, $moves, $attackingCheck);
					}
				}
			}
			//Castling is not attacking - so don't check it if doing an attacking check.
			if (!$attackingCheck && !$board->isChecked($this->colour) && $this->loc->x === 4) {
				if ($board->canCastle($this->colour, Castling::Queenside) &&
					!$board->hasPiece($this->loc->relative(dx: -1)) &&
					!$board->hasPiece($this->loc->relative(dx: -3)) &&
					$this->isSafeMove($board, $this->loc->relative(dx: -1))
				) {
					$this->addMove(-2, 0, $board, $moves, $attackingCheck);
				}
				if ($board->canCastle($this->colour, Castling::Kingside) &&
					!$board->hasPiece($this->loc->relative(dx: 1)) &&
					$this->isSafeMove($board, $this->loc->relative(dx: 1))
				) {
					$this->addMove(2, 0, $board, $moves, $attackingCheck);
				}
			}
		}
		if ($this->pieceID === self::QUEEN || $this->pieceID === self::ROOK) {
			// Unlimited linear movement
			$moveX = 0;
			while ($this->addMove(--$moveX, 0, $board, $moves, $attackingCheck)); //Left
			$moveX = 0;
			while ($this->addMove(++$moveX, 0, $board, $moves, $attackingCheck)); //Right
			$moveY = 0;
			while ($this->addMove(0, ++$moveY, $board, $moves, $attackingCheck)); //Up
			$moveY = 0;
			while ($this->addMove(0, --$moveY, $board, $moves, $attackingCheck)); //Down
		}
		if ($this->pieceID === self::QUEEN || $this->pieceID === self::BISHOP) {
			// Unlimited diagonal movement
			$moveX = 0;
			$moveY = 0;
			while ($this->addMove(--$moveX, --$moveY, $board, $moves, $attackingCheck)); //Left-Down
			$moveX = 0;
			$moveY = 0;
			while ($this->addMove(++$moveX, --$moveY, $board, $moves, $attackingCheck)); //Right-Down
			$moveX = 0;
			$moveY = 0;
			while ($this->addMove(--$moveX, ++$moveY, $board, $moves, $attackingCheck)); //Left-Up
			$moveX = 0;
			$moveY = 0;
			while ($this->addMove(++$moveX, ++$moveY, $board, $moves, $attackingCheck)); //Right-Up
		}
		if ($this->pieceID === self::KNIGHT) {
			$knightMoves = [[2, -1], [1, -2], [-1, -2], [-2, -1], [-2, 1], [-1, 2], [1, 2], [2, 1]];
			foreach ($knightMoves as [$moveX, $moveY]) {
				$this->addMove($moveX, $moveY, $board, $moves, $attackingCheck);
			}
		}

		return $moves;
	}

	/**
	 * @param list<Loc> $moves
	 */
	private function addMove(int $dx, int $dy, Board $board, array &$moves, bool $attackingCheck = true): bool {
		$toLoc = $this->loc->relativeOrNull($dx, $dy);
		if ($toLoc !== null) {
			$toPiece = $board->getPieceOrNull($toLoc);
			if ($toPiece === null || $toPiece->colour !== $this->colour) {
				//We can only actually move to this position if it is safe to do so, however we can pass through it looking for a safe move so we still want to return true.
				if (($attackingCheck === true || $this->isSafeMove($board, $toLoc))) {
					$moves[] = $toLoc;
				}
				return $toPiece === null; // no more moves if we hit another piece
			}
		}
		return false;
	}

	public function getPieceLetter(): string {
		return self::getLetterForPiece($this->pieceID, $this->colour);
	}

	public function getPieceSymbol(): string {
		return self::getSymbolForPiece($this->pieceID, $this->colour);
	}

	public static function getSymbolForPiece(int $pieceID, Colour $colour): string {
		return '&#' . (9811 + $pieceID + ($colour === Colour::White ? 0 : 6)) . ';';
	}

	public static function getLetterForPiece(int $pieceID, Colour $colour): string {
		$letter = match ($pieceID) {
			self::KING => 'k',
			self::QUEEN => 'q',
			self::ROOK => 'r',
			self::BISHOP => 'b',
			self::KNIGHT => 'n',
			self::PAWN => 'p',
			default => throw new Exception('Invalid chess piece ID: ' . $pieceID),
		};
		if ($colour === Colour::White) {
			$letter = strtoupper($letter);
		}
		return $letter;
	}

	/**
	 * @return self::*
	 */
	public static function getPieceForLetter(string $letter): int {
		return match (strtolower($letter)) {
			'k' => self::KING,
			'q' => self::QUEEN,
			'r' => self::ROOK,
			'b' => self::BISHOP,
			'n' => self::KNIGHT,
			'p' => self::PAWN,
			default => throw new Exception('Invalid chess piece letter: ' . $letter),
		};
	}

}
