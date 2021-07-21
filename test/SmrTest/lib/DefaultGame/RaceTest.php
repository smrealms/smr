<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Smr\Race;

/**
 * @covers Smr\Race
 */
class RaceTest extends \PHPUnit\Framework\TestCase {

	public function test_getPlayableIDs() : void {
		$ids = Race::getPlayableIDs();
		self::assertCount(8, $ids);
		self::assertNotContains(RACE_NEUTRAL, $ids);
		// Spot check the result
		self::assertContains(RACE_HUMAN, $ids);
	}

	public function test_getPlayableNames() : void {
		$names = Race::getPlayableNames();
		self::assertCount(8, $names);
		self::assertNotContains('Neutral', $names);
		// Spot check the result
		self::assertContains('Human', $names);
	}

	public function test_getPlayableIDs_matches_getPlayableNames() : void {
		$ids = Race::getPlayableIDs();
		$names = Race::getPlayableNames();
		self::assertSame($ids, array_keys($names));
	}

	public function test_getAllIDs_matches_getAllNames() : void {
		$ids = Race::getAllIDs();
		$names = Race::getAllNames();
		self::assertSame($ids, array_keys($names));
	}

	public function test_getName() : void {
		// Spot check the result
		self::assertSame('WQ Human', Race::getName(RACE_WQHUMAN));
	}

	public function test_getName_against_getAllNames() : void {
		foreach (Race::getAllNames() as $raceID => $raceName) {
			self::assertSame($raceName, Race::getName($raceID));
		}
	}

	public function test_getName_against_getPlayableNames() : void {
		foreach (Race::getPlayableNames() as $raceID => $raceName) {
			self::assertSame($raceName, Race::getName($raceID));
		}
	}

	public function test_getImage() : void {
		// Spot check the result
		$file = Race::getImage(RACE_ALSKANT);
		self::assertStringContainsString('race2', $file);
		// Check that all files exist
		foreach (Race::getPlayableIDs() as $raceID) {
			self::assertFileExists(WWW . Race::getImage($raceID));
		}
	}

	public function test_getHeadImage() : void {
		// Spot check the result
		$file = Race::getHeadImage(RACE_ALSKANT);
		self::assertStringContainsString('race2', $file);
		// Check that all files exist
		foreach (Race::getPlayableIDs() as $raceID) {
			self::assertFileExists(WWW . Race::getImage($raceID));
		}
	}

}
