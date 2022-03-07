<?php declare(strict_types=1);

namespace Smr;

/**
 * Categorization of ship types.
 */
class ShipClass {

	const HUNTER = 1;
	const TRADER = 2;
	const RAIDER = 3;
	const SCOUT = 4;
	const STARTER = 5;

	const NAMES = [
		self::HUNTER => 'Hunter',
		self::TRADER => 'Trader',
		self::RAIDER => 'Raider',
		self::SCOUT => 'Scout',
		self::STARTER => 'Starter',
	];

	public static function getName(int $shipClassID): string {
		return self::NAMES[$shipClassID];
	}

	public static function getAllNames(): array {
		return self::NAMES;
	}

}
