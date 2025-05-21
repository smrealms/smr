<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use Smr\Epoch;
use Smr\Game;
use Smr\Globals;
use Smr\Race;
use SmrTest\BaseIntegrationSpec;

#[CoversClass(Game::class)]
class GameIntegrationTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['game', 'race_has_relation'];
	}

	protected function tearDown(): void {
		Game::clearCache();
	}

	public function test_gameExists(): void {
		// Test that the game does not exist beforehand
		$gameID = 42;
		self::assertFalse(Game::gameExists($gameID));

		// Now create the game and confirm that the game now exists
		Game::createGame($gameID);
		self::assertTrue(Game::gameExists($gameID));
	}

	public function test_save_and_reload_required_properties(): void {
		// First create a new game
		$gameID = 3;
		$game1 = Game::createGame($gameID);

		// Then set all of its properties
		$game1->setName('Test Game');
		$game1->setDescription('A test game.');
		$game1->setStartTime(123);
		$game1->setJoinTime(234);
		$game1->setEndTime(345);
		$game1->setMaxPlayers(5);
		$game1->setMaxTurns(1000);
		$game1->setStartTurnHours(24);
		$game1->setGameTypeID(Game::GAME_TYPE_DRAFT);
		$game1->setCreditsNeeded(10);
		$game1->setGameSpeed(1.5);
		$game1->setEnabled(true);
		$game1->setIgnoreStats(true);
		$game1->setAllianceMaxPlayers(15);
		$game1->setAllianceMaxVets(10);
		$game1->setStartingCredits(3000);
		$game1->setDestroyPorts(true);

		// Now save the game and reload it
		$game1->save();
		$game2 = Game::getGame($gameID, true);

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
		self::assertTrue($game2->canDestroyPorts());
	}

	public function test_setStartingRelations(): void {
		// Set the starting relations
		$game = Game::createGame(1);
		$game->setStartingRelations(-123);

		// Verify that relations have been set properly
		foreach (Race::getAllIDs() as $raceID1) {
			$relations = Globals::getRaceRelations(1, $raceID1);
			foreach (Race::getAllIDs() as $raceID2) {
				$expected = -123;
				if ($raceID1 === $raceID2) {
					$expected = MAX_GLOBAL_RELATIONS;
				} elseif ($raceID1 === RACE_NEUTRAL || $raceID2 === RACE_NEUTRAL) {
					$expected = 0;
				}
				self::assertSame($expected, $relations[$raceID2]);
			}
		}
	}

	public function test_isGameType(): void {
		$game = Game::createGame(1);
		$game->setGameTypeID(Game::GAME_TYPE_NEWBIE);
		self::assertTrue($game->isGameType(Game::GAME_TYPE_NEWBIE));
		self::assertFalse($game->isGameType(Game::GAME_TYPE_DEFAULT));
	}

	/**
	 * Helper function to set up 4 games in a matrix of (enabled, end_time)
	 */
	private static function setupGameMatrix(): void {
		$gameId = 1;
		foreach ([false, true] as $enabled) {
			foreach ([Epoch::time() - 1, Epoch::time() + 1] as $endTime) {
				$game = Game::createGame($gameId);
				$game->setEnabled($enabled);
				$game->setEndTime($endTime);

				// All remaining properties must be set to save the game
				$game->setName('game' . $gameId); // must be unique
				$game->setDescription('');
				$game->setJoinTime(0);
				$game->setStartTime(0);
				$game->setMaxPlayers(0);
				$game->setMaxTurns(0);
				$game->setStartTurnHours(0);
				$game->setGameTypeID(Game::GAME_TYPE_DEFAULT);
				$game->setCreditsNeeded(0);
				$game->setGameSpeed(0);
				$game->setIgnoreStats(true);
				$game->setAllianceMaxPlayers(0);
				$game->setAllianceMaxVets(0);
				$game->setStartingCredits(0);
				$game->setDestroyPorts(true);

				$game->save();
				$gameId += 1;
			}
		}
	}

	public function test_getActiveGames(): void {
		self::setupGameMatrix();
		foreach (Game::getActiveGames(forceUpdate: true) as $gameId => $game) {
			self::assertSame(4, $game->getGameID()); // expect only game 4 from matrix
			self::assertSame(4, $gameId);
			self::assertTrue($game->isEnabled());
			self::assertFalse($game->hasEnded());
		}
	}

	public function test_getPastGames(): void {
		self::setupGameMatrix();
		foreach (Game::getPastGames(forceUpdate: true) as $gameId => $game) {
			self::assertSame(3, $game->getGameID()); // expect only game 3 from matrix
			self::assertSame(3, $gameId);
			self::assertTrue($game->isEnabled());
			self::assertTrue($game->hasEnded());
		}
	}

}
