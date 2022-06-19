<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use PHPUnit\Framework\TestCase;
use SmrScoutDrones;

/**
 * @covers SmrScoutDrones
 */
class SmrScoutDronesTest extends TestCase {

	public function test_getAmount(): void {
		$sds = new SmrScoutDrones(100);
		self::assertSame(100, $sds->getAmount());
	}

	public function test_getShieldDamage(): void {
		$sds = new SmrScoutDrones(100); // doesn't matter how many
		self::assertSame(20, $sds->getShieldDamage());
	}

	public function test_getArmourDamage(): void {
		$sds = new SmrScoutDrones(100); // doesn't matter how many
		self::assertSame(20, $sds->getShieldDamage());
	}

}
