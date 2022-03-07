<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use PHPUnit\Framework\TestCase;
use SmrMines;

/**
 * @covers SmrMines
 */
class SmrMinesTest extends TestCase {

	public function test_getMaxDamage(): void {
		$mines = new SmrMines(100); // doesn't matter how many
		$this->assertSame(20, $mines->getMaxDamage());
	}

}
