<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\TestCase;
use Smr\UserRanking;

/**
 * @covers Smr\UserRanking
 */
class UserRankingTest extends TestCase {

	public function test_rank_limits(): void {
		// test that the min/max rank are up to date
		$ranks = array_column(UserRanking::cases(), 'value');
		$this->assertSame(UserRanking::MIN_RANK, min($ranks));
		$this->assertSame(UserRanking::MAX_RANK, max($ranks));
	}

	public function test_score_limits(): void {
		// test the lowest possible score
		$rank = UserRanking::getRankFromScore(0);
		$this->assertSame(UserRanking::MIN_RANK, $rank->value);
		// test an absurdly high score
		$rank = UserRanking::getRankFromScore(PHP_INT_MAX);
		$this->assertSame(UserRanking::MAX_RANK, $rank->value);
	}

	public function test_score_and_rank_consistency(): void {
		// test all ranks
		foreach (UserRanking::cases() as $rank) {
			$minScore = $rank->getMinScore();
			// make sure the given min score is still the same rank
			$rankFromScore = UserRanking::getRankFromScore($minScore);
			$this->assertSame($rank, $rankFromScore);
		}
	}

}
