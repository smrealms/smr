<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\HardwareType;

#[CoversClass(HardwareType::class)]
class HardwareTypeTest extends TestCase {

	public static function setUpBeforeClass(): void {
		// Make sure cache is clear so we can cover the cache population code
		HardwareType::clearCache();
	}

	public function test_get(): void {
		// Spot check one of the hardware types
		$expected = new HardwareType(HARDWARE_COMBAT, 'Combat Drones', 5000);
		self::assertEquals($expected, HardwareType::get(HARDWARE_COMBAT));
	}

}
