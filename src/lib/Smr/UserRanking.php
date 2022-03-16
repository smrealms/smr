<?php declare(strict_types=1);

namespace Smr;

/**
 * User ranking titles
 */
class UserRanking {

	public const NAMES = [
		1 => 'Newbie',
		2 => 'Beginner',
		3 => 'Fledgling',
		4 => 'Average',
		5 => 'Adept',
		6 => 'Expert',
		7 => 'Elite',
		8 => 'Master',
		9 => 'Grandmaster',
	];

	public const MIN_RANK = 1;
	public const MAX_RANK = 9;

	public const SCORE_POW = .3;
	public const SCORE_POW_RANK_INCREMENT = 5.2;

	/**
	 * Given a score, return the associated rank
	 */
	public static function getRankFromScore(int $score): int {
		$rank = ICeil(pow($score, self::SCORE_POW) / self::SCORE_POW_RANK_INCREMENT);
		$rank = min(max($rank, self::MIN_RANK), self::MAX_RANK);
		return $rank;
	}

	/**
	 * Given a rank, return the minimum score needed to achieve it
	 * (this is an inversion of getRankFromScore)
	 */
	public static function getMinScoreForRank(int $rank): int {
		return ICeil(pow(($rank - 1) * self::SCORE_POW_RANK_INCREMENT, 1 / self::SCORE_POW));
	}

	/**
	 * Return the title associated with the given rank
	 */
	public static function getName(int $rank): string {
		return self::NAMES[$rank];
	}

	public static function getAllNames(): array {
		return self::NAMES;
	}

}
