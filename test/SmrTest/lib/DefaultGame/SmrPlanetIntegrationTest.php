<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use SmrPlanet;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers SmrPlanet
 */
class SmrPlanetIntegrationTest extends BaseIntegrationSpec {

	protected function tearDown() : void {
		SmrPlanet::clearCache();
		parent::tearDown();
	}

	public function test_createPlanet() : void {
		// Test arbitrary input
		$sectorID = 2;
		$gameID = 42;
		$typeID = 3;
		$inhabitableTime = 5;

		$planet = SmrPlanet::createPlanet($gameID, $sectorID, $typeID, $inhabitableTime);
		$this->assertTrue($planet->exists());

		// Check properties set explicitly
		$this->assertSame($gameID, $planet->getGameID());
		$this->assertSame($sectorID, $planet->getSectorID());
		$this->assertSame($typeID, $planet->getTypeID());
		$this->assertSame($inhabitableTime, $planet->getInhabitableTime());
	}

	public function test_createPlanet_already_exists() : void {
		SmrPlanet::createPlanet(1, 1, 1, 1);
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Planet already exists');
		SmrPlanet::createPlanet(1, 1, 1, 1);
	}

	public function test_removePlanet() : void {
		// Check that planet exists
		$planet = SmrPlanet::createPlanet(1, 1, 1, 1);
		$this->assertTrue($planet->exists());

		SmrPlanet::removePlanet(1, 1);
		$planet = SmrPlanet::getPlanet(1, 1, true);
		$this->assertFalse($planet->exists());
	}

	public function test_name() : void {
		$planet = SmrPlanet::createPlanet(1, 1, 1, 1);
		// Check default name
		$this->assertSame('Unknown', $planet->getDisplayName());

		// Set a new name (include non-HTML-safe character)
		$planet->setName('Test&');
		$this->assertSame('Test&amp;', $planet->getDisplayName());
	}

	public function test_owner() : void {
		// Check default owner
		$planet = SmrPlanet::createPlanet(1, 1, 1, 1);
		$this->assertFalse($planet->hasOwner());
		$this->assertSame(0, $planet->getOwnerID());

		// Set a new owner
		$ownerID = 3;
		$planet->setOwnerID($ownerID);
		$this->assertTrue($planet->hasOwner());
		$this->assertSame($ownerID, $planet->getOwnerID());

		// Remove the owner again
		$planet->removeOwner();
		$this->assertFalse($planet->hasOwner());
		$this->assertSame(0, $planet->getOwnerID());
	}

	public function test_password() : void {
		// Check default password
		$planet = SmrPlanet::createPlanet(1, 1, 1, 1);
		$this->assertSame('', $planet->getPassword());

		// Set a new password
		$password = 'test';
		$planet->setPassword($password);
		$this->assertSame($password, $planet->getPassword());

		// Remove the password again
		$planet->removePassword();
		$this->assertSame('', $planet->getPassword());
	}

	public function test_credits() : void {
		// Check default credits
		$planet = SmrPlanet::createPlanet(1, 1, 1, 1);
		$this->assertSame(0, $planet->getCredits());

		// Check increase/decrease credits
		$planet->increaseCredits(100);
		$this->assertSame(100, $planet->getCredits());
		$planet->increaseCredits(50);
		$this->assertSame(150, $planet->getCredits());
		$planet->decreaseCredits(50);
		$this->assertSame(100, $planet->getCredits());
	}

	public function test_bonds() : void {
		// Check default bond
		$planet = SmrPlanet::createPlanet(1, 1, 1, 1);
		$this->assertSame(0, $planet->getBonds());

		// Check increase/decrease bonds
		$planet->increaseBonds(100);
		$this->assertSame(100, $planet->getBonds());
		$planet->increaseBonds(50);
		$this->assertSame(150, $planet->getBonds());
		$planet->decreaseBonds(50);
		$this->assertSame(100, $planet->getBonds());
	}

