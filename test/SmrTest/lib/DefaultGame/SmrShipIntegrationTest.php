<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use SmrShip;
use AbstractSmrPlayer;
use SmrWeapon;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers SmrShip
 */
class SmrShipIntegrationTest extends BaseIntegrationSpec {

	private \PHPUnit\Framework\MockObject\MockObject $player;

	protected function setUp(): void {
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


	public function test_getShip(): void {
		// Get the ship associated with this player
		$original = SmrShip::getShip($this->player);
		self::assertSame($this->player->getAccountID(), $original->getAccountID());
		self::assertSame($this->player->getGameID(), $original->getGameID());
		self::assertSame($this->player->getShipTypeID(), $original->getTypeID());

		// Check that we get the exact same object if we get it again
		$forceUpdate = false;
		$ship = SmrShip::getShip($this->player, $forceUpdate);
		self::assertSame($original, $ship);

		// Check that we get a different object if we force update
		$forceUpdate = true;
		$ship = SmrShip::getShip($this->player, $forceUpdate);
		self::assertNotSame($original, $ship);
		// but it is still the same ship
		self::assertEquals($original, $ship);
	}


	public function test_updateHardware(): void {
		$original = SmrShip::getShip($this->player);

		// Add hardware
		$original->setHardwareToMax();
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = SmrShip::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Change some hardware
		$original->decreaseShields(10);
		$original->decreaseCDs(10);
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = SmrShip::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Remove hardware
		$original->removeAllHardware();
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = SmrShip::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);
	}


	public function test_updateWeapons(): void {
		$original = SmrShip::getShip($this->player);

		// Add a couple weapons
		$original->addWeapon(SmrWeapon::getWeapon(WEAPON_TYPE_LASER));
		$original->addWeapon(SmrWeapon::getWeapon(WEAPON_PORT_TURRET));
		$original->addWeapon(SmrWeapon::getWeapon(WEAPON_TYPE_LASER));
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = SmrShip::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Remove a weapon
		$original->removeWeapon(1);
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = SmrShip::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Remove all weapons
		$original->removeAllWeapons();
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = SmrShip::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);
	}


	public function test_updateCargo(): void {
		$original = SmrShip::getShip($this->player);
		$original->setHardwareToMax();

		// Add some cargo
		$original->increaseCargo(GOODS_ORE, 15);
		$original->increaseCargo(GOODS_WOOD, 5);
		$original->increaseCargo(GOODS_FOOD, 10);
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = SmrShip::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Modify existing cargo
		$original->decreaseCargo(GOODS_ORE, 5); // decrease
		$original->decreaseCargo(GOODS_WOOD, 5); // remove all
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = SmrShip::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Remove all cargo
		$original->removeAllCargo();
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = SmrShip::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);
	}


	public function test_updateCloak(): void {
		$original = SmrShip::getShip($this->player);
		$original->setHardwareToMax();

		// Enable cloak
		$original->enableCloak();
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = SmrShip::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Disable cloak
		$original->decloak();
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = SmrShip::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);
	}


	public function test_updateIllusion(): void {
		$original = SmrShip::getShip($this->player);
		$original->setHardwareToMax();

		// Enable illusion
		$original->setIllusion(SHIP_TYPE_DRUDGE, 2, 3);
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = SmrShip::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Change illusion
		$original->setIllusion(SHIP_TYPE_ROGUE, 5, 1);
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = SmrShip::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Disable illusion
		$original->disableIllusion();
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = SmrShip::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);
	}

}
