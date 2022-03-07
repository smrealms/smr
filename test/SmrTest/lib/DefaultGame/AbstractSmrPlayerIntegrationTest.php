<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use AbstractSmrPlayer;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers AbstractSmrPlayer
 */
class AbstractSmrPlayerIntegrationTest extends BaseIntegrationSpec {

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
		$this->expectException(\Smr\Exceptions\UserError::class);
		$this->expectExceptionMessage('That player name already exists.');
		AbstractSmrPlayer::createPlayer(2, 1, $name, RACE_HUMAN, false);
	}

	public function test_createPlayer_increment_playerid(): void {
		AbstractSmrPlayer::createPlayer(1, 1, 'test1', RACE_HUMAN, false);
		$player = AbstractSmrPlayer::createPlayer(2, 1, 'test2', RACE_HUMAN, false);
		$this->assertSame(2, $player->getPlayerID());
	}

}
