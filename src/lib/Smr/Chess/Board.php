<?php declare(strict_types=1);

namespace Smr\Chess;

use Exception;

class Board {

	public const int NX = 8; // number of x-coordinates
	public const int NY = 8; // number of y-coordinates

	/** @var array<value-of<Colour>, array<Castling>> */
	private array $canCastle;
	private ?Loc $enPassantPawn;
	/** @var array<int, array<int, ?ChessPiece>> */
	private array $board;
	private int $numMoves = 0;

	public function __construct(bool $initialize = true) {
		if ($initialize) {
			$this->initialize();
		}
	}

	private function initialize(): void {
		// Set up the pieces on the board
		$pieces = ChessGame::getStandardGame();
		$row = array_fill(0, self::NX, null);
		$board = array_fill(0, self::NY, $row);
		foreach ($pieces as $piece) {
			if ($board[$piece->loc->y][$piece->loc->x] !== null) {
				throw new Exception('Two pieces found in the same tile.');
			}
			$board[$piece->loc->y][$piece->loc->x] = $piece;
		}
		$this->board = $board;

		// Initialize metadata (castling/en passant)
		foreach (Colour::cases() as $colour) {
			$this->setCastling($colour, Castling::cases());
		}
		$this->setEnPassantPawn(null);
	}

	/**
	 * @param array<Castling> $castling Set allowed castling operations
	 */
	public function setCastling(Colour $colour, array $castling): void {
		$this->canCastle[$colour->value] = $castling;
	}

	public function setEnPassantPawn(?Loc $enPassantPawn): void {
		$this->enPassantPawn = $enPassantPawn;
	}

	public function deepCopy(): self {
		$copy = new self(initialize: false);
		$copy->board = [];
		foreach ($this->board as $y => $row) {
			foreach ($row as $x => $piece) {
				if ($piece === null) {
					$copy->board[$y][$x] = null;
				} else {
					$copy->board[$y][$x] = clone $piece;
				}
			}
		}
		$copy->canCastle = $this->canCastle;
		$copy->enPassantPawn = $this->enPassantPawn;
		$copy->numMoves = $this->numMoves;
		return $copy;
	}

	public function getFEN(): string {
		$fen = '';
		$blanks = 0;
		foreach (range(Loc::MAX_Y, 0, -1) as $y) {
			foreach (range(0, Loc::MAX_X) as $x) {
				$loc = new Loc($x, $y);
				if (!$this->hasPiece($loc)) {
					$blanks++;
				} else {
					if ($blanks > 0) {
						$fen .= $blanks;
						$blanks = 0;
					}
					$fen .= $this->getPiece($loc)->getPieceLetter();
				}
			}
			if ($blanks > 0) {
				$fen .= $blanks;
				$blanks = 0;
			}
			if ($y > 0) {
				$fen .= '/';
			}
		}
		$fen .= match ($this->getCurrentTurnColour()) {
			Colour::White => ' w ',
			Colour::Black => ' b ',
		};

		// Castling
		$castling = '';
		foreach (Colour::cases() as $colour) {
			if ($this->canCastle($colour, Castling::Kingside)) {
				$castling .= ChessPiece::getLetterForPiece(ChessPiece::KING, $colour);
			}
			if ($this->canCastle($colour, Castling::Queenside)) {
				$castling .= ChessPiece::getLetterForPiece(ChessPiece::QUEEN, $colour);
			}
		}
		if ($castling === '') {
			$castling = '-';
		}
		$fen .= $castling . ' ';

		// En passant
		$epLoc = $this->getEnPassantPawn();
		if ($epLoc !== null) {
			$fen .= $epLoc->file();
			$fen .= match ($epLoc->y) {
				3 => '3', // white pawn on rank 4
				4 => '6', // black pawn on rank 5
				default => throw new Exception('Invalid en passant target: ' . $epLoc->algebraic()),
			};
		} else {
			$fen .= '-';
		}
		$fen .= ' 0 ' . floor($this->numMoves / 2);

		return $fen;
	}

	public function getCurrentTurnColour(): Colour {
		return $this->numMoves % 2 === 0 ? Colour::White : Colour::Black;
	}

	/**
	 * Get the board from the colour's perspective as an array.
	 * The first dimension is the y-coordinate, second is x-coordinate.
	 *
	 * @return array<int, array<int, ?ChessPiece>>
	 */
	public function getBoardDisplay(bool $playerIsWhite): array {
		if ($playerIsWhite) {
			// Reverse rows
			$board = array_reverse($this->board, true);
		} else {
			// Reverse columns
			$board = $this->board;
			foreach ($board as $key => $row) {
				$board[$key] = array_reverse($row, true);
			}
		}
		return $board;
	}

	/**
	 * @return array<ChessPiece>
	 */
	public function getPieces(?Colour $colour = null): array {
		$pieces = [];
		foreach ($this->board as $row) {
			foreach ($row as $piece) {
				if ($piece !== null && ($colour === null || $colour === $piece->colour)) {
					$pieces[] = $piece;
				}
			}
		}
		return $pieces;
	}

	public function getPieceOrNull(Loc $loc): ?ChessPiece {
		return $this->board[$loc->y][$loc->x];
	}

	public function hasPiece(Loc $loc): bool {
		return $this->getPieceOrNull($loc) !== null;
	}

	public function getPiece(Loc $loc): ChessPiece {
		$piece = $this->getPieceOrNull($loc);
		if ($piece === null) {
			throw new Exception('No piece found on: ' . $loc->algebraic());
		}
		return $piece;
	}

