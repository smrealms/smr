<?php declare(strict_types=1);

namespace SmrTest\lib\Chess;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\Chess\Board;
use Smr\Chess\Castling;
use Smr\Chess\ChessGame;
use Smr\Chess\ChessPiece;
use Smr\Chess\Colour;

#[CoversClass(Board::class)]
class BoardTest extends TestCase {

	public function test_getFEN(): void {
		// Note that this does not test castling, promotion, or non-e.p.
		$board = new Board();
		$board->movePiece(6, 0, 5, 2); // Nf3 (for space in back row)
		$board->movePiece(3, 6, 3, 4); // d5 (for space in pawn row, and e.p.)
		$expected = 'rnbqkbnr/ppp1pppp/8/3p4/8/5N2/PPPPPPPP/RNBQKB1R w KQkq d6 0 1';
		self::assertSame($expected, $board->getFEN());
	}

	public function test_getCurrentTurnColour(): void {
		// White moves first in a new game
		$board = new Board();
		self::assertSame(Colour::White, $board->getCurrentTurnColour());

		// Black to move after White moves
		$board->movePiece(3, 1, 3, 3); // d4
		self::assertSame(Colour::Black, $board->getCurrentTurnColour());
	}

	#[TestWith([0, 0, true])]
	#[TestWith([7, 7, true])]
	#[TestWith([-1, 0, false])]
	#[TestWith([0, -1, false])]
	#[TestWith([8, 0, false])]
	#[TestWith([0, 8, false])]
	public function test_isValidCoord(int $x, int $y, bool $valid): void {
		self::assertSame($valid, Board::isValidCoord($x, $y));
	}

	public function test_getPieces(): void {
		// New board should have all the standard pieces
		$board = new Board();
		self::assertEquals(ChessGame::getStandardGame(), $board->getPieces());

		// Getting only pieces of a specific colour should be consistent
		foreach (Colour::cases() as $colour) {
			foreach ($board->getPieces($colour) as $piece) {
				self::assertSame($colour, $piece->colour);
			}
		}
	}

	public function test_hasPiece(): void {
		$board = new Board();
		self::assertTrue($board->hasPiece(0, 1)); // a2
		self::assertFalse($board->hasPiece(0, 2)); // a3
	}

	public function test_getPiece(): void {
		// Get a piece that exists
		$board = new Board();
		$expected = new ChessPiece(Colour::White, ChessPiece::PAWN, 0, 1); // a2
		self::assertEquals($expected, $board->getPiece(0, 1));

		// Get an error if the piece doesn't exist
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No piece found on: 0, 2');
		$board->getPiece(0, 2); // a3
	}

	public function test_getKing(): void {
		$board = new Board();
		foreach (Colour::cases() as $colour) {
			$y = match ($colour) {
				Colour::White => 0,
				Colour::Black => 7,
			};
			$expected = new ChessPiece($colour, ChessPiece::KING, 4, $y);
			self::assertEquals($expected, $board->getKing($colour));
		}
	}

	public function test_canCastle_default(): void {
		// Can castle by default
		$board = new Board();
		foreach (Colour::cases() as $colour) {
			foreach (Castling::cases() as $type) {
				self::assertTrue($board->canCastle($colour, $type));
			}
		}
	}

	public function test_canCastle_move_king(): void {
		// Moving King disables all castling
		$board = new Board();
		$board->movePiece(4, 1, 4, 2); // e3
		$board->movePiece(4, 6, 4, 5); // e6
		$board->movePiece(4, 0, 4, 1); // Ke2
		$board->movePiece(4, 7, 4, 6); // Ke7
		foreach (Colour::cases() as $colour) {
			foreach (Castling::cases() as $type) {
				self::assertFalse($board->canCastle($colour, $type));
			}
		}
	}

