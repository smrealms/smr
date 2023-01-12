<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame\Combat\Weapon;

use PHPUnit\Framework\TestCase;
use Smr\Combat\Weapon\CombatDrones;

/**
 * @covers Smr\Combat\Weapon\CombatDrones
 */
class CombatDronesTest extends TestCase {

	public function test_getAmount(): void {
		$drones = new CombatDrones(100);
		self::assertSame(100, $drones->getAmount());
	}

	public function test_getShieldDamage(): void {
		// regular drones
		$drones = new CombatDrones(100); // doesn't matter how many
		self::assertSame(2, $drones->getShieldDamage());
		// port/planet drones
		$drones = new CombatDrones(100, true);
		self::assertSame(1, $drones->getShieldDamage());
	}

	public function test_getArmourDamage(): void {
		// regular drones
		$drones = new CombatDrones(100); // doesn't matter how many
		self::assertSame(2, $drones->getArmourDamage());
		// port/planet drones
		$drones = new CombatDrones(100, true);
		self::assertSame(1, $drones->getArmourDamage());
	}

}
