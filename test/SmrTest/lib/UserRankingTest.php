<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\UserRanking;

#[CoversClass(UserRanking::class)]
class UserRankingTest extends TestCase {

	public function test_rank_limits(): void {
		// test that the min/max rank are up to date
		$ranks = array_column(UserRanking::cases(), 'value');
		self::assertSame(UserRanking::MIN_RANK, min($ranks));
		self::assertSame(UserRanking::MAX_RANK, max($ranks));
	}

	public function test_score_limits(): void {
		// test the lowest possible score
		$rank = UserRanking::getRankFromScore(0);
		self::assertSame(UserRanking::MIN_RANK, $rank->value);
		// test an absurdly high score
		$rank = UserRanking::getRankFromScore(PHP_INT_MAX);
		self::assertSame(UserRanking::MAX_RANK, $rank->value);
	}

	public function test_score_and_rank_consistency(): void {
		// test all ranks
		foreach (UserRanking::cases() as $rank) {
			$minScore = $rank->getMinScore();
			// make sure the given min score is still the same rank
			$rankFromScore = UserRanking::getRankFromScore($minScore);
			self::assertSame($rank, $rankFromScore);
		}
	}

}