	#[TestWith([Castling::Kingside])]
	#[TestWith([Castling::Queenside])]
	public function test_canCastle_move_rook(Castling $type): void {
		// Moving Rook disables castling only on that side
		$board = new Board();
		$x = match ($type) {
			Castling::Kingside => 7,
			Castling::Queenside => 0,
		};
		$otherType = match ($type) {
			Castling::Kingside => Castling::Queenside,
			Castling::Queenside => Castling::Kingside,
		};
		$board->movePiece($x, 1, $x, 2); // a3|h3
		$board->movePiece($x, 6, $x, 5); // a6|h6
		$board->movePiece($x, 0, $x, 1); // Ra2|Rh2
		$board->movePiece($x, 7, $x, 6); // Ra7|Rh7
		foreach (Colour::cases() as $colour) {
			self::assertFalse($board->canCastle($colour, $type));
			self::assertTrue($board->canCastle($colour, $otherType));
		}
	}

	public function test_getEnPassantPawn(): void {
		// No en passant pawn by default
		$board = new Board();
		$none = ['X' => -1, 'Y' => -1];
		self::assertSame($none, $board->getEnPassantPawn());

		// Double moving a pawn allows en passant
		$board->movePiece(0, 1, 0, 3); // a4
		$expected = ['X' => 0, 'Y' => 3];
		self::assertSame($expected, $board->getEnPassantPawn());

		// Moving any other piece declines en passant
		$board->movePiece(1, 7, 0, 5); // Na6
		self::assertSame($none, $board->getEnPassantPawn());

		// Single moving a pawn does not allow en passant
		$board->movePiece(1, 1, 1, 2); // b3
		self::assertSame($none, $board->getEnPassantPawn());
	}

	public function test_isChecked(): void {
		// Not in check by default
		$board = new Board();
		foreach (Colour::cases() as $colour) {
			self::assertFalse($board->isChecked($colour));
		}

		// Put the black king in check
		$board->movePiece(4, 1, 4, 2); // e3
		$board->movePiece(3, 6, 3, 5); // d6
		$board->movePiece(5, 0, 1, 4); // Bb5+
		self::assertTrue($board->isChecked(Colour::Black));
		self::assertFalse($board->isCheckmated(Colour::Black));
		self::assertFalse($board->isChecked(Colour::White));

		// Blocking the check results in no longer being checked
		$board->movePiece(3, 7, 3, 6); // Qd7
		foreach (Colour::cases() as $colour) {
			self::assertFalse($board->isChecked($colour));
		}
	}

	public function test_isCheckmated(): void {
		// Not in checkmate by default
		$board = new Board();
		foreach (Colour::cases() as $colour) {
			self::assertFalse($board->isCheckmated($colour));
		}

		// Scholar's Mate against Black
		$board->movePiece(4, 1, 4, 3); // e4
		$board->movePiece(4, 6, 4, 4); // e5
		$board->movePiece(5, 0, 2, 3); // Bc4
		$board->movePiece(1, 7, 2, 5); // Nc6
		$board->movePiece(3, 0, 7, 4); // Qh5
		$board->movePiece(6, 7, 5, 5); // Nf6
		$board->movePiece(7, 4, 5, 6); // Qxf7++
		self::assertTrue($board->isCheckmated(Colour::Black));
	}

	public function test_setSquare(): void {
		// Add a White Rook to d5
		$board = new Board();
		$piece = new ChessPiece(Colour::White, ChessPiece::ROOK, 0, 0);
		$board->setSquare(3, 4, $piece);

		// The piece is now set on the board
		self::assertSame($piece, $board->getPiece(3, 4));

		// The piece has its coords updates
		$expected = new ChessPiece(Colour::White, ChessPiece::ROOK, 3, 4);
		self::assertEquals($piece, $expected);
	}

	public function test_clearSquare(): void {
		$board = new Board();
		self::assertTrue($board->hasPiece(1, 0));
		$board->clearSquare(1, 0);
		self::assertFalse($board->hasPiece(1, 0));
	}

	public function test_movePiece_castling_kingside(): void {
		$board = new Board();
		// Make room for castling kinside
		$board->clearSquare(5, 0); // remove f1
		$board->clearSquare(6, 0); // remove g1
		$result = $board->movePiece(4, 0, 6, 0); // O-O
		$expected = [
			'Castling' => Castling::Kingside,
			'PieceTaken' => null,
			'EnPassant' => false,
			'PawnPromotion' => false,
		];
		self::assertSame($expected, $result);

		// Make sure pieces are in the right spot
		$expectedKing = new ChessPiece(Colour::White, ChessPiece::KING, 6, 0);
		self::assertEquals($expectedKing, $board->getPiece(6, 0));
		$expectedRook = new ChessPiece(Colour::White, ChessPiece::ROOK, 5, 0);
		self::assertEquals($expectedRook, $board->getPiece(5, 0));
		self::assertFalse($board->hasPiece(4, 0));
		self::assertFalse($board->hasPiece(7, 0));
	}

