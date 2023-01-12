<?php declare(strict_types=1);

namespace SmrTest\lib\Combat\Weapon;

use PHPUnit\Framework\TestCase;
use Smr\Combat\Weapon\ScoutDrones;

/**
 * @covers Smr\Combat\Weapon\ScoutDrones
 */
class ScoutDronesTest extends TestCase {

	public function test_getAmount(): void {
		$sds = new ScoutDrones(100);
		self::assertSame(100, $sds->getAmount());
	}

	public function test_getShieldDamage(): void {
		$sds = new ScoutDrones(100); // doesn't matter how many
		self::assertSame(20, $sds->getShieldDamage());
	}

	public function test_getArmourDamage(): void {
		$sds = new ScoutDrones(100); // doesn't matter how many
		self::assertSame(20, $sds->getShieldDamage());
	}

}
