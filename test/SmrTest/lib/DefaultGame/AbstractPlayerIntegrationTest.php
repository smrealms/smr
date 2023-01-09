<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Smr\AbstractPlayer;
use Smr\Exceptions\PlayerNotFound;
use Smr\Exceptions\UserError;
use Smr\Game;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers Smr\AbstractPlayer
 */
class AbstractPlayerIntegrationTest extends BaseIntegrationSpec {

	private static int $gameID = 42;

	protected function tablesToTruncate(): array {
		return ['player'];
	}

	protected function tearDown(): void {
		AbstractPlayer::clearCache();
	}

	public static function setUpBeforeClass(): void {
		// Make objects that must be accessed statically (can't be mocked)
		Game::createGame(self::$gameID)->setGameTypeID(Game::GAME_TYPE_DEFAULT);
	}

	public static function tearDownAfterClass(): void {
		Game::clearCache();
	}

	public function test_createPlayer(): void {
		// Test arbitrary input
		$accountID = 2;
		$name = 'test';
		$raceID = RACE_HUMAN;
		$isNewbie = true;
		$isNpc = false;

		$player = AbstractPlayer::createPlayer($accountID, self::$gameID, $name, $raceID, $isNewbie, $isNpc);

		$this->assertSame($accountID, $player->getAccountID());
		$this->assertSame(self::$gameID, $player->getGameID());
		$this->assertSame($name, $player->getPlayerName());
		$this->assertSame($raceID, $player->getRaceID());
		$this->assertSame($isNewbie, $player->hasNewbieStatus());
		$this->assertSame($isNpc, $player->isNPC());
		$this->assertSame(1, $player->getSectorID());
		$this->assertSame(1, $player->getPlayerID());
	}

	public function test_createPlayer_duplicate_name(): void {
		$name = 'test';
		AbstractPlayer::createPlayer(1, self::$gameID, $name, RACE_HUMAN, false);
		$this->expectException(UserError::class);
		$this->expectExceptionMessage('That player name already exists.');
		AbstractPlayer::createPlayer(2, self::$gameID, $name, RACE_HUMAN, false);
	}

	public function test_createPlayer_increment_playerid(): void {
		AbstractPlayer::createPlayer(1, self::$gameID, 'test1', RACE_HUMAN, false);
		$player = AbstractPlayer::createPlayer(2, self::$gameID, 'test2', RACE_HUMAN, false);
		$this->assertSame(2, $player->getPlayerID());
	}

	public function test_getPlayer_returns_created_player(): void {
		// Given a player that is created
		$player1 = AbstractPlayer::createPlayer(1, self::$gameID, 'test1', RACE_HUMAN, false);
		// When we get the same player
		$player2 = AbstractPlayer::getPlayer(1, self::$gameID);
		// Then they should be the same object
		self::assertSame($player1, $player2);

		// When we get the same player forcing a re-query from the database
		$player3 = AbstractPlayer::getPlayer(1, self::$gameID, true);
		// Then they are not the same, but they are equal
		self::assertNotSame($player1, $player3);
		self::assertTrue($player1->equals($player3));
	}

	public function test_getPlayer_throws_when_no_record_found(): void {
		$this->expectException(PlayerNotFound::class);
		$this->expectExceptionMessage('Invalid accountID: 123 OR gameID: 321');
		AbstractPlayer::getPlayer(123, 321);
	}

	public function test_changePlayerName_throws_when_name_unchanged(): void {
		// Try changing name to the same name
		$name = 'test';
		$player = AbstractPlayer::createPlayer(1, self::$gameID, $name, RACE_HUMAN, false);
		$this->expectException(UserError::class);
		$this->expectExceptionMessage('Your player already has that name!');
		$player->changePlayerName($name);
	}

	public function test_changePlayerName_throws_when_name_is_in_use(): void {
		// Try changing name to a name that is already taken
		$name1 = 'test1';
		AbstractPlayer::createPlayer(1, self::$gameID, $name1, RACE_HUMAN, false);
		$player2 = AbstractPlayer::createPlayer(2, self::$gameID, 'test2', RACE_HUMAN, false);
		$this->expectException(UserError::class);
		$this->expectExceptionMessage('That name is already being used in this game!');
		$player2->changePlayerName($name1);
	}

	public function test_changePlayerName_allows_case_change(): void {
		// Try changing name from 'test' to 'TEST'
		$name = 'test';
		$player = AbstractPlayer::createPlayer(1, self::$gameID, $name, RACE_HUMAN, false);
		$newName = strtoupper($name);
		self::assertNotEquals($name, $newName); // sanity check
		$player->changePlayerName($newName);
		self::assertSame($newName, $player->getPlayerName());
	}

	public function test_changePlayerName(): void {
		// Try changing name from 'test' to 'Wall-E' (as an admin)
		$player = AbstractPlayer::createPlayer(1, self::$gameID, 'test', RACE_HUMAN, false);
		$player->changePlayerName('Wall-E');
		self::assertSame('Wall-E', $player->getPlayerName());
		// Make sure we have NOT used the name change token
		self::assertFalse($player->isNameChanged());
	}

	public function test_changePlayerNameByPlayer(): void {
		// Try changing name from 'test' to 'Wall-E' (as a player)
		$player = AbstractPlayer::createPlayer(1, self::$gameID, 'test', RACE_HUMAN, false);
		$player->changePlayerNameByPlayer('Wall-E');
		self::assertSame('Wall-E', $player->getPlayerName());
		// Make sure we *have* used the name change token
		self::assertTrue($player->isNameChanged());
	}

	/**
	 * @dataProvider dataProvider_alignment
	 */
	public function test_alignment(int $alignment, bool $isGood, bool $isEvil, bool $isNeutral): void {
		// Create a player with a specific alignment
		$player = AbstractPlayer::createPlayer(1, self::$gameID, 'test', RACE_HUMAN, false);
		$player->setAlignment($alignment);

		// Test the alignment querying methods
		self::assertSame($alignment, $player->getAlignment());
		self::assertSame($isGood, $player->hasGoodAlignment());
		self::assertSame($isEvil, $player->hasEvilAlignment());
		self::assertSame($isNeutral, $player->hasNeutralAlignment());
	}

	/**
	 * @return array<array{int, bool, bool, bool}>
	 */
	public function dataProvider_alignment(): array {
		// Test at, above, and below alignment thresholds
		return [
			[0, false, false, true],
			[ALIGNMENT_GOOD, true, false, false],
			[ALIGNMENT_GOOD + 1, true, false, false],
			[ALIGNMENT_GOOD - 1, false, false, true],
			[ALIGNMENT_EVIL, false, true, false],
			[ALIGNMENT_EVIL + 1, false, false, true],
			[ALIGNMENT_EVIL - 1, false, true, false],
		];
	}

}
