<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\Container\DiContainer;
use Smr\Exceptions\UserError;

/**
 * This class is responsible for ensuring that page processing occurs
 * sequentially as requests come in, avoiding race conditions by using a
 * blocking queue.
 *
 * The lock creates exclusive access to a specified sector, since that is
 * the primary way (caveat: but not the _only_ way) in which simultaneous
 * processing would create inconsistencies.
 *
 * There are more elegant solutions to this problem (such a row-level
 * locking within the database). There are also some deficiencies in this
 * implementation, in particular the inability for a single player to lock
 * two sectors at once (to avoid, e.g., race conditions with movement).
 */
class SectorLock {

	/**
	 * The max time (in seconds) for a lock to be active before being
	 * considered stale.
	 */
	private const int LOCK_DURATION = 10;

	/**
	 * The max time (in seconds) to retry acquiring locks before giving up.
	 * Should be less than LOCK_DURATION.
	 */
	private const int RETRY_DURATION = 5;

	private ?int $lockID = null;
	private bool $failed = false;

	private ?int $gameID = null;
	private ?int $accountID = null;
	private ?int $sectorID = null;

	/**
	 * Convenience wrapper to acquire a lock for the player in their current sector.
	 */
	public function acquireForPlayer(AbstractPlayer $player): bool {
		return $this->acquire($player->getGameID(), $player->getAccountID(), $player->getSectorID());
	}

	/**
	 * Acquire an exclusive lock on a sector.
	 *
	 * @return bool True if a new lock is acquired or false if existing lock used.
	 */
	public function acquire(int $gameID, int $accountID, int $sectorID): bool {
		// Skip if we already have the lock
		if ($this->isActive()) {
			if ($gameID !== $this->gameID || $accountID !== $this->accountID || $sectorID !== $this->sectorID) {
				throw new Exception('This instance has an active lock in a different sector!');
			}
			return false;
		}

		// Abort if we've already failed to acquire a lock
		if ($this->hasFailed()) {
			throw new Exception('Cannot reacquire locks after failing!');
		}

		// Save lock info for sanity checking future calls to this method.
		$this->gameID = $gameID;
		$this->accountID = $accountID;
		$this->sectorID = $sectorID;

		// Insert ourselves into the queue.
		$db = Database::getInstance();
		$this->lockID = $db->insertAutoIncrement('locks_queue', [
			'game_id' => $gameID,
			'account_id' => $accountID,
			'sector_id' => $sectorID,
			'timestamp' => Epoch::time(),
		]);

		// Return once we are next in the queue.
		for ($i = 0; $i < 250; ++$i) {
			if (time() - Epoch::time() > self::RETRY_DURATION) {
				break;
			}

			$staleLockEpoch = Epoch::time() - self::LOCK_DURATION;
			$query = 'SELECT COUNT(*) FROM locks_queue WHERE
				sector_id = :sector_id
				AND game_id = :game_id
				AND timestamp > :stale_time';
			$sqlParams = [
				'sector_id' => $db->escapeNumber($sectorID),
				'game_id' => $db->escapeNumber($gameID),
				'stale_time' => $db->escapeNumber($staleLockEpoch),
			];

			// If there is someone else before us in the queue we sleep for a while
			$dbResult = $db->read($query . ' AND lock_id < :lock_id', [
				...$sqlParams,
				'lock_id' => $db->escapeNumber($this->lockID),
			]);
			$locksInQueue = $dbResult->record()->getInt('COUNT(*)');
			if ($locksInQueue === 0) {
				return true;
			}

			// We can only have one lock in the queue, anything more means someone is screwing around
			$dbResult = $db->read($query . ' AND account_id = :account_id', [
				...$sqlParams,
				'account_id' => $db->escapeNumber($accountID),
			]);
			if ($dbResult->record()->getInt('COUNT(*)') > 1) {
				$this->setFailed();
				throw new UserError('Multiple actions cannot be performed at the same time!');
			}

			// Wait 0.025 seconds for each lock in this sector
			usleep(25000 * $locksInQueue);
		}

		$this->setFailed();
		throw new Exception('Sector lock acquisition timed out!');
	}

	public function getSectorID(): int {
		if ($this->sectorID === null) {
			throw new Exception('Must acquire lock before calling this method!');
		}
		return $this->sectorID;
	}

	/**
	 * @phpstan-assert-if-true !null $this->lockID
	 */
	public function isActive(): bool {
		return $this->lockID !== null;
	}

	private function setFailed(): void {
		$this->release();
		$this->failed = true;
	}

	public function hasFailed(): bool {
		return $this->failed;
	}

	/**
	 * @return bool Whether there was a lock to release or not
	 */
	public function release(): bool {
		if (!$this->isActive()) {
			return false;
		}
		// Delete this lock (and any stale locks)
		$db = Database::getInstance();
		$db->write('DELETE from locks_queue WHERE lock_id = :lock_id OR timestamp < :lock_time', [
			'lock_id' => $db->escapeNumber($this->lockID),
			'lock_time' => $db->escapeNumber(Epoch::time() - self::LOCK_DURATION),
		]);

		$this->lockID = null;
		return true;
	}

	/**
	 * Returns the instance of this class from the DI container.
	 * The first time this is called, it will populate the DI container.
	 */
	public static function getInstance(): self {
		return DiContainer::getClass(self::class);
	}

	/**
	 * Reset the instance of this class associated with this page request
	 * (i.e. stored in the DI container).
	 *
	 * NOTE: This should never be called by normal page requests, and should
	 * only be used by the CLI programs that run continuously.
	 */
	public static function resetInstance(): void {
		if (DiContainer::getContainer()->get('NPC_SCRIPT') === false) {
			throw new Exception('Only call this function from CLI programs!');
		}
		// Release before resetting
		self::getInstance()->release();
		DiContainer::getContainer()->set(self::class, new self());
	}

}
