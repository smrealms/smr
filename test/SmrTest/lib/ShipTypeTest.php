<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\BuyerRestriction;
use Smr\ShipClass;
use Smr\ShipType;

#[CoversClass(ShipType::class)]
class ShipTypeTest extends TestCase {

	public static function setUpBeforeClass(): void {
		// Ensure the cache has not been populated yet
		ShipType::clearCache();
	}

	public function test_one_ship_properties(): void {
		// Test all properties of one particular ship (Fed Ult)
		$shipType = ShipType::get(SHIP_TYPE_FEDERAL_ULTIMATUM);

		self::assertSame(SHIP_TYPE_FEDERAL_ULTIMATUM, $shipType->getTypeID());
		self::assertSame(ShipClass::Raider, $shipType->getClass());
		self::assertSame('Federal Ultimatum', $shipType->getName());
		self::assertSame(38675738, $shipType->getCost());
		self::assertSame(BuyerRestriction::Good, $shipType->getRestriction());
		self::assertSame(8, $shipType->getSpeed());
		self::assertSame(7, $shipType->getHardpoints());
		self::assertSame(24, $shipType->getMaxPower());

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
		self::assertSame($hardware, $shipType->getAllMaxHardware());
		foreach ($hardware as $hardwareID => $amount) {
			self::assertSame($amount, $shipType->getMaxHardware($hardwareID));
		}
	}

	public function test_getAll_matches_get(): void {
		// Check that we get the same ship type from get and getAll
		$shipType1 = ShipType::get(SHIP_TYPE_GALACTIC_SEMI);
		$shipType2 = ShipType::getAll()[SHIP_TYPE_GALACTIC_SEMI];
		self::assertSame($shipType1, $shipType2);
	}

	public function test_can_have_special_hardware(): void {
		// Demonica has all special hardware
		$shipType = ShipType::get(SHIP_TYPE_DEMONICA);
		self::assertTrue($shipType->canHaveJump());
		self::assertTrue($shipType->canHaveDCS());
		self::assertTrue($shipType->canHaveScanner());
		self::assertTrue($shipType->canHaveCloak());
		self::assertTrue($shipType->canHaveIllusion());

		// Galactic Semi has no special hardware
		$shipType = ShipType::get(SHIP_TYPE_GALACTIC_SEMI);
		self::assertFalse($shipType->canHaveJump());
		self::assertFalse($shipType->canHaveDCS());
		self::assertFalse($shipType->canHaveScanner());
		self::assertFalse($shipType->canHaveCloak());
		self::assertFalse($shipType->canHaveIllusion());
	}

}
