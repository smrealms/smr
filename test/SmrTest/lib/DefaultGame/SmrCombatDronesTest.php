<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use PHPUnit\Framework\TestCase;
use SmrCombatDrones;

/**
 * @covers SmrCombatDrones
 */
class SmrCombatDronesTest extends TestCase {

	public function test_getAmount(): void {
		$drones = new SmrCombatDrones(100);
		self::assertSame(100, $drones->getAmount());
	}

	public function test_getShieldDamage(): void {
		// regular drones
		$drones = new SmrCombatDrones(100); // doesn't matter how many
		self::assertSame(2, $drones->getShieldDamage());
		// port/planet drones
		$drones = new SmrCombatDrones(100, true);
		self::assertSame(1, $drones->getShieldDamage());
	}

	public function test_getArmourDamage(): void {
		// regular drones
		$drones = new SmrCombatDrones(100); // doesn't matter how many
		self::assertSame(2, $drones->getArmourDamage());
		// port/planet drones
		$drones = new SmrCombatDrones(100, true);
		self::assertSame(1, $drones->getArmourDamage());
	}

}
