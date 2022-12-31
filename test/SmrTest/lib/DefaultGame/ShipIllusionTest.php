<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use PHPUnit\Framework\TestCase;
use Smr\ShipIllusion;

/**
 * @covers Smr\ShipIllusion
 */
class ShipIllusionTest extends TestCase {

	public function test_getName(): void {
		// Spot check a random ship type
		$illusion = new ShipIllusion(SHIP_TYPE_THIEF, 0, 0);
		self::assertSame('Thief', $illusion->getName());
	}

}