	public function test_bond_maturity() : void {
		// Check default maturity
		$planet = SmrPlanet::createPlanet(1, 1, 1, 1);
		$this->assertSame(0, $planet->getMaturity());

		// Set a new bond maturity
		$maturity = time();
		$planet->setMaturity($maturity);
		$this->assertSame($maturity, $planet->getMaturity());
	}

	public function test_stockpile() : void {
		// Check default stockpile
		$planet = SmrPlanet::createPlanet(1, 1, 1, 1);
		$this->assertFalse($planet->hasStockpile());
		$this->assertSame([], $planet->getStockpile());
		foreach (array_keys(\Globals::getGoods()) as $goodID) {
			$this->assertFalse($planet->hasStockpile($goodID));
			$this->assertSame(0, $planet->getStockpile($goodID));
		}

		// Setting 0 still counts as empty
		$planet->setStockpile(GOODS_ORE, 0);
		$this->assertFalse($planet->hasStockpile());
		$this->assertFalse($planet->hasStockpile(GOODS_ORE));

		// Check increase stockpile
		$planet->increaseStockpile(GOODS_ORE, 50);
		$this->assertTrue($planet->hasStockpile());
		$this->assertSame([GOODS_ORE => 50], $planet->getStockpile());
		foreach (array_keys(\Globals::getGoods()) as $goodID) {
			if ($goodID === GOODS_ORE) {
				$this->assertTrue($planet->hasStockpile($goodID));
				$this->assertSame(50, $planet->getStockpile($goodID));
			} else {
				$this->assertFalse($planet->hasStockpile($goodID));
				$this->assertSame(0, $planet->getStockpile($goodID));
			}
		}

		// Check decrease stockpile
		$planet->decreaseStockpile(GOODS_ORE, 10);
		$this->assertTrue($planet->hasStockpile());
		$this->assertSame([GOODS_ORE => 40], $planet->getStockpile());
		foreach (array_keys(\Globals::getGoods()) as $goodID) {
			if ($goodID === GOODS_ORE) {
				$this->assertTrue($planet->hasStockpile($goodID));
				$this->assertSame(40, $planet->getStockpile($goodID));
			} else {
				$this->assertFalse($planet->hasStockpile($goodID));
				$this->assertSame(0, $planet->getStockpile($goodID));
			}
		}

		// Check remaining stockpile (ore: 600 - 40)
		$this->assertSame(560, $planet->getRemainingStockpile(GOODS_ORE));
	}

	public function test_setStockpile_throws_when_negative() : void {
		$planet = SmrPlanet::createPlanet(1, 1, 1, 1);
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Trying to set negative stockpile');
		$planet->setStockpile(GOODS_ORE, -20);
	}

	public function test_setBuilding_throws_when_negative() : void {
		$planet = SmrPlanet::createPlanet(1, 1, 1, 1);
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Cannot set negative number of buildings');
		$planet->setBuilding(PLANET_HANGAR, -1);
	}

	public function test_destroyBuilding_throws_when_invalid() : void {
		$planet = SmrPlanet::createPlanet(1, 1, 1, 1);
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Cannot set negative number of buildings');
		$planet->destroyBuilding(PLANET_TURRET, 1);
	}

	public function test_checkForDowngrade() : void {
		$planet = SmrPlanet::createPlanet(1, 1, 1, 1);

		// If we don't do enough damage, we should never downgrade
		$this->assertSame([], $planet->checkForDowngrade(0));

		// With no buildings, this should always return empty
		$this->assertSame([], $planet->checkForDowngrade(100 * SmrPlanet::DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE));

		// Give the planet 2 structures, and destroy them both
		$planet->setBuilding(PLANET_GENERATOR, 2);
		srand(95); // seed rand for reproducibility
		$result = $planet->checkForDowngrade(2 * SmrPlanet::DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE);
		$this->assertSame([PLANET_GENERATOR => 2], $result);
	}

