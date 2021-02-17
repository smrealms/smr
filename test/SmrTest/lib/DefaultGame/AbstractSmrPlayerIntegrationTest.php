<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use AbstractSmrPlayer;
use SmrTest\BaseIntegrationSpec;

/**
 * Class AbstractSmrPlayerTest
 * @covers AbstractSmrPlayer
 */
class AbstractSmrPlayerIntegrationTest extends BaseIntegrationSpec {

	public function test_createPlayer() : void {
		// Test arbitrary input
		$accountID = 2;
		$gameID = 42;
		$name = 'test';
		$raceID = RACE_HUMAN;
		$isNewbie = true;
		$isNpc = false;

		$player = AbstractSmrPlayer::createPlayer($accountID, $gameID, $name, $raceID, $isNewbie, $isNpc);

		$this->assertSame($player->getAccountID(), $accountID);
		$this->assertSame($player->getGameID(), $gameID);
		$this->assertSame($player->getPlayerName(), $name);
		$this->assertSame($player->getRaceID(), $raceID);
		$this->assertSame($player->hasNewbieStatus(), $isNewbie);
		$this->assertSame($player->isNPC(), $isNpc);
		$this->assertSame($player->getSectorID(), 1);
		$this->assertSame($player->getPlayerID(), 1);
	}

	public function test_createPlayer_duplicate_name() : void {
		$this->expectException(\Smr\UserException::class);
		$this->expectExceptionMessage('That player name already exists.');
		$name = 'test';
		AbstractSmrPlayer::createPlayer(1, 1, $name, RACE_HUMAN, false);
		AbstractSmrPlayer::createPlayer(2, 1, $name, RACE_HUMAN, false);
	}

	public function test_createPlayer_increment_playerid() : void {
		AbstractSmrPlayer::createPlayer(1, 1, 'test1', RACE_HUMAN, false);
		$player = AbstractSmrPlayer::createPlayer(2, 1, 'test2', RACE_HUMAN, false);
		$this->assertSame($player->getPlayerID(), 2);
	}

}
