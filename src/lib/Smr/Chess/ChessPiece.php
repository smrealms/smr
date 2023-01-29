<?php declare(strict_types=1);

namespace Smr\Chess;

use Exception;

class ChessPiece {

	public const KING = 1;
	public const QUEEN = 2;
	public const ROOK = 3;
	public const BISHOP = 4;
	public const KNIGHT = 5;
	public const PAWN = 6;

	public function __construct(
		public readonly Colour $colour,
		public int $pieceID,
		public int $x,
		public int $y,
	) {}

	public function isSafeMove(Board $board, int $toX, int $toY): bool {
		// Make a deep copy of the board so that we can inspect possible future
		// positions without actually changing the state of the real board.
		// (Note $hasMoved is safe to shallow copy since it has no objects.)
		$boardCopy = $board->deepCopy();
		$boardCopy->movePiece($this->x, $this->y, $toX, $toY);
		return !$boardCopy->isChecked($this->colour);
	}

	/**
	 * Check if the piece is attacking a specific square
	 */
	public function isAttacking(Board $board, int $x, int $y): bool {
		$moves = $this->getPossibleMoves($board, attackingCheck: true);
		foreach ($moves as [$toX, $toY]) {
			if ($toX == $x && $toY == $y) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return array<array{int, int}>>
	 */
	public function getPossibleMoves(Board $board, bool $attackingCheck = false): array {
		$moves = [];
		if ($this->pieceID == self::PAWN) {
			$dirY = match ($this->colour) {
				Colour::White => 1,
				Colour::Black => -1,
			};
			$moveY = $this->y + $dirY;
			//Pawn forward movement is not attacking - so don't check it if doing an attacking check.
			if (!$attackingCheck) {
				if ($board->isValidCoord($this->x, $moveY) && !$board->hasPiece($this->x, $moveY) && $this->isSafeMove($board, $this->x, $moveY)) {
					$moves[] = [$this->x, $moveY];
				}
				$doubleMoveY = $moveY + $dirY;
				if ($this->y - $dirY == 0 || $this->y - $dirY * 2 == Board::NY) { //Double move first move
					if (!$board->hasPiece($this->x, $moveY) && !$board->hasPiece($this->x, $doubleMoveY) && $this->isSafeMove($board, $this->x, $doubleMoveY)) {
						$moves[] = [$this->x, $doubleMoveY];
					}
				}
			}
			for ($i = -1; $i < 2; $i += 2) {
				$moveX = $this->x + $i;
				if ($board->isValidCoord($moveX, $moveY)) {
					if ($attackingCheck ||
						(($board->getEnPassantPawn() === ['X' => $moveX, 'Y' => $this->y] ||
						($board->hasPiece($moveX, $moveY) && $board->getPiece($moveX, $moveY)->colour != $this->colour))
						&& $this->isSafeMove($board, $moveX, $moveY))) {
						$moves[] = [$moveX, $moveY];
					}
				}
			}
		}
		if ($this->pieceID == self::KING) {
			for ($i = -1; $i < 2; $i++) {
				for ($j = -1; $j < 2; $j++) {
					if ($i != 0 || $j != 0) {
						$this->addMove($this->x + $i, $this->y + $j, $board, $moves, $attackingCheck);
					}
				}
			}
			//Castling is not attacking - so don't check it if doing an attacking check.
			if (!$attackingCheck && !$board->isChecked($this->colour)) {
				if ($board->canCastle($this->colour, Castling::Queenside) &&
					$board->isValidCoord($this->x - 1, $this->y) && !$board->hasPiece($this->x - 1, $this->y) &&
					$board->isValidCoord($this->x - 3, $this->y) && !$board->hasPiece($this->x - 3, $this->y) &&
					$this->isSafeMove($board, $this->x - 1, $this->y)
				) {
					$this->addMove($this->x - 2, $this->y, $board, $moves, $attackingCheck);
				}
				if ($board->canCastle($this->colour, Castling::Kingside) &&
					$board->isValidCoord($this->x + 1, $this->y) && !$board->hasPiece($this->x + 1, $this->y) &&
					$this->isSafeMove($board, $this->x + 1, $this->y)
				) {
					$this->addMove($this->x + 2, $this->y, $board, $moves, $attackingCheck);
				}
			}
		}
		if ($this->pieceID == self::QUEEN || $this->pieceID == self::ROOK) {
			// Unlimited linear movement
			$moveX = $this->x;
			while ($this->addMove(--$moveX, $this->y, $board, $moves, $attackingCheck) && !$board->hasPiece($moveX, $this->y)); //Left
			$moveX = $this->x;
			while ($this->addMove(++$moveX, $this->y, $board, $moves, $attackingCheck) && !$board->hasPiece($moveX, $this->y)); //Right
			$moveY = $this->y;
			while ($this->addMove($this->x, ++$moveY, $board, $moves, $attackingCheck) && !$board->hasPiece($this->x, $moveY)); //Up
			$moveY = $this->y;
			while ($this->addMove($this->x, --$moveY, $board, $moves, $attackingCheck) && !$board->hasPiece($this->x, $moveY)); //Down
		}
		if ($this->pieceID == self::QUEEN || $this->pieceID == self::BISHOP) {
			// Unlimited diagonal movement
			$moveX = $this->x;
			$moveY = $this->y;
			while ($this->addMove(--$moveX, --$moveY, $board, $moves, $attackingCheck) && !$board->hasPiece($moveX, $moveY)); //Left-Down
			$moveX = $this->x;
			$moveY = $this->y;
			while ($this->addMove(++$moveX, --$moveY, $board, $moves, $attackingCheck) && !$board->hasPiece($moveX, $moveY)); //Right-Down
			$moveX = $this->x;
			$moveY = $this->y;
			while ($this->addMove(--$moveX, ++$moveY, $board, $moves, $attackingCheck) && !$board->hasPiece($moveX, $moveY)); //Left-Up
			$moveX = $this->x;
			$moveY = $this->y;
			while ($this->addMove(++$moveX, ++$moveY, $board, $moves, $attackingCheck) && !$board->hasPiece($moveX, $moveY)); //Right-Up
		}
		if ($this->pieceID == self::KNIGHT) {
			$knightMoves = [[2, -1], [1, -2], [-1, -2], [-2, -1], [-2, 1], [-1, 2], [1, 2], [2, 1]];
			foreach ($knightMoves as [$moveX, $moveY]) {
				$this->addMove($this->x + $moveX, $this->y + $moveY, $board, $moves, $attackingCheck);
			}
		}

		return $moves;
	}

	/**
	 * @param array{int, int} $moves
	 */
	private function addMove(int $toX, int $toY, Board $board, array &$moves, bool $attackingCheck = true): bool {
		if ($board->isValidCoord($toX, $toY)) {
			if (!$board->hasPiece($toX, $toY) || $board->getPiece($toX, $toY)->colour != $this->colour) {
				//We can only actually move to this position if it is safe to do so, however we can pass through it looking for a safe move so we still want to return true.
				if (($attackingCheck === true || $this->isSafeMove($board, $toX, $toY))) {
					$moves[] = [$toX, $toY];
				}
				return true;
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
		return '&#' . (9811 + $pieceID + ($colour == Colour::White ? 0 : 6)) . ';';
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
		if ($colour == Colour::White) {
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
