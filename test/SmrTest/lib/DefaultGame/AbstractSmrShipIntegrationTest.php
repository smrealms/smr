<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use SmrShip;
use AbstractSmrPlayer;

/**
 * This test is expected to not make any changes to the database.
 * @covers AbstractSmrShip
 */
class AbstractSmrShipIntegrationTest extends \PHPUnit\Framework\TestCase {

	private $player;

	protected function setUp() : void {
		// Create mock player that will be needed to create any ship
		$this->player = $this->createMock(AbstractSmrPlayer::class);
		$this->player
			->method('getAccountID')
			->willReturn(7);
		$this->player
			->method('getGameID')
			->willReturn(3);
		// Use Demonica because it's the only ship with all special hardware
		$this->player
			->method('getShipTypeID')
			->willReturn(SHIP_TYPE_DEMONICA);
	}

	public function test_base_ship_properties_are_set_correctly() {
		$ship = SmrShip::getShip($this->player);
		self::assertSame("Demonica", $ship->getName());
		self::assertSame(SHIP_TYPE_DEMONICA, $ship->getShipTypeID());
		self::assertSame(AbstractSmrShip::SHIP_CLASS_HUNTER, $ship->getShipClassID());
		self::assertSame(6, $ship->getHardpoints());
		self::assertSame(10, $ship->getSpeed());
		self::assertSame(0, $ship->getCost());
	}

}
