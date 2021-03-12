<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use SmrShip;
use AbstractSmrPlayer;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers SmrShip
 */
class SmrShipIntegrationTest extends BaseIntegrationSpec {

	private $player;

	protected function setUp() : void {
		// Start each test with an empty ship cache
		SmrShip::clearCache();

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

	public function test_getShip() {
		// Get the ship associated with this player
		$original = SmrShip::getShip($this->player);
		self::assertSame($this->player->getAccountID(), $original->getAccountID());
		self::assertSame($this->player->getGameID(), $original->getGameID());
		self::assertSame($this->player->getShipTypeID(), $original->getShipTypeID());

		// Check that we get the exact same object if we get it again
		$forceUpdate = false;
		$ship = SmrShip::getShip($this->player, $forceUpdate);
		self::assertSame($original, $ship);

		// Check that we get a different object if we force update
		$forceUpdate = true;
		$ship = SmrShip::getShip($this->player, $forceUpdate);
		self::assertNotSame($original, $ship);
		// but it is still the same ship
		self::assertSame($original->getGameID(), $ship->getGameID());
		self::assertSame($original->getAccountID(), $ship->getAccountID());
		self::assertSame($original->getShipTypeID(), $ship->getShipTypeID());
	}

	public function test_cloak() {
		$ship = SmrShip::getShip($this->player);

		// ships are initially uncloaked
		self::assertFalse($ship->isCloaked());

		// remain uncloaked when enabled without hardware
		// (note that this doesn't check hardware...)
		$ship->enableCloak();
		self::assertFalse($ship->isCloaked());
		// remain uncloaked when disabled without hardware
		$ship->decloak();
		self::assertFalse($ship->isCloaked());

		// add cloak hardware
		$ship->increaseHardware(HARDWARE_CLOAK, 1);
		self::assertFalse($ship->isCloaked());
		// enable
		$ship->enableCloak();
		self::assertTrue($ship->isCloaked());
		// disable
		$ship->decloak();
		self::assertFalse($ship->isCloaked());
	}

	public function test_illusion_generator() {
		$ship = SmrShip::getShip($this->player);

		// ship has no IG initially
		self::assertFalse($ship->getIllusionShip());

		// still no illusion when set without hardware
		// (note that this doesn't check hardware...)
		$ship->setIllusion(SHIP_TYPE_THIEF, 12, 13);
		self::assertFalse($ship->getIllusionShip());
		// remain unset when disabled without hardware
		$ship->disableIllusion();
		self::assertFalse($ship->getIllusionShip());

		// add IG hardware
		$ship->increaseHardware(HARDWARE_ILLUSION, 1);
		self::assertFalse($ship->getIllusionShip());
		// enable
		$ship->setIllusion(SHIP_TYPE_THIEF, 12, 13);
		$expected = [
			'ID' => SHIP_TYPE_THIEF,
			'Attack' => 12,
			'Defense' => 13,
			'Name' => 'Thief',
		];
		self::assertSame($expected, $ship->getIllusionShip());
		// disable
		$ship->disableIllusion();
		self::assertFalse($ship->getIllusionShip());
	}

}
