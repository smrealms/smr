<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use SmrCombatDrones;

/**
 * @covers SmrCombatDrones
 */
class SmrCombatDronesTest extends \PHPUnit\Framework\TestCase {

	public function test_getMaxDamage() {
		// regular drones
		$drones = new SmrCombatDrones(100); // doesn't matter how many
		$this->assertSame(2, $drones->getMaxDamage());
		// port/planet drones
		$drones = new SmrCombatDrones(100, true);
		$this->assertSame(1, $drones->getMaxDamage());
	}

}
