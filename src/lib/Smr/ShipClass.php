<?php declare(strict_types=1);

namespace Smr;

/**
 * Categorization of ship types.
 */
class ShipClass {

	public const HUNTER = 1;
	public const TRADER = 2;
	public const RAIDER = 3;
	public const SCOUT = 4;
	public const STARTER = 5;

	public const NAMES = [
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
