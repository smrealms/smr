<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Smr\ShipClass;

/**
 * @covers Smr\ShipClass
 */
class ShipClassTest extends \PHPUnit\Framework\TestCase {

	public function test_getName() {
		$this->assertSame('Trader', ShipClass::getName(2));
	}

	public function test_getAllNames() {
		$this->assertSame('Trader', ShipClass::getAllNames()[2]);
	}

}
