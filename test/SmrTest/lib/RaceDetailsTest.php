<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\Race;
use Smr\RaceDetails;

#[CoversClass(RaceDetails::class)]
class RaceDetailsTest extends TestCase {

	public function test_getShortDescription(): void {
		// Check that a description exists for all playable races
		foreach (Race::getPlayableIDs() as $raceID) {
			self::assertNotEmpty(RaceDetails::getShortDescription($raceID));
		}
	}

	public function test_getLongDescription(): void {
		// Check that a description exists for all playable races
		foreach (Race::getPlayableIDs() as $raceID) {
			self::assertNotEmpty(RaceDetails::getLongDescription($raceID));
		}
	}

}
