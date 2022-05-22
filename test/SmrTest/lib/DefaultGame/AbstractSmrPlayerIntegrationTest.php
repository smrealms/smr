<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use AbstractSmrPlayer;
use Smr\Exceptions\PlayerNotFound;
use Smr\Exceptions\UserError;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers AbstractSmrPlayer
 */
class AbstractSmrPlayerIntegrationTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['player'];
	}

	protected function tearDown(): void {
		AbstractSmrPlayer::clearCache();
	}

	public function test_createPlayer(): void {
		// Test arbitrary input
		$accountID = 2;
		$gameID = 42;
		$name = 'test';
		$raceID = RACE_HUMAN;
		$isNewbie = true;
		$isNpc = false;

		$player = AbstractSmrPlayer::createPlayer($accountID, $gameID, $name, $raceID, $isNewbie, $isNpc);

		$this->assertSame($accountID, $player->getAccountID());
		$this->assertSame($gameID, $player->getGameID());
		$this->assertSame($name, $player->getPlayerName());
		$this->assertSame($raceID, $player->getRaceID());
		$this->assertSame($isNewbie, $player->hasNewbieStatus());
		$this->assertSame($isNpc, $player->isNPC());
		$this->assertSame(1, $player->getSectorID());
		$this->assertSame(1, $player->getPlayerID());
	}

	public function test_createPlayer_duplicate_name(): void {
		$name = 'test';
		AbstractSmrPlayer::createPlayer(1, 1, $name, RACE_HUMAN, false);
		$this->expectException(UserError::class);
		$this->expectExceptionMessage('That player name already exists.');
		AbstractSmrPlayer::createPlayer(2, 1, $name, RACE_HUMAN, false);
	}

	public function test_createPlayer_increment_playerid(): void {
		AbstractSmrPlayer::createPlayer(1, 1, 'test1', RACE_HUMAN, false);
		$player = AbstractSmrPlayer::createPlayer(2, 1, 'test2', RACE_HUMAN, false);
		$this->assertSame(2, $player->getPlayerID());
	}

	public function test_getPlayer_returns_created_player(): void {
		// Given a player that is created
		$player1 = AbstractSmrPlayer::createPlayer(1, 2, 'test1', RACE_HUMAN, false);
		// When we get the same player
		$player2 = AbstractSmrPlayer::getPlayer(1, 2);
		// Then they should be the same object
		self::assertSame($player1, $player2);

		// When we get the same player forcing a re-query from the database
		$player3 = AbstractSmrPlayer::getPlayer(1, 2, true);
		// Then they are not the same, but they are equal
		self::assertNotSame($player1, $player3);
		self::assertTrue($player1->equals($player3));
	}

	public function test_getPlayer_throws_when_no_record_found(): void {
		$this->expectException(PlayerNotFound::class);
		$this->expectExceptionMessage('Invalid accountID: 123 OR gameID: 321');
		AbstractSmrPlayer::getPlayer(123, 321);
	}

	public function test_changePlayerName_throws_when_name_unchanged(): void {
		// Try changing name to the same name
		$name = 'test';
		$player = AbstractSmrPlayer::createPlayer(1, 2, $name, RACE_HUMAN, false);
		$this->expectException(UserError::class);
		$this->expectExceptionMessage('Your player already has that name!');
		$player->changePlayerName($name);
	}

	public function test_changePlayerName_throws_when_name_is_in_use(): void {
		// Try changing name to a name that is already taken
		$name1 = 'test1';
		AbstractSmrPlayer::createPlayer(1, 2, $name1, RACE_HUMAN, false);
		$player2 = AbstractSmrPlayer::createPlayer(2, 2, 'test2', RACE_HUMAN, false);
		$this->expectException(UserError::class);
		$this->expectExceptionMessage('That name is already being used in this game!');
		$player2->changePlayerName($name1);
	}

	public function test_changePlayerName_allows_case_change(): void {
		// Try changing name from 'test' to 'TEST'
		$name = 'test';
		$player = AbstractSmrPlayer::createPlayer(1, 2, $name, RACE_HUMAN, false);
		$newName = strtoupper($name);
		self::assertNotEquals($name, $newName); // sanity check
		$player->changePlayerName($newName);
		self::assertSame($newName, $player->getPlayerName());
	}

	public function test_changePlayerName(): void {
		// Try changing name from 'test' to 'Wall-E' (as an admin)
		$player = AbstractSmrPlayer::createPlayer(1, 2, 'test', RACE_HUMAN, false);
		$player->changePlayerName('Wall-E');
		self::assertSame('Wall-E', $player->getPlayerName());
		// Make sure we have NOT used the name change token
		self::assertFalse($player->isNameChanged());
	}

	public function test_changePlayerNameByPlayer(): void {
		// Try changing name from 'test' to 'Wall-E' (as a player)
		$player = AbstractSmrPlayer::createPlayer(1, 2, 'test', RACE_HUMAN, false);
		$player->changePlayerNameByPlayer('Wall-E');
		self::assertSame('Wall-E', $player->getPlayerName());
		// Make sure we *have* used the name change token
		self::assertTrue($player->isNameChanged());
	}

}
