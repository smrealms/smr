<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use PHPUnit\Framework\TestCase;
use Smr\ShipClass;

/**
 * @covers Smr\ShipClass
 */
class ShipClassTest extends TestCase {

	public function test_getName(): void {
		$this->assertSame('Trader', ShipClass::getName(2));
	}

	public function test_getAllNames(): void {
		$this->assertSame('Trader', ShipClass::getAllNames()[2]);
	}

}
