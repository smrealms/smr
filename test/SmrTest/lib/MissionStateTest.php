<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Smr\Exceptions\MissionStepNotFound;
use Smr\MissionActions\ClaimReward;
use Smr\MissionActions\EnterSector;
use Smr\MissionState;
use Smr\MissionStep;
use Smr\Player;
use SmrTest\Fakes\MissionFake;
use SmrTest\TestUtils;

#[CoversClass(MissionState::class)]
class MissionStateTest extends TestCase {

	public function test_addPlayerMission(): void {
		$missionID = 7;
		$mission = $this->createStub(MissionFake::class);
		$mission->method('getMissionID')->willReturn($missionID);
		$player = $this->createStub(Player::class);
		$player->method('getAccountID')->willReturn(2);
		$player->method('getGameID')->willReturn(3);

		$state = MissionState::addPlayerMission($player, $mission);

		// Check public getters/properties
		self::assertFalse($state->isComplete());
		self::assertSame($state->accountID, 2);
		self::assertSame($state->gameID, 3);
		self::assertSame($state->missionID, $missionID);
		self::assertSame($state->mission, $mission);

		// Make sure it was added to the cache
		$states = MissionState::getPlayerMissionStates($player);
		self::assertCount(1, $states);
		self::assertSame($states[$missionID], $state);
	}

	public function test_checkAction(): void {
		$mission = $this->createStub(MissionFake::class);
		$mission->method('getStep')
			->willReturnCallback(function (int $step) {
				return match ($step) {
					0 => new MissionStep(new EnterSector(9), '', ''),
					1 => new MissionStep(new EnterSector(5), '', ''),
					default => throw new MissionStepNotFound(),
				};
			});

		$state = TestUtils::constructPrivateClass(MissionState::class, 2, 3, 4, $mission, 0, false, 0, false, true);
		$action0 = new EnterSector(9);
		$action1 = new EnterSector(5);
		$actionNo = new EnterSector(4);

		$onStep = new ReflectionProperty(MissionState::class, 'onStep');

		// Make sure we start incomplete at step 0
		self::assertFalse($state->isComplete());
		self::assertSame($onStep->getValue($state), 0);

		// Action matching no steps should do nothing
		$state->checkAction($actionNo);
		self::assertSame($onStep->getValue($state), 0);

		// Action matching the wrong step should do nothing
		$state->checkAction($action1);
		self::assertSame($onStep->getValue($state), 0);

		// Action matching the correct step should increment
		$state->checkAction($action0);
		self::assertSame($onStep->getValue($state), 1);
		self::assertFalse($state->isComplete()); // still incomplete

		// Action matching no steps should still do nothing
		$state->checkAction($actionNo);
		self::assertSame($onStep->getValue($state), 1);

		// Action matching the wrong step should still do nothing
		$state->checkAction($action0);
		self::assertSame($onStep->getValue($state), 1);

		// Action matching the final step should increment and complete
		$state->checkAction($action1);
		self::assertSame($onStep->getValue($state), 2);
		self::assertTrue($state->isComplete());
	}

	public function test_getUnreadMessages(): void {
		$mission = new MissionFake();
		$state = TestUtils::constructPrivateClass(MissionState::class, 2, 3, 4, $mission, 0, true, 0, false, true);

		self::assertSame($state->getUnreadMessage(), 'message');
		self::assertNull($state->getUnreadMessage()); // already read above
	}

	public function test_hasClaimableReward(): void {
		$mission = $this->createStub(MissionFake::class);
		$mission->method('getStep')->willReturn(
			new MissionStep(new ClaimReward(7), '', ''),
		);

		$state = TestUtils::constructPrivateClass(MissionState::class, 2, 3, 4, $mission, 0, false, 0, false, true);

		self::assertFalse($state->hasClaimableReward(9)); // wrong sector ID
		self::assertTrue($state->hasClaimableReward(7)); // right sector ID
	}

}
