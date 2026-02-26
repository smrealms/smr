<?php declare(strict_types=1);

namespace Smr;

/**
 * Provides details about bar drinks
 */
class BarDrink {

	private const array BASIC_DRINKS = [
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
	];

	private const array RACIAL_DRINKS = [
		RACE_ALSKANT => 'Alskant Space Shandy',
		RACE_CREONTI => 'Creonti "Big Daiquiri"',
		RACE_HUMAN => 'Human Bourbon Bruiser',
		RACE_IKTHORNE => 'Ik\'Thorne Buttery Burst',
		RACE_SALVENE => 'Salvene Swamp Soda',
		RACE_THEVIAN => 'Thevian Vodka Vortex',
		RACE_WQHUMAN => 'West Quadrant Colada',
		RACE_NIJARIN => 'Nijarin Ion Martini',
	];

	private const array SPECIAL_DRINKS = [
		'Spooky Midnight Special' => 'Suddenly the secrets of the universe become manifestly clear and you are at peace.',
		'Azoolian Sunrise Special' => 'At the bottom of the glass, you see a reflection of the best trader in the universe, and it is you.',
	];

	public static function isSpecial(string $drink): bool {
		return array_key_exists($drink, self::SPECIAL_DRINKS);
	}

	/**
	 * Returns the message displayed to the player when they buy a special
	 * bar drink with the given name.
	 */
	public static function getSpecialMessage(string $drink): string {
		return self::SPECIAL_DRINKS[$drink];
	}

	/**
	 * Returns the name of the drink associated with the given race.
	 */
	public static function getRacialDrink(int $raceID): string {
		return self::RACIAL_DRINKS[$raceID];
	}

	/**
	 * Returns the entire list of bar drinks.
	 *
	 * @return array<string>
	 */
	public static function getAll(): array {
		return array_merge(
			self::BASIC_DRINKS,
			self::getRacial(),
			self::getSpecial(),
		);
	}

	/**
	 * Returns the list of special bar drinks.
	 *
	 * @return array<string>
	 */
	public static function getSpecial(): array {
		return array_keys(self::SPECIAL_DRINKS);
	}

	/**
	 * Returns the list of racial bar drinks.
	 *
	 * @return array<string>
	 */
	public static function getRacial(): array {
		return array_values(self::RACIAL_DRINKS);
	}

	/**
	 * Returns the bar drink list with special drinks removed.
	 *
	 * @return array<string>
	 */
	public static function getCommon(): array {
		// Remove the special drinks
		return array_diff(self::getAll(), self::getSpecial());
	}

}
