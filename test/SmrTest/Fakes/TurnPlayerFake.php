<?php declare(strict_types=1);

namespace SmrTest\Fakes;

use Override;
use Smr\Account;
use Smr\Ship;

class TurnPlayerFake extends PlayerFake {

	private const int MAX_TURNS = 100;

	public function __construct(
		private readonly Ship $ship,
		private readonly Account $account,
		int $turns,
		int $lastTurnUpdate,
	) {
		parent::__construct(gameID: 1, accountID: 1);
		$this->turns = $turns;
		$this->newbieTurns = 0;
		$this->lastTurnUpdate = $lastTurnUpdate;
		$this->lastActive = 0;
		$this->lastCPLAction = 0;
	}

	#[Override]
	public function getAccount(): Account {
		return $this->account;
	}

	#[Override]
	public function getMaxTurns(): int {
		return self::MAX_TURNS;
	}

	#[Override]
	public function getShip(bool $forceUpdate = false): Ship {
		return $this->ship;
	}

	#[Override]
	public function increaseHOF(float $amount, array $typeList, string $visibility): void {
	}

}
