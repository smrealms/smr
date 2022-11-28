<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use AbstractSmrLocation;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @covers AbstractSmrLocation
 */
class AbstractSmrLocationTest extends TestCase {

	public function test_getLocation_and_basic_properties(): void {
		$typeID = LOCATION_TYPE_FEDERAL_HQ;
		$loc = AbstractSmrLocation::getLocation(gameID: 0, locationTypeID: $typeID);
		self::assertSame($typeID, $loc->getTypeID());
		self::assertSame('Federal Headquarters', $loc->getName());
		self::assertSame('government.php', $loc->getAction());
		self::assertSame('images/government.png', $loc->getImage());
	}

	public function test_getLocation_throws_when_no_record_found(): void {
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Cannot find location: 999');
		AbstractSmrLocation::getLocation(gameID: 0, locationTypeID: 999);
	}

	/**
	 * @dataProvider provider_isFed
	 */
	public function test_isFed(int $typeID, bool $expected): void {
		$loc = AbstractSmrLocation::getLocation(gameID: 0, locationTypeID: $typeID);
		self::assertSame($expected, $loc->isFed());
	}

	/**
	 * @return array<array{int, bool}>
	 */
	public function provider_isFed(): array {
		return [
			[UNDERGROUND, false],
			[LOCATION_TYPE_FEDERAL_BEACON, true],
			[LOCATION_TYPE_FEDERAL_HQ, false],
			[LOCATION_TYPE_FEDERAL_MINT, false],
		];
	}

	/**
	 * @dataProvider provider_isHQ
	 */
	public function test_isHQ(int $typeID, bool $expected): void {
		$loc = AbstractSmrLocation::getLocation(gameID: 0, locationTypeID: $typeID);
		self::assertSame($expected, $loc->isHQ());
	}

	/**
	 * @return array<array{int, bool}>
	 */
	public function provider_isHQ(): array {
		return [
			[UNDERGROUND, false],
			[LOCATION_TYPE_FEDERAL_BEACON, false],
			[LOCATION_TYPE_FEDERAL_HQ, true],
			[LOCATION_TYPE_FEDERAL_MINT, false],
		];
	}

	/**
	 * @dataProvider provider_isBank
	 */
	public function test_isBank(int $typeID, bool $expected): void {
		$loc = AbstractSmrLocation::getLocation(gameID: 0, locationTypeID: $typeID);
		self::assertSame($expected, $loc->isBank());
	}

	/**
	 * @return array<array{int, bool}>
	 */
	public function provider_isBank(): array {
		return [
			[UNDERGROUND, false],
			[LOCATION_TYPE_FEDERAL_BEACON, false],
			[LOCATION_TYPE_FEDERAL_HQ, false],
			[LOCATION_TYPE_FEDERAL_MINT, true],
		];
	}

	/**
	 * @dataProvider provider_isUG
	 */
	public function test_isUG(int $typeID, bool $expected): void {
		$loc = AbstractSmrLocation::getLocation(gameID: 0, locationTypeID: $typeID);
		self::assertSame($expected, $loc->isUG());
	}

	/**
	 * @return array<array{int, bool}>
	 */
	public function provider_isUG(): array {
		return [
			[UNDERGROUND, true],
			[LOCATION_TYPE_FEDERAL_BEACON, false],
			[LOCATION_TYPE_FEDERAL_HQ, false],
			[LOCATION_TYPE_FEDERAL_MINT, false],
		];
	}

	/**
	 * @dataProvider provider_hasAction
	 */
	public function test_hasAction(int $typeID, bool $expected): void {
		$loc = AbstractSmrLocation::getLocation(gameID: 0, locationTypeID: $typeID);
		self::assertSame($expected, $loc->hasAction());
	}

	/**
	 * @return array<array{int, bool}>
	 */
	public function provider_hasAction(): array {
		return [
			[UNDERGROUND, true],
			[LOCATION_TYPE_FEDERAL_BEACON, false],
			[LOCATION_TYPE_FEDERAL_HQ, true],
			[LOCATION_TYPE_FEDERAL_MINT, true],
		];
	}

	public function test_isShipSold(): void {
		$loc = AbstractSmrLocation::getLocation(gameID: 0, locationTypeID: RACE_WARS_SHIPS);
		self::assertTrue($loc->isShipSold());
		self::assertTrue($loc->isShipSold(SHIP_TYPE_GALACTIC_SEMI));
		self::assertFalse($loc->isShipSold(SHIP_TYPE_DEMONICA));
	}

	public function test_isWeaponSold(): void {
		$loc = AbstractSmrLocation::getLocation(gameID: 0, locationTypeID: RACE_WARS_WEAPONS);
		self::assertTrue($loc->isWeaponSold());
		self::assertTrue($loc->isWeaponSold(WEAPON_TYPE_LASER));
		self::assertFalse($loc->isWeaponSold(WEAPON_PLANET_TURRET));
	}

}
