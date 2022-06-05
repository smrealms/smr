<?php declare(strict_types=1);

class DummyPlayer extends AbstractSmrPlayer {

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

	public function __sleep() {
		return ['playerName', 'experience', 'shipID', 'dead', 'accountID', 'gameID'];
	}

	public function __wakeup() {
		$this->setConstantProperties();
	}

	public function increaseHOF(float $amount, array $typeList, string $visibility): void {}

	public function killPlayerByPlayer(AbstractSmrPlayer $killer): array {
		$this->dead = true;
		return ['DeadExp' => 0, 'KillerCredits' => 0, 'KillerExp' => 0];
	}

	public function getShip(bool $forceUpdate = false): DummyShip {
		return DummyShip::getCachedDummyShip($this->playerName);
	}

}
