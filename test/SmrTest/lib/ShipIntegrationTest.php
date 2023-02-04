<?php declare(strict_types=1);

namespace SmrTest\lib;

use Smr\AbstractPlayer;
use Smr\Combat\Weapon\Weapon;
use Smr\Ship;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers Smr\Ship
 */
class ShipIntegrationTest extends BaseIntegrationSpec {

	private AbstractPlayer $player; // will be mocked

	protected function tablesToTruncate(): array {
		return ['ship_has_cargo', 'ship_has_hardware', 'ship_has_illusion', 'ship_has_weapon', 'ship_is_cloaked'];
	}

	protected function setUp(): void {
		// Start each test with an empty ship cache
		Ship::clearCache();

		// Create mock player that will be needed to create any ship
		$this->player = $this->createMock(AbstractPlayer::class);
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
		$original = Ship::getShip($this->player);
		self::assertSame($this->player->getAccountID(), $original->getAccountID());
		self::assertSame($this->player->getGameID(), $original->getGameID());
		self::assertSame($this->player->getShipTypeID(), $original->getTypeID());

		// Check that we get the exact same object if we get it again
		$forceUpdate = false;
		$ship = Ship::getShip($this->player, $forceUpdate);
		self::assertSame($original, $ship);

		// Check that we get a different object if we force update
		$forceUpdate = true;
		$ship = Ship::getShip($this->player, $forceUpdate);
		self::assertNotSame($original, $ship);
		// but it is still the same ship
		self::assertEquals($original, $ship);
	}

	public function test_updateHardware(): void {
		$original = Ship::getShip($this->player);

		// Add hardware
		$original->setHardwareToMax();
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = Ship::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Change some hardware
		$original->decreaseShields(10);
		$original->decreaseCDs(10);
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = Ship::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Remove hardware
		$original->removeAllHardware();
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = Ship::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);
	}

	public function test_updateWeapons(): void {
		$original = Ship::getShip($this->player);

		// Add a couple weapons
		$original->addWeapon(Weapon::getWeapon(WEAPON_TYPE_LASER));
		$original->addWeapon(Weapon::getWeapon(WEAPON_PORT_TURRET));
		$original->addWeapon(Weapon::getWeapon(WEAPON_TYPE_LASER));
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = Ship::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Remove a weapon
		$original->removeWeapon(1);
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = Ship::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Remove all weapons
		$original->removeAllWeapons();
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = Ship::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);
	}

	public function test_updateCargo(): void {
		$original = Ship::getShip($this->player);
		$original->setHardwareToMax();

		// Add some cargo
		$original->increaseCargo(GOODS_ORE, 15);
		$original->increaseCargo(GOODS_WOOD, 5);
		$original->increaseCargo(GOODS_FOOD, 10);
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = Ship::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Modify existing cargo
		$original->decreaseCargo(GOODS_ORE, 5); // decrease
		$original->decreaseCargo(GOODS_WOOD, 5); // remove all
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = Ship::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Remove all cargo
		$original->removeAllCargo();
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = Ship::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);
	}

	public function test_updateCloak(): void {
		$original = Ship::getShip($this->player);
		$original->setHardwareToMax();

		// Enable cloak
		$original->enableCloak();
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = Ship::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Disable cloak
		$original->decloak();
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = Ship::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);
	}

	public function test_updateIllusion(): void {
		$original = Ship::getShip($this->player);
		$original->setHardwareToMax();

		// Enable illusion
		$original->setIllusion(SHIP_TYPE_DRUDGE, 2, 3);
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = Ship::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Change illusion
		$original->setIllusion(SHIP_TYPE_ROGUE, 5, 1);
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = Ship::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);

		// Disable illusion
		$original->disableIllusion();
		$original->update();

		// Check that the reloaded ship is equal to the original
		$ship = Ship::getShip($this->player, true);
		self::assertNotSame($original, $ship);
		self::assertEquals($original, $ship);
	}

}
