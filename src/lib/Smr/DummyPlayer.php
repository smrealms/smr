<?php declare(strict_types=1);

namespace Smr;

use Override;

class DummyPlayer extends AbstractPlayer {

	protected readonly int $accountID;
	protected readonly int $gameID;

	public function __construct(string $playerName, int $experience = 1000, int $shipTypeID = 60) {
		$this->accountID = 0;
		$this->gameID = 0;
		$this->playerName = $playerName;
		$this->experience = $experience;
		$this->shipID = $shipTypeID;
		$this->dead = false;
		$this->setConstantProperties();
	}

	/**
	 * Sets properties that are needed for combat, but do not need to
	 * be stored in the database.
	 */
	protected function setConstantProperties(): void {
		$this->playerID = 0;
		$this->turns = 0;
		$this->alignment = 0;
		$this->underAttack = false;
		$this->npc = true;
	}

	#[Override]
	public function __sleep() {
		return ['playerName', 'experience', 'shipID', 'dead', 'accountID', 'gameID'];
	}

	public function __wakeup() {
		$this->setConstantProperties();
	}

	#[Override]
	public function increaseHOF(float $amount, array $typeList, string $visibility): void {}

	#[Override]
	public function killPlayerByPlayer(AbstractPlayer $killer): array {
		$this->dead = true;
		return ['DeadExp' => 0, 'KillerCredits' => 0, 'KillerExp' => 0];
	}

	#[Override]
	public function getShip(bool $forceUpdate = false): DummyShip {
		return DummyShip::getCachedDummyShip($this->playerName);
	}

}
