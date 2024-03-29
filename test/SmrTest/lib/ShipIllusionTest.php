<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\ShipIllusion;

#[CoversClass(ShipIllusion::class)]
class ShipIllusionTest extends TestCase {

	public function test_getName(): void {
		// Spot check a random ship type
		$illusion = new ShipIllusion(SHIP_TYPE_THIEF, 0, 0);
		self::assertSame('Thief', $illusion->getName());
	}

}
