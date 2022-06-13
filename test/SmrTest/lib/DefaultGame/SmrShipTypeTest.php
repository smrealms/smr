<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use PHPUnit\Framework\TestCase;
use Smr\BuyerRestriction;
use Smr\ShipClass;
use SmrShipType;

/**
 * @covers SmrShipType
 */
class SmrShipTypeTest extends TestCase {

	public static function setUpBeforeClass(): void {
		// Ensure the cache has not been populated yet
		SmrShipType::clearCache();
	}

	public function test_one_ship_properties(): void {
		// Test all properties of one particular ship (Fed Ult)
		$shipType = SmrShipType::get(SHIP_TYPE_FEDERAL_ULTIMATUM);

		$this->assertSame(SHIP_TYPE_FEDERAL_ULTIMATUM, $shipType->getTypeID());
		$this->assertSame(ShipClass::RAIDER, $shipType->getClassID());
		$this->assertSame('Federal Ultimatum', $shipType->getName());
		$this->assertSame(38675738, $shipType->getCost());
		$this->assertSame(BuyerRestriction::Good, $shipType->getRestriction());
		$this->assertSame(8, $shipType->getSpeed());
		$this->assertSame(7, $shipType->getHardpoints());
		$this->assertSame(24, $shipType->getMaxPower());

		$hardware = [
			HARDWARE_SHIELDS => 700,
			HARDWARE_ARMOUR => 600,
			HARDWARE_CARGO => 120,
			HARDWARE_COMBAT => 120,
			HARDWARE_SCOUT => 15,
			HARDWARE_MINE => 0,
			HARDWARE_SCANNER => 1,
			HARDWARE_CLOAK => 0,
			HARDWARE_ILLUSION => 0,
			HARDWARE_JUMP => 1,
			HARDWARE_DCS => 0,
		];
		$this->assertSame($hardware, $shipType->getAllMaxHardware());
		foreach ($hardware as $hardwareID => $amount) {
			$this->assertSame($amount, $shipType->getMaxHardware($hardwareID));
		}
	}

	public function test_getAll_matches_get(): void {
		// Check that we get the same ship type from get and getAll
		$shipType1 = SmrShipType::get(SHIP_TYPE_GALACTIC_SEMI);
		$shipType2 = SmrShipType::getAll()[SHIP_TYPE_GALACTIC_SEMI];
		$this->assertSame($shipType1, $shipType2);
	}

	public function test_can_have_special_hardware(): void {
		// Demonica has all special hardware
		$shipType = SmrShipType::get(SHIP_TYPE_DEMONICA);
		$this->assertTrue($shipType->canHaveJump());
		$this->assertTrue($shipType->canHaveDCS());
		$this->assertTrue($shipType->canHaveScanner());
		$this->assertTrue($shipType->canHaveCloak());
		$this->assertTrue($shipType->canHaveIllusion());

		// Galactic Semi has no special hardware
		$shipType = SmrShipType::get(SHIP_TYPE_GALACTIC_SEMI);
		$this->assertFalse($shipType->canHaveJump());
		$this->assertFalse($shipType->canHaveDCS());
		$this->assertFalse($shipType->canHaveScanner());
		$this->assertFalse($shipType->canHaveCloak());
		$this->assertFalse($shipType->canHaveIllusion());
	}

}
