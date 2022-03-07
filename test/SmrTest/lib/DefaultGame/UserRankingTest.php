<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use PHPUnit\Framework\TestCase;
use Smr\UserRanking;

/**
 * @covers Smr\UserRanking
 */
class UserRankingTest extends TestCase {

	public function test_getName(): void {
		$this->assertSame('Expert', UserRanking::getName(6));
	}

	public function test_getAllNames(): void {
		$this->assertSame('Expert', UserRanking::getAllNames()[6]);
	}

	public function test_rank_limits(): void {
		$ranks = array_keys(UserRanking::getAllNames());
		$this->assertSame(UserRanking::MIN_RANK, min($ranks));
		$this->assertSame(UserRanking::MAX_RANK, max($ranks));
	}

	public function test_score_limits(): void {
		// test the lowest possible score
		$rank = UserRanking::getRankFromScore(0);
		$this->assertSame(UserRanking::MIN_RANK, $rank);
		// test an absurdly high score
		$rank = UserRanking::getRankFromScore(PHP_INT_MAX);
		$this->assertSame(UserRanking::MAX_RANK, $rank);
	}

	public function test_getMinScoreForRank(): void {
		// test all ranks
		foreach (UserRanking::getAllNames() as $rank => $name) {
			$minScore = UserRanking::getMinScoreForRank($rank);
			// make sure the given min score is still the same rank
			$rankFromScore = UserRanking::getRankFromScore($minScore);
			$this->assertSame($rank, $rankFromScore);
		}
	}

}
