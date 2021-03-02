<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use SmrPlanet;
use SmrTest\BaseIntegrationSpec;

/**
 * Class SmrPlanetIntegrationTest
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
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Planet already exists');
		SmrPlanet::createPlanet(1, 1, 1, 1);
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

}
