<?php declare(strict_types=1);

namespace Smr;

use Exception;

class Bounty {

	/**
	 * Returns a list of all active (not claimable) bounties for given location $type.
	 *
	 * @return array<self>
	 */
	public static function getMostWanted(BountyType $type, int $gameID): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM bounty WHERE game_id = :game_id AND type = :type AND claimer_id = 0 ORDER BY amount DESC', [
			'game_id' => $db->escapeNumber($gameID),
			'type' => $db->escapeString($type->value),
		]);
		$bounties = [];
		foreach ($dbResult->records() as $dbRecord) {
			$bounties[] = self::getFromRecord($dbRecord);
		}
		return $bounties;
	}

	/**
	 * Get bounties that have been placed on this player.
	 *
	 * @return array<int, self>
	 */
	public static function getPlacedOnPlayer(AbstractPlayer $player): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM bounty WHERE ' . AbstractPlayer::SQL, $player->SQLID);
		$bounties = [];
		foreach ($dbResult->records() as $dbRecord) {
			// Recall that bounty_id is only unique to a given player
			$bounties[$dbRecord->getInt('bounty_id')] = self::getFromRecord($dbRecord);
		}
		return $bounties;
	}

	/**
	 * Get bounties that can be claimed by this player.
	 *
	 * @return array<self>
	 */
	public static function getClaimableByPlayer(AbstractPlayer $player, ?BountyType $type = null): array {
		$db = Database::getInstance();
		$query = 'SELECT * FROM bounty WHERE claimer_id = :claimer_id AND game_id = :game_id';
		$sqlParams = [
			'claimer_id' => $db->escapeNumber($player->getAccountID()),
			'game_id' => $db->escapeNumber($player->getGameID()),
		];
		if ($type === null) {
			$dbResult = $db->read($query, $sqlParams);
		} else {
			$sqlParams['type'] = $db->escapeString($type->value);
			$dbResult = $db->read($query . ' AND type = :type', $sqlParams);
		}
		$bounties = [];
		foreach ($dbResult->records() as $dbRecord) {
			$bounties[] = self::getFromRecord($dbRecord);
		}
		return $bounties;
	}

	public static function getFromRecord(DatabaseRecord $record): self {
		return new self(
			targetID: $record->getInt('account_id'),
			bountyID: $record->getInt('bounty_id'),
			gameID: $record->getInt('game_id'),
			type: $record->getStringEnum('type', BountyType::class),
			time: $record->getInt('time'),
			claimerID: $record->getInt('claimer_id'),
			credits: $record->getInt('amount'),
			smrCredits: $record->getInt('smr_credits'),
			hasChanged: false,
		);
	}

	public function __construct(
		public readonly int $targetID, // target account ID
		public readonly int $bountyID, // only unique to the target
		public readonly int $gameID,
		public readonly BountyType $type,
		public readonly int $time,
		private int $claimerID = 0, // claimer account ID (or 0)
		private int $credits = 0,
		private int $smrCredits = 0,
		private bool $hasChanged = true,
	) {}

	public function getCredits(): int {
		return $this->credits;
	}

	public function getSmrCredits(): int {
		return $this->smrCredits;
	}

	public function isActive(): bool {
		return $this->claimerID == 0;
	}

	public function setClaimable(int $claimerID): void {
		if (!$this->isActive()) {
			throw new Exception('This bounty has already been claimed!');
		}
		$this->claimerID = $claimerID;
		$this->hasChanged = true;
	}

	public function setClaimed(): void {
		$this->setCredits(0);
		$this->setSmrCredits(0);
	}

	private function setCredits(int $credits): void {
		if ($this->credits == $credits) {
			return;
		}
		$this->credits = $credits;
		$this->hasChanged = true;
	}

	private function setSmrCredits(int $smrCredits): void {
		if ($this->smrCredits == $smrCredits) {
			return;
		}
		$this->smrCredits = $smrCredits;
		$this->hasChanged = true;
	}

	public function increaseCredits(int $credits): void {
		if ($credits < 0) {
			throw new Exception('Cannot increase by a negative amount');
		}
		$this->setCredits($this->credits + $credits);
	}

	public function increaseSmrCredits(int $smrCredits): void {
		if ($smrCredits < 0) {
			throw new Exception('Cannot increase by a negative amount');
		}
		$this->setSmrCredits($this->smrCredits + $smrCredits);
	}

	public function getTargetPlayer(): AbstractPlayer {
		return Player::getPlayer($this->targetID, $this->gameID);
	}

	public function getClaimerPlayer(): AbstractPlayer {
		return Player::getPlayer($this->claimerID, $this->gameID);
	}

	/**
	 * @return bool Whether or not the database was updated
	 */
	public function update(): bool {
		if (!$this->hasChanged) {
			return false;
		}
		$db = Database::getInstance();
		if ($this->credits > 0 || $this->smrCredits > 0) {
			$db->replace('bounty', [
				'account_id' => $this->targetID,
				'bounty_id' => $this->bountyID,
				'game_id' => $this->gameID,
				'type' => $this->type->value,
				'time' => $this->time,
				'claimer_id' => $this->claimerID,
				'amount' => $this->credits,
				'smr_credits' => $this->smrCredits,
			]);
		} else {
			$db->delete('bounty', [
				'bounty_id' => $this->bountyID,
				'account_id' => $this->targetID,
				'game_id' => $this->gameID,
			]);
		}
		$this->hasChanged = false;
		return true;
	}

}
