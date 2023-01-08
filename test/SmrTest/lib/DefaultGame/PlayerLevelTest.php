<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Exception;
use PHPUnit\Framework\TestCase;
use Smr\PlayerLevel;

/**
 * @covers Smr\PlayerLevel
 */
class PlayerLevelTest extends TestCase {

	public static function setUpBeforeClass(): void {
		// Make sure cache is clear so we can cover the cache population code
		PlayerLevel::clearCache();
	}

	public function test_get(): void {
		// Test that we calculate level from exp properly
		$exp = 49240;
		$level = PlayerLevel::get($exp);
		$expected = new PlayerLevel(22, 'Lieutenant 2nd Class', 44765);
		self::assertEquals($expected, $level);
		self::assertGreaterThanOrEqual($level->expRequired, $exp); // B >= A

		// Make sure the next level has more exp
		self::assertLessThan($level->next()->expRequired, $exp); // B < A
	}

	public function test_get_invalid_exp(): void {
		// If we pass an invalid amount of exp, we throw
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Failed to properly determine level from exp: -1');
		PlayerLevel::get(-1);
	}

	public function test_getMax(): void {
		self::assertSame(50, PlayerLevel::getMax());
	}

	/**
	 * @testWith [1, 2]
	 *           [49, 50]
	 *           [50, 50]
	 */
	public function test_next(int $levelID, int $nextLevelID): void {
		$level = new PlayerLevel($levelID, '', 0);
		self::assertSame($nextLevelID, $level->next()->id);
	}

}
