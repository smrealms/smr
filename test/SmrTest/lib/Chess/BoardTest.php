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
use Smr\Chess\Loc;

#[CoversClass(Board::class)]
class BoardTest extends TestCase {

	public function test_getFEN(): void {
		// Note that this does not test castling, promotion, or non-e.p.
		$board = new Board();
		$board->movePiece(Loc::at('g1'), Loc::at('f3')); // Nf3 (for space in back row)
		$board->movePiece(Loc::at('d7'), Loc::at('d5')); // d5 (for space in pawn row, and e.p.)
		$expected = 'rnbqkbnr/ppp1pppp/8/3p4/8/5N2/PPPPPPPP/RNBQKB1R w KQkq d6 0 1';
		self::assertSame($expected, $board->getFEN());
	}

	public function test_getCurrentTurnColour(): void {
		// White moves first in a new game
		$board = new Board();
		self::assertSame(Colour::White, $board->getCurrentTurnColour());

		// Black to move after White moves
		$board->movePiece(Loc::at('d2'), Loc::at('d4')); // d4
		self::assertSame(Colour::Black, $board->getCurrentTurnColour());
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
		self::assertTrue($board->hasPiece(Loc::at('a2')));
		self::assertFalse($board->hasPiece(Loc::at('a3')));
	}

	public function test_getPiece(): void {
		// Get a piece that exists
		$board = new Board();
		$expected = new ChessPiece(Colour::White, ChessPiece::PAWN, Loc::at('a2'));
		self::assertEquals($expected, $board->getPiece(Loc::at('a2')));

		// Get an error if the piece doesn't exist
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No piece found on: a3');
		$board->getPiece(Loc::at('a3'));
	}

	public function test_getPieceOrNull(): void {
		$board = new Board();
		$expected = new ChessPiece(Colour::White, ChessPiece::PAWN, Loc::at('a2'));
		self::assertEquals($expected, $board->getPieceOrNull(Loc::at('a2')));

		// Get null if the piece doesn't exist
		self::assertNull($board->getPieceOrNull(Loc::at('a3')));
	}