	public function getKing(Colour $colour): ChessPiece {
		foreach ($this->getPieces($colour) as $piece) {
			if ($piece->pieceID === ChessPiece::KING) {
				return $piece;
			}
		}
		throw new Exception('Could not find the ' . $colour->value . ' King!');
	}

	public function canCastle(Colour $colour, Castling $type): bool {
		return in_array($type, $this->canCastle[$colour->value], true);
	}

	public function getEnPassantPawn(): ?Loc {
		return $this->enPassantPawn;
	}

	/**
	 * Is the $colour King being attacked by any enemy pieces?
	 */
	public function isChecked(Colour $colour): bool {
		$king = $this->getKing($colour);
		foreach ($this->getPieces($colour->opposite()) as $piece) {
			if ($piece->isAttacking($this, $king->loc)) {
				return true;
			}
		}
		return false;
	}

	public function isCheckmated(Colour $colour): bool {
		// Checkmate only if there are no legal moves and King is in check
		return !$this->hasLegalMoves($colour) && $this->isChecked($colour);
	}

	public function isDraw(Colour $colour): bool {
		if (!$this->hasLegalMoves($colour) && !$this->isChecked($colour)) {
			// Draw by stalemate
			return true;
		}
		// Draw by insufficient material
		// (Consider only the case where Kings remain, others too complex)
		foreach ($this->getPieces() as $piece) {
			if ($piece->pieceID !== ChessPiece::KING) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Can $colour make any legal moves in the current position?
	 */
	private function hasLegalMoves(Colour $colour): bool {
		foreach ($this->getPieces($colour) as $piece) {
			if (count($piece->getPossibleMoves($this)) > 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Change the position of a piece on the board
	 */
	public function setSquare(Loc $loc, ChessPiece $piece): void {
		$this->board[$loc->y][$loc->x] = $piece;
		$piece->loc = $loc;
	}

	/**
	 * Remove the piece, if any, from this square
	 */
	public function clearSquare(Loc $loc): void {
		$this->board[$loc->y][$loc->x] = null;
	}

	/**
	 * Remove all pieces from the board
	 */
	public function clear(): void {
		foreach ($this->board as $y => $row) {
			foreach (array_keys($row) as $x) {
				$this->board[$y][$x] = null;
			}
		}
		$this->setEnPassantPawn(null);
		$this->setCastling(Colour::White, []);
		$this->setCastling(Colour::Black, []);
	}

	/**
	 * Move a piece. Note that legality of the move is not checked here.
	 *
	 * @return array{Castling: ?Castling, PieceTaken: ?ChessPiece, EnPassant: bool, PawnPromotion: bool}
	 */
	public function movePiece(Loc $loc, Loc $toLoc, int $pawnPromotionPiece = ChessPiece::QUEEN): array {
		$piece = $this->getPiece($loc);
		$pieceTaken = $this->getPieceOrNull($toLoc);
		if ($pieceTaken?->pieceID === ChessPiece::KING) {
			throw new Exception('King cannot be taken');
		}

		// Update the board state
		$this->setSquare($toLoc, $piece);
		$this->clearSquare($loc);

		$canEnPassant = $this->getEnPassantPawn();
		$enPassant = false;
		$enPassantPawn = null;
		$castlingType = null;
		$pawnPromotion = false;

		if ($piece->pieceID === ChessPiece::KING) {
			// We've moved the King, so no more castling
			$this->canCastle[$piece->colour->value] = [];

			// If castling, also update the Rook position
			$castling = ChessGame::isCastling($loc, $toLoc);
			if ($castling !== false) {
				$castlingType = $castling['Type'];
				$rook = $this->getPiece($castling['RookFrom']);
				$this->setSquare($castling['RookTo'], $rook);
				$this->clearSquare($castling['RookFrom']);
			}
		} elseif ($piece->pieceID === ChessPiece::ROOK) {
			// We've moved a Rook, so can no longer castle with it
			if ($loc->x === 0) {
				array_remove_value($this->canCastle[$piece->colour->value], Castling::Queenside);
			} elseif ($loc->x === 7) {
				array_remove_value($this->canCastle[$piece->colour->value], Castling::Kingside);
			}
		} elseif ($piece->pieceID === ChessPiece::PAWN) {
			if ($toLoc->y === 0 || $toLoc->y === 7) {
				// Pawn was promoted
				$pawnPromotion = true;
				$piece->pieceID = $pawnPromotionPiece;
			} elseif ($loc->y === 1 && $toLoc->y === 3 || $loc->y === 6 && $toLoc->y === 4) {
				// Double move to track?
				$enPassantPawn = $toLoc;
			} elseif ($canEnPassant !== null && $canEnPassant->x === $toLoc->x && ($canEnPassant->y === 3 && $toLoc->y === 2 || $canEnPassant->y === 4 && $toLoc->y === 5)) {
				// En passant? Update the taken pawn.
				$enPassant = true;
				$pieceTaken = $this->getPiece($canEnPassant);
				$this->clearSquare($canEnPassant);
			}
		}

		// Track if the move we just made allows the opponent to take en passant
		$this->enPassantPawn = $enPassantPawn;

		// Increment the move counter
		$this->numMoves++;

		return [
			'Castling' => $castlingType,
			'PieceTaken' => $pieceTaken,
			'EnPassant' => $enPassant,
			'PawnPromotion' => $pawnPromotion,
		];
	}

}