	public function test_movePiece_castling_queenside(): void {
		$board = new Board();
		// Make room for castling queenside
		$board->clearSquare(1, 0); // remove b1
		$board->clearSquare(2, 0); // remove c1
		$board->clearSquare(3, 0); // remove d1
		$result = $board->movePiece(4, 0, 2, 0); // O-O-O
		$expected = [
			'Castling' => Castling::Queenside,
			'PieceTaken' => null,
			'EnPassant' => false,
			'PawnPromotion' => false,
		];
		self::assertSame($expected, $result);

		// Make sure pieces are in the right spot
		$expectedKing = new ChessPiece(Colour::White, ChessPiece::KING, 2, 0);
		self::assertEquals($expectedKing, $board->getPiece(2, 0));
		$expectedRook = new ChessPiece(Colour::White, ChessPiece::ROOK, 3, 0);
		self::assertEquals($expectedRook, $board->getPiece(3, 0));
		self::assertFalse($board->hasPiece(0, 0));
		self::assertFalse($board->hasPiece(1, 0));
		self::assertFalse($board->hasPiece(4, 0));
	}

	public function test_movePiece_capturing(): void {
		$board = new Board();
		// Remove pawn on d2
		$board->clearSquare(3, 1);
		$capturee = $board->getPiece(3, 6); // pawn on d7
		$result = $board->movePiece(3, 0, 3, 6); // Qxd7
		$expected = [
			'Castling' => null,
			'PieceTaken' => $capturee,
			'EnPassant' => false,
			'PawnPromotion' => false,
		];
		self::assertSame($expected, $result);
	}

	public function test_movePiece_capturing_en_passant(): void {
		$board = new Board();
		$capturer = new ChessPiece(Colour::Black, ChessPiece::PAWN, 0, 0);
		$board->setSquare(3, 3, $capturer); // add Black pawn to d4
		$board->movePiece(4, 1, 4, 3); // e4
		$capturee = $board->getPiece(4, 3); // pawn on e4
		$result = $board->movePiece(3, 3, 4, 2); // dxe4 e.p.
		$expected = [
			'Castling' => null,
			'PieceTaken' => $capturee,
			'EnPassant' => true,
			'PawnPromotion' => false,
		];
		self::assertSame($expected, $result);

		// Make sure pieces are in the right spot
		$expectedPawn = new ChessPiece(Colour::Black, ChessPiece::PAWN, 4, 2);
		self::assertEquals($expectedPawn, $board->getPiece(4, 2));
		self::assertFalse($board->hasPiece(4, 3));
	}

	public function test_movePiece_promoting(): void {
		$board = new Board();
		// Clear path for pawn promotion
		$board->clearSquare(0, 6); // a7
		$board->clearSquare(0, 7); // a8
		// Add pawn to a7
		$pawn = new ChessPiece(Colour::White, ChessPiece::PAWN, 0, 0);
		$board->setSquare(0, 6, $pawn);
		$result = $board->movePiece(0, 6, 0, 7);
		$expected = [
			'Castling' => null,
			'PieceTaken' => null,
			'EnPassant' => false,
			'PawnPromotion' => true,
		];
		self::assertSame($expected, $result);

		// Make sure pieces are in the right spot
		$expectedQueen = new ChessPiece(Colour::White, ChessPiece::QUEEN, 0, 7);
		self::assertEquals($expectedQueen, $board->getPiece(0, 7));
	}

	public function test_movePiece_invalid_to_coord(): void {
		$board = new Board();
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid from coordinates, x=0, y=8');
		$board->movePiece(0, 8, 0, 7);
	}

	public function test_movePiece_invalid_from_coord(): void {
		$board = new Board();
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid to coordinates, x=0, y=8');
		$board->movePiece(0, 7, 0, 8);
	}

}
