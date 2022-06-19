<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use PHPUnit\Framework\TestCase;
use SmrMines;

/**
 * @covers SmrMines
 */
class SmrMinesTest extends TestCase {

	public function test_getAmount(): void {
		$mines = new SmrMines(100);
		self::assertSame(100, $mines->getAmount());
	}

	public function test_getShieldDamage(): void {
		$mines = new SmrMines(100); // doesn't matter how many
		self::assertSame(20, $mines->getShieldDamage());
	}

	public function test_getArmourDamage(): void {
		$mines = new SmrMines(100); // doesn't matter how many
		self::assertSame(20, $mines->getShieldDamage());
	}

}
