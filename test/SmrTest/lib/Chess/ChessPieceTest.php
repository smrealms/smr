<?php declare(strict_types=1);

namespace SmrTest\lib\Chess;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\Chess\Board;
use Smr\Chess\ChessPiece;
use Smr\Chess\Loc;

#[CoversClass(ChessPiece::class)]
class ChessPieceTest extends TestCase {

	public function test_getPossibleMoves_initial(): void {
		// Check possible moves from a starting position
		$board = new Board();

		// Only pawns and knights have allowable moves
		$pawnW = $board->getPiece(Loc::at('a2'));
		$expected = [Loc::at('a3'), Loc::at('a4')];
		self::assertEquals($expected, $pawnW->getPossibleMoves($board));

		$pawnB = $board->getPiece(Loc::at('a7'));
		$expected = [Loc::at('a6'), Loc::at('a5')];
		self::assertEquals($expected, $pawnB->getPossibleMoves($board));

		$knight = $board->getPiece(Loc::at('b1'));
		$expected = [Loc::at('a3'), Loc::at('c3')];
		self::assertEquals($expected, $knight->getPossibleMoves($board));

		// These pieces don't have any moves at the start of the game
		foreach (['a1', 'c1', 'd1', 'e1'] as $coord) {
			$piece = $board->getPiece(Loc::at($coord));
			self::assertSame([], $piece->getPossibleMoves($board));
		}
	}

	public function test_getPossibleMoves_queen(): void {
		$board = new Board();
		$queen = $board->getPiece(Loc::at('d8'));
		$board->setSquare(Loc::at('d5'), $queen); // place queen in the open
		$board->clearSquare(Loc::at('d8')); // remove original queen
		$expected = [
			Loc::at('c5'), Loc::at('b5'), Loc::at('a5'), // left
			Loc::at('e5'), Loc::at('f5'), Loc::at('g5'), Loc::at('h5'), // right
			Loc::at('d6'), // up
			Loc::at('d4'), Loc::at('d3'), Loc::at('d2'), // down
			Loc::at('c4'), Loc::at('b3'), Loc::at('a2'), // down-left
			Loc::at('e4'), Loc::at('f3'), Loc::at('g2'), // down-right
			Loc::at('c6'), // up-left
			Loc::at('e6'), // up-right
		];
		self::assertEquals($expected, $queen->getPossibleMoves($board));
	}

	public function test_getPossibleMoves_king(): void {
		$board = new Board();
		$king = $board->getPiece(Loc::at('e1'));
		$board->setSquare(Loc::at('g6'), $king);
		$board->clearSquare(Loc::at('e1')); // remove original king
		$board->clearSquare(Loc::at('f8')); // remove enemy bishop
		$expected = [
			// can't move to h6 or f6 since pawns defend those squares
			// can't take pawns on h7 or f7 due to defending pieces
			Loc::at('f5'), Loc::at('g5'),
			Loc::at('g7'), // since bishop is not defending anymore
			Loc::at('h5'),
		];
		self::assertEquals($expected, $king->getPossibleMoves($board));
	}

}
