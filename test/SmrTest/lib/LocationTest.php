<?php declare(strict_types=1);

namespace SmrTest\lib;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\Location;

#[CoversClass(Location::class)]
class LocationTest extends TestCase {

	public function test_getLocation_and_basic_properties(): void {
		$typeID = LOCATION_FEDERAL_HQ;
		$loc = Location::getLocation(gameID: 0, locationTypeID: $typeID);
		self::assertSame($typeID, $loc->getTypeID());
		self::assertSame('Federal Headquarters', $loc->getName());
		self::assertSame('government.php', $loc->getAction());
		self::assertSame('images/government.png', $loc->getImage());
	}

	public function test_getLocation_throws_when_no_record_found(): void {
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Cannot find location: 999');
		Location::getLocation(gameID: 0, locationTypeID: 999);
	}

	#[TestWith([LOCATION_UNDERGROUND, false])]
	#[TestWith([LOCATION_FEDERAL_BEACON, true])]
	#[TestWith([LOCATION_FEDERAL_HQ, false])]
	#[TestWith([LOCATION_FEDERAL_MINT, false])]
	public function test_isFed(int $typeID, bool $expected): void {
		$loc = Location::getLocation(gameID: 0, locationTypeID: $typeID);
		self::assertSame($expected, $loc->isFed());
	}

	#[TestWith([LOCATION_UNDERGROUND, false])]
	#[TestWith([LOCATION_FEDERAL_BEACON, false])]
	#[TestWith([LOCATION_FEDERAL_HQ, true])]
	#[TestWith([LOCATION_FEDERAL_MINT, false])]
	public function test_isHQ(int $typeID, bool $expected): void {
		$loc = Location::getLocation(gameID: 0, locationTypeID: $typeID);
		self::assertSame($expected, $loc->isHQ());
	}

	#[TestWith([LOCATION_UNDERGROUND, false])]
	#[TestWith([LOCATION_FEDERAL_BEACON, false])]
	#[TestWith([LOCATION_FEDERAL_HQ, false])]
	#[TestWith([LOCATION_FEDERAL_MINT, true])]
	public function test_isBank(int $typeID, bool $expected): void {
		$loc = Location::getLocation(gameID: 0, locationTypeID: $typeID);
		self::assertSame($expected, $loc->isBank());
	}

	#[TestWith([LOCATION_UNDERGROUND, true])]
	#[TestWith([LOCATION_FEDERAL_BEACON, false])]
	#[TestWith([LOCATION_FEDERAL_HQ, false])]
	#[TestWith([LOCATION_FEDERAL_MINT, false])]
	public function test_isUG(int $typeID, bool $expected): void {
		$loc = Location::getLocation(gameID: 0, locationTypeID: $typeID);
		self::assertSame($expected, $loc->isUG());
	}

	#[TestWith([LOCATION_UNDERGROUND, true])]
	#[TestWith([LOCATION_FEDERAL_BEACON, false])]
	#[TestWith([LOCATION_FEDERAL_HQ, true])]
	#[TestWith([LOCATION_FEDERAL_MINT, true])]
	public function test_hasAction(int $typeID, bool $expected): void {
		$loc = Location::getLocation(gameID: 0, locationTypeID: $typeID);
		self::assertSame($expected, $loc->hasAction());
	}

	public function test_isShipSold(): void {
		$loc = Location::getLocation(gameID: 0, locationTypeID: RACE_WARS_SHIPS);
		self::assertTrue($loc->isShipSold());
		self::assertTrue($loc->isShipSold(SHIP_TYPE_GALACTIC_SEMI));
		self::assertFalse($loc->isShipSold(SHIP_TYPE_DEMONICA));
	}

	public function test_isWeaponSold(): void {
		$loc = Location::getLocation(gameID: 0, locationTypeID: RACE_WARS_WEAPONS);
		self::assertTrue($loc->isWeaponSold());
		self::assertTrue($loc->isWeaponSold(WEAPON_TYPE_LASER));
		self::assertFalse($loc->isWeaponSold(WEAPON_PLANET_TURRET));
	}

	public function test_getAllBars(): void {
		foreach (Location::getAllBars() as $loc) {
			self::assertTrue($loc->isBar());
		}
	}

	public function test_getAllBanks(): void {
		foreach (Location::getAllBanks() as $loc) {
			self::assertTrue($loc->isBank());
		}
	}

	public function test_getAllWeaponShops(): void {
		foreach (Location::getAllWeaponShops() as $loc) {
			self::assertTrue($loc->isWeaponSold());
		}
	}

	public function test_getAllShipShops(): void {
		foreach (Location::getAllShipShops() as $loc) {
			self::assertTrue($loc->isShipSold());
		}
	}

}
