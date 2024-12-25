<?php declare(strict_types=1);

namespace Smr;

/**
 * Provides methods to map race IDs to basic race properties.
 */
class Race {

	private const array RACE_NAMES = [
		RACE_NEUTRAL => 'Neutral',
		RACE_ALSKANT => 'Alskant',
		RACE_CREONTI => 'Creonti',
		RACE_HUMAN => 'Human',
		RACE_IKTHORNE => 'Ik\'Thorne',
		RACE_SALVENE => 'Salvene',
		RACE_THEVIAN => 'Thevian',
		RACE_WQHUMAN => 'WQ Human',
		RACE_NIJARIN => 'Nijarin',
	];

	/**
	 * All possible race IDs.
	 *
	 * @return array<int>
	 */
	public static function getAllIDs(): array {
		return \array_keys(self::RACE_NAMES);
	}

	/**
	 * Maps all possible race IDs to race names.
	 *
	 * @return array<int, string>
	 */
	public static function getAllNames(): array {
		return self::RACE_NAMES;
	}

	/**
	 * Race IDs for playable races only. Practically speaking, this is the
	 * same as `getAllIDs` with the Neutral race excluded.
	 *
	 * Note: Some playable races may be excluded on a game-by-game basis
	 * by omitting racial HQ locations. See Game::getPlayableRaceIDs.
	 *
	 * @return array<int>
	 */
	public static function getPlayableIDs(): array {
		$names = self::RACE_NAMES;
		unset($names[RACE_NEUTRAL]);
		return \array_keys($names);
	}

	/**
	 * Maps race IDs to race names for playable races. Practically speaking,
	 * this is the same as `getAllNames` with the Neutral race excluded.
	 *
	 * Note: Some playable races may be excluded on a game-by-game basis
	 * by omitting racial HQ locations. See Game::getPlayableRaceIDs.
	 *
	 * @return array<int, string>
	 */
	public static function getPlayableNames(): array {
		$names = self::RACE_NAMES;
		unset($names[RACE_NEUTRAL]);
		return $names;
	}

	public static function getName(int $raceID): string {
		return self::RACE_NAMES[$raceID];
	}

	/**
	 * Relative path to racial image file.
	 */
	public static function getImage(int $raceID): string {
		return 'images/race/race' . $raceID . '.jpg';
	}

	/**
	 * Relative path to racial image file (portrait version).
	 */
	public static function getHeadImage(int $raceID): string {
		return 'images/race/head/race' . $raceID . '.jpg';
	}

}