	public function test_buildings() : void {
		$planet = SmrPlanet::createPlanet(1, 1, 1, 1);

		// Tests with no buildings
		$this->assertFalse($planet->hasBuilding(PLANET_HANGAR));
		$this->assertSame(0, $planet->getBuilding(PLANET_HANGAR));
		$this->assertSame(0.0, $planet->getLevel());

		// Add some hangars
		$planet->increaseBuilding(PLANET_HANGAR, 4);
		$this->assertTrue($planet->hasBuilding(PLANET_HANGAR));
		$this->assertSame(4, $planet->getBuilding(PLANET_HANGAR));
		$this->assertSame(4/3, $planet->getLevel());

		// Destroy some hangars
		$planet->destroyBuilding(PLANET_HANGAR, 2);
		$this->assertTrue($planet->hasBuilding(PLANET_HANGAR));
		$this->assertSame(2, $planet->getBuilding(PLANET_HANGAR));
		$this->assertSame(2/3, $planet->getLevel());
	}

	public function test_defenses() : void {
		// Make a Defense World planet
		$planet = SmrPlanet::createPlanet(1, 1, 4, 1);

		// Add buildings so that we can add defenses
		$planet->increaseBuilding(PLANET_GENERATOR, 1);
		$planet->increaseBuilding(PLANET_HANGAR, 1);
		$planet->increaseBuilding(PLANET_BUNKER, 1);

		// Make sure there are no defenses to start
		self::assertSame(0, $planet->getShields());
		self::assertFalse($planet->hasShields());
		self::assertSame(0, $planet->getCDs());
		self::assertFalse($planet->hasCDs());
		self::assertSame(0, $planet->getArmour());
		self::assertFalse($planet->hasArmour());

		// Increase shields
		$planet->increaseShields(10);
		self::assertSame(10, $planet->getShields());
		self::assertTrue($planet->hasShields());

		// Don't increase shields
		$planet->increaseShields(0);
		self::assertSame(10, $planet->getShields());

		// Decrease shields
		$planet->decreaseShields(2);
		self::assertSame(8, $planet->getShields());

		// Make sure we can't go above the Generator limit
		$planet->setShields(PLANET_GENERATOR_SHIELDS + 1);
		self::assertSame(PLANET_GENERATOR_SHIELDS, $planet->getShields());

		// Make sure we can't go below 0 shields
		$planet->setShields(-1);
		self::assertSame(0, $planet->getShields());

		// Increase CDs
		$planet->increaseCDs(5);
		self::assertSame(5, $planet->getCDs());
		self::assertTrue($planet->hasCDs());

		// Don't increase CDs
		$planet->increaseCDs(0);
		self::assertSame(5, $planet->getCDs());

		// Decrease CDs
		$planet->decreaseCDs(3);
		self::assertSame(2, $planet->getCDs());

		// Make sure we can't go above the Hangar limit
		$planet->setCDs(PLANET_HANGAR_DRONES + 1);
		self::assertSame(PLANET_HANGAR_DRONES, $planet->getCDs());

		// Make sure we can't go below 0 CDs
		$planet->setCDs(-1);
		self::assertSame(0, $planet->getCDs());

		// Increase armour
		$planet->increaseArmour(15);
		self::assertSame(15, $planet->getArmour());
		self::assertTrue($planet->hasArmour());

		// Don't increase armour
		$planet->increaseArmour(0);
		self::assertSame(15, $planet->getArmour());

		// Decrease armour
		$planet->decreaseArmour(4);
		self::assertSame(11, $planet->getArmour());

		// Make sure we can't go above the Bunker limit
		$planet->setArmour(PLANET_BUNKER_ARMOUR + 1);
		self::assertSame(PLANET_BUNKER_ARMOUR, $planet->getArmour());

		// Make sure we can't go below 0 armour
		$planet->setArmour(-1);
		self::assertSame(0, $planet->getArmour());
	}

}
