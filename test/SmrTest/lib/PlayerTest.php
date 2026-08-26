<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\Account;
use Smr\Container\DiContainer;
use Smr\Epoch;
use Smr\Player;
use Smr\Ship;
use SmrTest\Fakes\TurnPlayerFake;

#[CoversClass(Player::class)]
class PlayerTest extends TestCase {

	private const float TURN_SPEED = 10.0;
	private const int TURN_INTERVAL = 360; // (3600 / TURN_SPEED)

	protected function tearDown(): void {
		DiContainer::initialize(false);
	}

	public function test_takeTurns_creates_turn_debt(): void {
		// Start at 1 turn, take 3, go 2 turns into debt
		$now = 10_000;
		$turnsTaken = 3;
		$this->setTime($now);
		$player = $this->createPlayer(turns: 1, lastTurnUpdate: $now);

		$player->takeTurns($turnsTaken);
		$turnDebt = 2;

		self::assertSame(0, $player->getTurns());
		// Each turn takes 360 seconds at a speed of 10 turns per hour.
		self::assertSame($now + $turnDebt * self::TURN_INTERVAL, $player->getLastTurnUpdate());
		// The debt must be repaid before the next usable turn arrives.
		self::assertSame(($turnDebt + 1) * self::TURN_INTERVAL, $player->getTimeUntilNextTurn());
	}

	public function test_takeTurns_accounts_for_pending_turns_when_creating_debt(): void {
		// A stale lastTurnUpdate is effectively pending turns
		$now = 10_000;
		$pendingTurns = 2;
		$turnsTaken = $pendingTurns + 1;
		$this->setTime($now);
		$player = $this->createPlayer(
			turns: 0,
			lastTurnUpdate: $now - $pendingTurns * self::TURN_INTERVAL,
		);

		$player->takeTurns($turnsTaken);

		// Two pending turns offset two of the three turns taken, leaving one turn of debt.
		self::assertSame($now + self::TURN_INTERVAL, $player->getLastTurnUpdate());

		// Advance time and show we gain our turn at the proper time.
		$this->setTime($now + self::TURN_INTERVAL);
		$player->updateTurns();
		self::assertSame(0, $player->getTurns());

		$this->setTime($now + 2 * self::TURN_INTERVAL);
		$player->updateTurns();
		self::assertSame(1, $player->getTurns());
	}

	public function test_getTimeUntilNextTurn_handles_stale_turn_update(): void {
		$now = 10_000;
		$this->setTime($now);
		// One full turn and half of the next have elapsed, so 180 seconds remain.
		$player = $this->createPlayer(
			turns: 0,
			lastTurnUpdate: $now - self::TURN_INTERVAL - self::TURN_INTERVAL / 2,
		);

		self::assertSame(self::TURN_INTERVAL / 2, $player->getTimeUntilNextTurn());
	}

	private function setTime(int $time): void {
		$epoch = $this->createStub(Epoch::class);
		$epoch->method('getTime')->willReturn($time);
		DiContainer::getContainer()->set(Epoch::class, $epoch);
	}

	private function createPlayer(int $turns, int $lastTurnUpdate): TurnPlayerFake {
		$ship = $this->createStub(Ship::class);
		$ship->method('getRealSpeed')->willReturn(self::TURN_SPEED);
		$account = $this->createStub(Account::class);
		$account->method('isValidated')->willReturn(true);

		return new TurnPlayerFake(
			ship: $ship,
			account: $account,
			turns: $turns,
			lastTurnUpdate: $lastTurnUpdate,
		);
	}

}
