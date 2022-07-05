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
		public int $pieceNo = -1) {
	}

	/**
	 * @param array<int, array<int, ?ChessPiece>> $board
	 * @param array<mixed> $hasMoved
	 */
	public function isSafeMove(array $board, array $hasMoved, int $toX, int $toY): bool {
		// Make a deep copy of the board so that we can inspect possible future
		// positions without actually changing the state of the real board.
		// (Note $hasMoved is safe to shallow copy since it has no objects.)
		$boardCopy = [];
		foreach ($board as $y => $row) {
			foreach ($row as $x => $piece) {
				if ($piece === null) {
					$boardCopy[$y][$x] = null;
				} else {
					$boardCopy[$y][$x] = clone $piece;
				}
			}
		}

		ChessGame::movePiece($boardCopy, $hasMoved, $this->x, $this->y, $toX, $toY);
		return !ChessGame::isPlayerChecked($boardCopy, $hasMoved, $this->colour);
	}

	/**
	 * @param array<int, array<int, ?ChessPiece>> $board
	 * @param array<mixed> $hasMoved
	 */
	public function isAttacking(array $board, array $hasMoved, bool $king, int $x = -1, int $y = -1): bool {
		$moves = $this->getPossibleMoves($board, $hasMoved, null, true);
		foreach ($moves as [$toX, $toY]) {
			$p = $board[$toY][$toX];
			if (($toX == $x && $toY == $y) || ($king === true && $p != null && $p->pieceID == self::KING && $this->colour != $p->colour)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param array<int, array<int, ?ChessPiece>> $board
	 * @param array<mixed> $hasMoved
	 * @return array<array{int, int}>>
	 */
	public function getPossibleMoves(array $board, array $hasMoved, Colour $forColour = null, bool $attackingCheck = false): array {
		$moves = [];
		if ($forColour === null || $this->colour === $forColour) {
			if ($this->pieceID == self::PAWN) {
				$dirY = $this->colour == Colour::Black ? 1 : -1;
				$moveY = $this->y + $dirY;
				//Pawn forward movement is not attacking - so don't check it if doing an attacking check.
				if (!$attackingCheck) {
					if (ChessGame::isValidCoord($this->x, $moveY, $board) && $board[$moveY][$this->x] === null && $this->isSafeMove($board, $hasMoved, $this->x, $moveY)) {
						$moves[] = [$this->x, $moveY];
					}
					$doubleMoveY = $moveY + $dirY;
					if ($this->y - $dirY == 0 || $this->y - $dirY * 2 == count($board)) { //Double move first move
						if ($board[$moveY][$this->x] === null && $board[$doubleMoveY][$this->x] === null && $this->isSafeMove($board, $hasMoved, $this->x, $doubleMoveY)) {
							$moves[] = [$this->x, $doubleMoveY];
						}
					}
				}
				for ($i = -1; $i < 2; $i += 2) {
					$moveX = $this->x + $i;
					if (ChessGame::isValidCoord($moveX, $moveY, $board)) {
						if ($attackingCheck ||
							((($hasMoved[self::PAWN][0] == $moveX && $hasMoved[self::PAWN][1] == $this->y) ||
							($board[$moveY][$moveX] != null && $board[$moveY][$moveX]->colour != $this->colour))
							&& $this->isSafeMove($board, $hasMoved, $moveX, $moveY))) {
							$moves[] = [$moveX, $moveY];
						}
					}
				}
			} elseif ($this->pieceID == self::KING) {
				for ($i = -1; $i < 2; $i++) {
					for ($j = -1; $j < 2; $j++) {
						if ($i != 0 || $j != 0) {
							$this->addMove($this->x + $i, $this->y + $j, $board, $moves, $hasMoved, $attackingCheck);
						}
					}
				}
				//Castling is not attacking - so don't check it if doing an attacking check.
				if (!$attackingCheck && !$hasMoved[$this->colour->value][self::KING] && !ChessGame::isPlayerChecked($board, $hasMoved, $this->colour)) {
					if (!$hasMoved[$this->colour->value][self::ROOK]['Queen'] &&
							ChessGame::isValidCoord($this->x - 1, $this->y, $board) && $board[$this->y][$this->x - 1] === null &&
							ChessGame::isValidCoord($this->x - 3, $this->y, $board) && $board[$this->y][$this->x - 3] === null &&
							$this->isSafeMove($board, $hasMoved, $this->x - 1, $this->y)) {
						$this->addMove($this->x - 2, $this->y, $board, $moves, $hasMoved, $attackingCheck);
					}
					if (!$hasMoved[$this->colour->value][self::ROOK]['King'] &&
							ChessGame::isValidCoord($this->x + 1, $this->y, $board) && $board[$this->y][$this->x + 1] === null &&
							$this->isSafeMove($board, $hasMoved, $this->x + 1, $this->y)) {
						$this->addMove($this->x + 2, $this->y, $board, $moves, $hasMoved, $attackingCheck);
					}
				}
			} elseif ($this->pieceID == self::QUEEN) {
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove(--$moveX, $moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Left
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove(++$moveX, $moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Right
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove($moveX, ++$moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Up
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove($moveX, --$moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Down
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove(--$moveX, --$moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Up-Left
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove(++$moveX, --$moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Up-Right
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove(--$moveX, ++$moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Down-Left
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove(++$moveX, ++$moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Up-Left
			} elseif ($this->pieceID == self::ROOK) {
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove(--$moveX, $moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Left
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove(++$moveX, $moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Right
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove($moveX, ++$moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Up
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove($moveX, --$moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Down
			} elseif ($this->pieceID == self::BISHOP) {
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove(--$moveX, --$moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Up-Left
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove(++$moveX, --$moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Up-Right
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove(--$moveX, ++$moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Down-Left
				$moveX = $this->x;
				$moveY = $this->y;
				while ($this->addMove(++$moveX, ++$moveY, $board, $moves, $hasMoved, $attackingCheck) && $board[$moveY][$moveX] === null); //Up-Left
			} elseif ($this->pieceID == self::KNIGHT) {
				$moveX = $this->x - 1;
				$moveY = $this->y - 2;
				$this->addMove($moveX, $moveY, $board, $moves, $hasMoved, $attackingCheck); //2up-left
				$moveX += 2;
				$this->addMove($moveX, $moveY, $board, $moves, $hasMoved, $attackingCheck); //2up-right
				$moveY = $this->y + 2;
				$this->addMove($moveX, $moveY, $board, $moves, $hasMoved, $attackingCheck); //2down-right
				$moveX -= 2;
				$this->addMove($moveX, $moveY, $board, $moves, $hasMoved, $attackingCheck); //2down-left
				$moveX = $this->x - 2;
				$moveY = $this->y - 1;
				$this->addMove($moveX, $moveY, $board, $moves, $hasMoved, $attackingCheck); //2left-up
				$moveY += 2;
				$this->addMove($moveX, $moveY, $board, $moves, $hasMoved, $attackingCheck); //2left-down
				$moveX = $this->x + 2;
				$this->addMove($moveX, $moveY, $board, $moves, $hasMoved, $attackingCheck); //2right-down
				$moveY -= 2;
				$this->addMove($moveX, $moveY, $board, $moves, $hasMoved, $attackingCheck); //2right-up
			}
		}

		return $moves;
	}

	/**
	 * @param array<int, array<int, ?ChessPiece>> $board
	 * @param array{int, int} $moves
	 * @param array<mixed> $hasMoved
	 */
	private function addMove(int $toX, int $toY, array $board, array &$moves, array $hasMoved, bool $attackingCheck = true): bool {
		if (ChessGame::isValidCoord($toX, $toY, $board)) {
			if (($board[$toY][$toX] === null || $board[$toY][$toX]->colour != $this->colour)) {
				//We can only actually move to this position if it is safe to do so, however we can pass through it looking for a safe move so we still want to return true.
				if (($attackingCheck === true || $this->isSafeMove($board, $hasMoved, $toX, $toY))) {
					$moves[] = [$toX, $toY];
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * @param array<int, array<int, ?ChessPiece>> $board
	 * @return array<string, int>
	 */
	public function promote(int $pawnPromotionPieceID, array $board): array {
		$takenNos = [];
		foreach ($board as $row) {
			foreach ($row as $piece) {
				if ($piece != null && $piece->pieceID == $pawnPromotionPieceID && $piece->colour == $this->colour) {
					$takenNos[$piece->pieceNo] = true;
				}
			}
		}
		$i = 0;
		while (isset($takenNos[$i])) {
			$i++;
		}
		return ['PieceID' => $pawnPromotionPieceID, 'PieceNo' => $i];
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
