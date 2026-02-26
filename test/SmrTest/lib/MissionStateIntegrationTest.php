<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use Smr\MissionState;
use SmrTest\BaseIntegrationSpec;
use SmrTest\Fakes\MissionFake;
use SmrTest\Fakes\PlayerFake;

#[CoversClass(MissionState::class)]
class MissionStateIntegrationTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['player_has_mission'];
	}

	protected function setUp(): void {
		// Start each test with an empty cache
		MissionState::clearCache();
	}

	public function test_database_layer(): void {
		$missionID = 9;
		$player = new PlayerFake(gameID: 3, accountID: 7);
		$mission = $this->createStub(MissionFake::class);
		$mission->method('getMissionID')->willReturn($missionID);
		$state = MissionState::addPlayerMission($player, $mission);
		MissionState::saveMissionStates();
		MissionState::clearCache();
		$states = MissionState::getPlayerMissionStates($player);
		self::assertCount(1, $states);
		self::assertArrayHasKey($missionID, $states);
		$newState = $states[$missionID];
		// Old and new states should be different objects w/ same contents
		self::assertNotSame($state, $newState);
		self::assertEquals($state, $newState);

		// Now that the cache is populated, we get the exact same object now
		$states2 = MissionState::getPlayerMissionStates($player);
		self::assertSame($states, $states2);
	}

}
