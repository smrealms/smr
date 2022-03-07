<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Smr\Path;

/**
 * @covers Smr\Path
 */
class PathTest extends \PHPUnit\Framework\TestCase {

	private static function make_complex_path(): Path {
		// Create a path with multiple links and warps
		$path = new Path(1);
		$path->addLink(2);
		$path->addLink(3);
		$path->addWarp(100);
		$path->addLink(101);
		$path->addWarp(200);
		return $path;
	}

	public function test_constructor(): void {
		// Constructor creates a 1-element path
		$path = new Path(42);
		self::assertSame([42], $path->getPath());
	}

	public function test_getLength(): void {
		// Length should always be one less than the path size
		$path = new Path(1);
		self::assertSame(0, $path->getLength());
		$path->addLink(2);
		self::assertSame(1, $path->getLength());
	}

	public function test_isInPath(): void {
		// Test if sector IDs are in the path
		$path = new Path(42);
		self::assertTrue($path->isInPath(42));
		self::assertFalse($path->isInPath(43));
	}

	public function test_addLink(): void {
		// Links increase the distance and turns by 1
		$path = new Path(1);
		$path->addLink(2);
		self::assertSame([1, 2], $path->getPath());
		self::assertSame(1, $path->getTurns());
		self::assertSame(1, $path->getDistance());
	}

	public function test_addWarp(): void {
		// Warps increase the distance and turns by 5
		$path = new Path(1);
		$path->addWarp(100);
		self::assertSame([1, 100], $path->getPath());
		self::assertSame(5, $path->getTurns());
		self::assertSame(5, $path->getDistance());
		self::assertSame(1, $path->getNumWarps());
	}

	public function test_getStartSectorID(): void {
		// Check the start sector of a 2-sector path
		$path = new Path(1);
		$path->addLink(2);
		self::assertSame(1, $path->getStartSectorID());
	}

	public function test_getNextOnPath(): void {
		// Check the next sector of a 3-sector path
		$path = new Path(1);
		$path->addLink(2);
		$path->addLink(3);
		self::assertSame(2, $path->getNextOnPath());
	}

	public function test_getEndSectorID(): void {
		// Check the end sector of a 3-sector path
		$path = new Path(1);
		$path->addLink(2);
		$path->addLink(3);
		self::assertSame(3, $path->getEndSectorID());
	}

	public function test_followPath(): void {
		// Check that following a path updates both the path array and
		// the warps bookkeeping
		$path = self::make_complex_path();
		$path->followPath();
		self::assertSame([2, 3, 100, 101, 200], $path->getPath());
		self::assertSame(2, $path->getNumWarps());
		$path->followPath();
		self::assertSame([3, 100, 101, 200], $path->getPath());
		self::assertSame(2, $path->getNumWarps());
		$path->followPath();
		self::assertSame([100, 101, 200], $path->getPath());
		self::assertSame(1, $path->getNumWarps());
	}

	public function test_skipToSector(): void {
		// Check that skipping to a sector in the middle of a path updates
		// both the path array and the warps bookkeeping
		$path = self::make_complex_path();
		$path->skipToSector(100);
		self::assertSame([100, 101, 200], $path->getPath());
		self::assertSame(1, $path->getNumWarps());
	}

	public function test_skipToSector_not_in_path(): void {
		// Try to skip to a sector that isn't in the path
		$path = new Path(1);
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Cannot skip to sector not in path!');
		$path->skipToSector(2);
	}

	public function test_reversePath(): void {
		// Try reversing a path with multiple links and warps
		$path = self::make_complex_path();
		$path->reversePath();
		self::assertSame([200, 101, 100, 3, 2, 1], $path->getPath());

		// The distance and number of warps has not changed
		self::assertSame(13, $path->getDistance());
		self::assertSame(2, $path->getNumWarps());

		// Only good way to test that the warp map was reversed is to
		// check the distance after following the path.
		$path->followPath();
		self::assertSame([101, 100, 3, 2, 1], $path->getPath());
		self::assertSame(1, $path->getNumWarps());
	}

}
