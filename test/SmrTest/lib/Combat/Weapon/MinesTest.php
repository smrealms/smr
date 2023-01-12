<?php declare(strict_types=1);

namespace SmrTest\lib\Combat\Weapon;

use PHPUnit\Framework\TestCase;
use Smr\Combat\Weapon\Mines;

/**
 * @covers Smr\Combat\Weapon\Mines
 */
class MinesTest extends TestCase {

	public function test_getAmount(): void {
		$mines = new Mines(100);
		self::assertSame(100, $mines->getAmount());
	}

	public function test_getShieldDamage(): void {
		$mines = new Mines(100); // doesn't matter how many
		self::assertSame(20, $mines->getShieldDamage());
	}

	public function test_getArmourDamage(): void {
		$mines = new Mines(100); // doesn't matter how many
		self::assertSame(20, $mines->getShieldDamage());
	}

}
