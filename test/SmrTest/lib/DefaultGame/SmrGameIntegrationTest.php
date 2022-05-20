<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Globals;
use Smr\Race;
use SmrGame;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers SmrGame
 */
class SmrGameIntegrationTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['game', 'race_has_relation'];
	}

	protected function tearDown(): void {
		SmrGame::clearCache();
	}

	public function test_gameExists(): void {
		// Test that the game does not exist beforehand
		$gameID = 42;
		self::assertFalse(SmrGame::gameExists($gameID));

		// Now create the game and confirm that the game now exists
		SmrGame::createGame($gameID);
		self::assertTrue(SmrGame::gameExists($gameID));
	}

	public function test_save_and_reload_required_properties(): void {
		// First create a new game
		$gameID = 3;
		$game1 = SmrGame::createGame($gameID);

		// Then set all of its properties
		$game1->setName('Test Game');
		$game1->setDescription('A test game.');
		$game1->setStartTime(123);
		$game1->setJoinTime(234);
		$game1->setEndTime(345);
		$game1->setMaxPlayers(5);
		$game1->setMaxTurns(1000);
		$game1->setStartTurnHours(24);
		$game1->setGameTypeID(SmrGame::GAME_TYPE_DRAFT);
		$game1->setCreditsNeeded(10);
		$game1->setGameSpeed(1.5);
		$game1->setEnabled(true);
		$game1->setIgnoreStats(true);
		$game1->setAllianceMaxPlayers(15);
		$game1->setAllianceMaxVets(10);
		$game1->setStartingCredits(3000);

		// Now save the game and reload it
		$game1->save();
		$game2 = SmrGame::getGame($gameID, true);

		// Test that the properties have all propagated correctly
		self::assertSame('Test Game', $game2->getName());
		self::assertSame('A test game.', $game2->getDescription());
		self::assertSame(123, $game2->getStartTime());
		self::assertSame(234, $game2->getJoinTime());
		self::assertSame(345, $game2->getEndTime());
		self::assertSame(5, $game2->getMaxPlayers());
		self::assertSame(1000, $game2->getMaxTurns());
		self::assertSame(24, $game2->getStartTurnHours());
		self::assertSame('Draft', $game2->getGameType());
		self::assertSame(10, $game2->getCreditsNeeded());
		self::assertSame(1.5, $game2->getGameSpeed());
		self::assertTrue($game2->isEnabled());
		self::assertTrue($game2->isIgnoreStats());
		self::assertSame(15, $game2->getAllianceMaxPlayers());
		self::assertSame(10, $game2->getAllianceMaxVets());
		self::assertSame(3000, $game2->getStartingCredits());
	}

	public function test_setStartingRelations(): void {
		// Set the starting relations
		$game = SmrGame::createGame(1);
		$game->setStartingRelations(-123);

		// Verify that relations have been set properly
		foreach (Race::getAllIDs() as $raceID1) {
			$relations = Globals::getRaceRelations(1, $raceID1);
			foreach (Race::getAllIDs() as $raceID2) {
				$expected = -123;
				if ($raceID1 == $raceID2) {
					$expected = MAX_GLOBAL_RELATIONS;
				} elseif ($raceID1 == RACE_NEUTRAL || $raceID2 == RACE_NEUTRAL) {
					$expected = 0;
				}
				self::assertSame($expected, $relations[$raceID2]);
			}
		}
	}

}