	public function test_getKing(): void {
		$board = new Board();
		foreach (Colour::cases() as $colour) {
			$y = match ($colour) {
				Colour::White => 0,
				Colour::Black => 7,
			};
			$expected = new ChessPiece($colour, ChessPiece::KING, new Loc(4, $y));
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

	public function test_setCastling(): void {
		$board = new Board();
		$board->setCastling(Colour::Black, [Castling::Queenside]);
		self::assertFalse($board->canCastle(Colour::Black, Castling::Kingside));
	}

	public function test_canCastle_move_king(): void {
		// Moving King disables all castling
		$board = new Board();
		$board->movePiece(Loc::at('e2'), Loc::at('e3')); // e3
		$board->movePiece(Loc::at('e7'), Loc::at('e6')); // e6
		$board->movePiece(Loc::at('e1'), Loc::at('e2')); // Ke2
		$board->movePiece(Loc::at('e8'), Loc::at('e7')); // Ke7
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
		$board->movePiece(new Loc($x, 1), new Loc($x, 2)); // a3|h3
		$board->movePiece(new Loc($x, 6), new Loc($x, 5)); // a6|h6
		$board->movePiece(new Loc($x, 0), new Loc($x, 1)); // Ra2|Rh2
		$board->movePiece(new Loc($x, 7), new Loc($x, 6)); // Ra7|Rh7
		foreach (Colour::cases() as $colour) {
			self::assertFalse($board->canCastle($colour, $type));
			self::assertTrue($board->canCastle($colour, $otherType));
		}
	}

	public function test_getEnPassantPawn(): void {
		// No en passant pawn by default
		$board = new Board();
		self::assertNull($board->getEnPassantPawn());

		// Double moving a pawn allows en passant
		$board->movePiece(Loc::at('a2'), Loc::at('a4')); // a4
		$expected = Loc::at('a4');
		self::assertEquals($expected, $board->getEnPassantPawn());

		// Moving any other piece declines en passant
		$board->movePiece(Loc::at('b8'), Loc::at('a6')); // Na6
		self::assertNull($board->getEnPassantPawn());

		// Single moving a pawn does not allow en passant
		$board->movePiece(Loc::at('b2'), Loc::at('b3')); // b3
		self::assertNull($board->getEnPassantPawn());
	}

	public function test_setEnPassantPawn(): void {
		$board = new Board();
		$enPassantPawn = Loc::at('d5');
		$board->setEnPassantPawn($enPassantPawn);
		self::assertSame($enPassantPawn, $board->getEnPassantPawn());

		// Now reset it
		$board->setEnPassantPawn(null);
		self::assertNull($board->getEnPassantPawn());
	}

	public function test_isChecked(): void {
		// Not in check by default
		$board = new Board();
		foreach (Colour::cases() as $colour) {
			self::assertFalse($board->isChecked($colour));
		}

		// Put the black king in check
		$board->movePiece(Loc::at('e2'), Loc::at('e3')); // e3
		$board->movePiece(Loc::at('d7'), Loc::at('d6')); // d6
		$board->movePiece(Loc::at('f1'), Loc::at('b5')); // Bb5+
		self::assertTrue($board->isChecked(Colour::Black));
		self::assertFalse($board->isCheckmated(Colour::Black));
		self::assertFalse($board->isChecked(Colour::White));

		// Blocking the check results in no longer being checked
		$board->movePiece(Loc::at('d8'), Loc::at('d7')); // Qd7
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
		$board->movePiece(Loc::at('e2'), Loc::at('e4')); // e4
		$board->movePiece(Loc::at('e7'), Loc::at('e5')); // e5
		$board->movePiece(Loc::at('f1'), Loc::at('c4')); // Bc4
		$board->movePiece(Loc::at('b8'), Loc::at('c6')); // Nc6
		$board->movePiece(Loc::at('d1'), Loc::at('h5')); // Qh5
		$board->movePiece(Loc::at('g8'), Loc::at('f6')); // Nf6
		$board->movePiece(Loc::at('h5'), Loc::at('f7')); // Qxf7++
		self::assertTrue($board->isCheckmated(Colour::Black));
	}

	public function test_isDraw_not_by_default(): void {
		// Not draw by default
		$board = new Board();
		foreach (Colour::cases() as $colour) {
			self::assertFalse($board->isDraw($colour));
		}
	}

	public function test_isDraw_stalemate(): void {
		$board = new Board();
		$board->clear();

		// Black King on a1
		$board->setSquare(Loc::at('a1'), new ChessPiece(Colour::Black, ChessPiece::KING, new Loc(0, 0)));
		// White Queen on b3
		$board->setSquare(Loc::at('b3'), new ChessPiece(Colour::White, ChessPiece::QUEEN, new Loc(0, 0)));

		// Position is stalemate: Black has no valid moves and is not in check
		self::assertTrue($board->isDraw(Colour::Black));
	}

	public function test_isDraw_insufficient_material(): void {
		$board = new Board();
		$board->clear();

		// Black King on a1
		$board->setSquare(Loc::at('a1'), new ChessPiece(Colour::Black, ChessPiece::KING, new Loc(0, 0)));
		// White King on a3
		$board->setSquare(Loc::at('a3'), new ChessPiece(Colour::White, ChessPiece::KING, new Loc(0, 0)));

		// Insufficient material draw doesn't depend on player turn
		foreach (Colour::cases() as $colour) {
			self::assertTrue($board->isDraw($colour));
		}
	}

	public function test_setSquare(): void {
		// Add a White Rook to d5
		$board = new Board();
		$d5 = Loc::at('d5');
		$piece = new ChessPiece(Colour::White, ChessPiece::ROOK, new Loc(0, 0));
		self::assertNotEquals($d5, $piece->loc);
		$board->setSquare($d5, $piece);

		// The piece is now set on the board
		self::assertSame($piece, $board->getPiece($d5));

		// The piece has its coords updates
		$expected = new ChessPiece(Colour::White, ChessPiece::ROOK, $d5);
		self::assertEquals($piece, $expected);
	}

	public function test_clearSquare(): void {
		$board = new Board();
		$b1 = Loc::at('b1');
		self::assertTrue($board->hasPiece($b1));
		$board->clearSquare($b1);
		self::assertFalse($board->hasPiece($b1));
	}

	public function test_clear(): void {
		$board = new Board();
		$board->clear();
		self::assertEquals($board->getPieces(), []);
		foreach (Castling::cases() as $castling) {
			foreach (Colour::cases() as $colour) {
				self::assertFalse($board->canCastle($colour, $castling));
			}
		}
	}

	public function test_movePiece_castling_kingside(): void {
		$board = new Board();
		$e1 = Loc::at('e1');
		$f1 = Loc::at('f1');
		$g1 = Loc::at('g1');
		// Make room for castling kinside
		$board->clearSquare($f1); // remove f1
		$board->clearSquare($g1); // remove g1
		$result = $board->movePiece($e1, $g1); // O-O
		$expected = [
			'Castling' => Castling::Kingside,
			'PieceTaken' => null,
			'EnPassant' => false,
			'PawnPromotion' => false,
		];
		self::assertSame($expected, $result);

		// Make sure pieces are in the right spot
		$expectedKing = new ChessPiece(Colour::White, ChessPiece::KING, $g1);
		self::assertEquals($expectedKing, $board->getPiece($g1));
		$expectedRook = new ChessPiece(Colour::White, ChessPiece::ROOK, $f1);
		self::assertEquals($expectedRook, $board->getPiece($f1));
		self::assertFalse($board->hasPiece($e1)); // K original square
		self::assertFalse($board->hasPiece(Loc::at('h1'))); // R original square
	}

	public function test_movePiece_castling_queenside(): void {
		$board = new Board();
		$b1 = Loc::at('b1');
		$c1 = Loc::at('c1');
		$d1 = Loc::at('d1');
		$e1 = Loc::at('e1');
		// Make room for castling queenside
		$board->clearSquare($b1); // remove b1
		$board->clearSquare($c1); // remove c1
		$board->clearSquare($d1); // remove d1
		$result = $board->movePiece($e1, $c1); // O-O-O
		$expected = [
			'Castling' => Castling::Queenside,
			'PieceTaken' => null,
			'EnPassant' => false,
			'PawnPromotion' => false,
		];
		self::assertSame($expected, $result);

		// Make sure pieces are in the right spot
		$expectedKing = new ChessPiece(Colour::White, ChessPiece::KING, $c1);
		self::assertEquals($expectedKing, $board->getPiece($c1));
		$expectedRook = new ChessPiece(Colour::White, ChessPiece::ROOK, $d1);
		self::assertEquals($expectedRook, $board->getPiece($d1));
		self::assertFalse($board->hasPiece(Loc::at('a1')));
		self::assertFalse($board->hasPiece($b1));
		self::assertFalse($board->hasPiece($e1));
	}

	public function test_movePiece_capturing(): void {
		$board = new Board();
		$d7 = Loc::at('d7');
		// Remove pawn on d2
		$board->clearSquare(Loc::at('d2'));
		$capturee = $board->getPiece($d7); // pawn on d7
		$result = $board->movePiece(Loc::at('d1'), $d7); // Qxd7
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
		$d4 = Loc::at('d4');
		$e4 = Loc::at('e4');
		$e3 = Loc::at('e3');
		$capturer = new ChessPiece(Colour::Black, ChessPiece::PAWN, new Loc(0, 0));
		$board->setSquare($d4, $capturer); // add Black pawn to d4
		$board->movePiece(Loc::at('e2'), $e4); // e4
		$capturee = $board->getPiece($e4); // pawn on e4
		$result = $board->movePiece($d4, $e3); // dxe4 e.p.
		$expected = [
			'Castling' => null,
			'PieceTaken' => $capturee,
			'EnPassant' => true,
			'PawnPromotion' => false,
		];
		self::assertSame($expected, $result);

		// Make sure pieces are in the right spot
		$expectedPawn = new ChessPiece(Colour::Black, ChessPiece::PAWN, $e3);
		self::assertEquals($expectedPawn, $board->getPiece($e3));
		self::assertFalse($board->hasPiece($e4));
	}

	public function test_movePiece_promoting(): void {
		$board = new Board();
		$a7 = Loc::at('a7');
		$a8 = Loc::at('a8');
		// Clear path for pawn promotion
		$board->clearSquare($a7); // a7
		$board->clearSquare($a8); // a8
		// Add pawn to a7
		$pawn = new ChessPiece(Colour::White, ChessPiece::PAWN, new Loc(0, 0));
		$board->setSquare($a7, $pawn);
		$result = $board->movePiece($a7, $a8);
		$expected = [
			'Castling' => null,
			'PieceTaken' => null,
			'EnPassant' => false,
			'PawnPromotion' => true,
		];
		self::assertSame($expected, $result);

		// Make sure pieces are in the right spot
		$expectedQueen = new ChessPiece(Colour::White, ChessPiece::QUEEN, $a8);
		self::assertEquals($expectedQueen, $board->getPiece($a8));
	}

}
