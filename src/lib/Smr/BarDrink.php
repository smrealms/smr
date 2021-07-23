<?php declare(strict_types=1);

namespace Smr;

/**
 * Provides details about bar drinks
 */
class BarDrink {

	// Special drink used in missions
	public const SALVENE_SWAMP_SODA = 'Salvene Swamp Soda';

	private const DRINK_NAMES = [
		'Spooky Midnight Special',
		'Azoolian Sunrise Special',
		'Big Momma Mojito',
		'Cosmic Crush',
		'Federal Berry Fizz',
		'Flux Punch',
		'Holy Hand Squeezed Gimlet',
		'Little Julep Torpedo',
		'Medium Cargo Cherry Chiller',
		'Pod Giver',
		'Stellar Side Car',
		'Smuggler\'s Salty Swizzler',
		'Alskant Space Shandy',
		'Creonti "Big Daiquiri"',
		'Human Bourbon Bruiser',
		'Ik\'Thorne Buttery Burst',
		self::SALVENE_SWAMP_SODA,
		'Thevian Vodka Vortex',
		'West Quadrant Colada',
		'Nijarin Ion Martini',
	];

	private const SPECIAL_DRINK_MESSAGES = [
		'Spooky Midnight Special' => 'Suddenly the secrets of the universe become manifestly clear and you are at peace.',
		'Azoolian Sunrise Special' => 'At the bottom of the glass, you see a reflection of the best trader in the universe, and it is you.',
	];

	public static function isSpecial(string $drink) : bool {
		return array_key_exists($drink, self::SPECIAL_DRINK_MESSAGES);
	}

	/**
	 * Returns the message displayed to the player when they buy a special
	 * bar drink with the given name.
	 */
	public static function getSpecialMessage(string $drink) : string {
		return self::SPECIAL_DRINK_MESSAGES[$drink];
	}

	/**
	 * Returns the entire list of bar drinks.
	 *
	 * @return array<string>
	 */
	public static function getAll() : array {
		return self::DRINK_NAMES;
	}

	/**
	 * Returns the list of special bar drinks.
	 *
	 * @return array<string>
	 */
	public static function getSpecial() : array {
		return array_keys(self::SPECIAL_DRINK_MESSAGES);
	}

	/**
	 * Returns the bar drink list with special drinks removed.
	 *
	 * @return array<string>
	 */
	public static function getCommon() : array {
		// Remove the special drinks
		return array_diff(self::DRINK_NAMES, self::getSpecial());
	}

}
