<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use SmrMines;

/**
 * @covers SmrMines
 */
class SmrMinesTest extends \PHPUnit\Framework\TestCase {

	public function test_getMaxDamage() {
		$mines = new SmrMines(100); // doesn't matter how many
		$this->assertSame(20, $mines->getMaxDamage());
	}

}
