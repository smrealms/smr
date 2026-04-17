<?php declare(strict_types=1);

namespace Smr;

use Exception;

class Bounty {

	/**
	 * Maximum amount of bounty.credits in the database
	 */
	private const int MAX_CREDITS = SQL_MAX_UNSIGNED_INT;

	/**
	 * Returns a list of all active (not claimable) bounties for given location $type.
	 *
	 * @return array<self>
	 */
	public static function getMostWanted(BountyType $type, int $gameID): array {
		$db = Database::getInstance();
		$dbResult = $db->select(
			'bounty',
			[
				'game_id' => $gameID,
				'type' => $type->value,
				'claimer_id' => 0,
			],
			orderBy: ['amount'],
			order: ['DESC'],
		);
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
		$dbResult = $db->select('bounty', $player->SQLID);
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
		$table = 'bounty';
		$sqlParams = [
			'claimer_id' => $player->getAccountID(),
			'game_id' => $player->getGameID(),
		];
		if ($type === null) {
			$dbResult = $db->select($table, $sqlParams);
		} else {
			$dbResult = $db->select($table, ['type' => $type->value, ...$sqlParams]);
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
		return $this->claimerID === 0;
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
		if ($this->credits === $credits || $credits > self::MAX_CREDITS) {
			return;
		}
		$this->credits = $credits;
		$this->hasChanged = true;
	}

	private function setSmrCredits(int $smrCredits): void {
		if ($this->smrCredits === $smrCredits) {
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
