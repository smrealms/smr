<?php declare(strict_types=1);

namespace SmrTest\lib\Chess;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\Chess\Loc;

#[CoversClass(Loc::class)]
class LocTest extends TestCase {

	#[TestWith([1, 2, true])]
	#[TestWith([1, 3, false])]
	#[TestWith([2, 2, false])]
	#[TestWith([2, 3, false])]
	public function test_same(int $x, int $y, bool $same): void {
		$loc1 = new Loc(1, 2);
		$loc2 = Loc::validate($x, $y);
		self::assertSame($same, $loc1->same($loc2));
		self::assertSame($same, $loc2->same($loc1));
	}

	public function test_relative(): void {
		$loc = new Loc(1, 2);
		$expected = new Loc(0, 4);
		self::assertEquals($loc->relativeOrNull(-1, 2), $expected);
		self::assertEquals($loc->relative(-1, 2), $expected);
	}

	#[TestWith([-1, 0])]
	#[TestWith([0, -1])]
	#[TestWith([Loc::MAX_X + 1, 0])]
	#[TestWith([0, Loc::MAX_Y + 1])]
	public function test_relative_out_of_bounds(int $dx, int $dy): void {
		$loc = new Loc(0, 0);
		self::assertNull($loc->relativeOrNull($dx, $dy));

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid position: ' . $dx . ',' . $dy);
		$loc->relative($dx, $dy);
	}

	#[TestWith([0, 7, 'a8'])]
	#[TestWith([7, 0, 'h1'])]
	#[TestWith([3, 2, 'd3'])]
	public function test_algebraic(int $x, int $y, string $expected): void {
		$loc = Loc::validate($x, $y);
		self::assertSame($loc->algebraic(), $expected);
		self::assertEquals(Loc::at($expected), $loc);
	}

	#[TestWith([0, 0])]
	#[TestWith([0, Loc::MAX_Y])]
	#[TestWith([Loc::MAX_X, 0])]
	#[TestWith([Loc::MAX_X, Loc::MAX_Y])]
	public function test_validate(int $x, int $y): void {
		self::assertNotNull(Loc::validateOrNull($x, $y));
		$loc = Loc::validate($x, $y);
		self::assertSame($x, $loc->x);
		self::assertSame($y, $loc->y);
	}

	#[TestWith([-1, 0])]
	#[TestWith([0, -1])]
	#[TestWith([Loc::MAX_X + 1, 0])]
	#[TestWith([0, Loc::MAX_Y + 1])]
	public function test_validate_invalid(int $x, int $y): void {
		self::assertNull(Loc::validateOrNull($x, $y));
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid position: ' . $x . ',' . $y);
		Loc::validate($x, $y);
	}

	public function test_at_invalid(): void {
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid coord given: a');
		Loc::at('a');
	}

}
