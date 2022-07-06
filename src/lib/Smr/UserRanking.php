<?php declare(strict_types=1);

namespace Smr;

/**
 * User ranking titles
 */
enum UserRanking: int {

	// Backing values map to database values and must not be changed
	case Newbie = 1;
	case Beginner = 2;
	case Fledgling = 3;
	case Average = 4;
	case Adept = 5;
	case Expert = 6;
	case Elite = 7;
	case Master = 8;
	case Grandmaster = 9;

	public const MIN_RANK = 1;
	public const MAX_RANK = 9;

	public const SCORE_POW = .3;
	public const SCORE_POW_RANK_INCREMENT = 5.2;

	/**
	 * Given a score, return the associated rank
	 */
	public static function getRankFromScore(int $score): self {
		$rank = ICeil(pow($score, self::SCORE_POW) / self::SCORE_POW_RANK_INCREMENT);
		$rank = min(max($rank, self::MIN_RANK), self::MAX_RANK);
		return self::from($rank);
	}

	/**
	 * Given a rank, return the minimum score needed to achieve it
	 * (this is an inversion of getRankFromScore)
	 */
	public function getMinScore(): int {
		return ICeil(pow(($this->value - 1) * self::SCORE_POW_RANK_INCREMENT, 1 / self::SCORE_POW));
	}

}
