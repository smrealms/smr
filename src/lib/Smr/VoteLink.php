<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\Pages\Player\VoteLinkProcessor;

/**
 * Site-independent handling of links to external game voting sites.
 * This is used to award free turns to players for voting.
 */
class VoteLink {

	public const TIME_BETWEEN_VOTING = 84600; // 23.5 hours

	/** @var ?array<int, int> */
	private static ?array $CACHE_TIMEOUTS = null;

	/** @var array<string, mixed> */
	public readonly array $data;

	public function __construct(
		public readonly VoteSite $site,
		public readonly int $accountID,
		public readonly int $gameID,
	) {
		$this->data = $site->getData();
	}

	public static function clearCache(): void {
		self::$CACHE_TIMEOUTS = null;
	}

	/**
	 * Returns the earliest time (in seconds) until free turns
	 * are available across all voting sites.
	 */
	public static function getMinTimeUntilFreeTurns(int $accountID, int $gameID): int {
		$waitTimes = [];
		foreach (VoteSite::cases() as $site) {
			$link = new self($site, $accountID, $gameID);
			if ($link->givesFreeTurns()) {
				$waitTimes[] = $link->getTimeUntilFreeTurns();
			}
		}
		if (count($waitTimes) === 0) {
			throw new Exception('No enabled vote sites give free turns!');
		}
		return min($waitTimes);
	}

	/**
	 * Does this VoteSite have a voting callback that can be used
	 * to award free turns?
	 */
	public function givesFreeTurns(): bool {
		return isset($this->data['img_star']);
	}

	/**
	 * Time until the account can get free turns from voting at this site.
	 * If the time is 0, this site is eligible for free turns.
	 */
	public function getTimeUntilFreeTurns(bool $forceUpdate = false): int {
		if (!$this->givesFreeTurns()) {
			throw new Exception('This vote site cannot award free turns!');
		}

		// Populate timeout cache from the database
		if ($forceUpdate || !isset(self::$CACHE_TIMEOUTS)) {
			self::$CACHE_TIMEOUTS = []; // ensure this is set
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT link_id, timeout FROM vote_links WHERE account_id=' . $db->escapeNumber($this->accountID));
			foreach ($dbResult->records() as $dbRecord) {
				// 'timeout' is the last time the player claimed free turns (or 0, if unclaimed)
				self::$CACHE_TIMEOUTS[$dbRecord->getInt('link_id')] = $dbRecord->getInt('timeout');
			}
		}

		// If not in the vote_link database, this site is eligible now.
		$lastClaimTime = self::$CACHE_TIMEOUTS[$this->site->value] ?? 0;
		return max(0, $lastClaimTime + self::TIME_BETWEEN_VOTING - Epoch::time());
	}

	/**
	 * Register that the player has clicked on a vote site that is eligible
	 * for free turns, so that we will accept incoming votes. This ensures
	 * that voting is done through an authenticated SMR session.
	 */
	public function setClicked(): void {
		$db = Database::getInstance();
		$db->lockTable('vote_links');
		try {
			if (!$this->freeTurnsReady(true)) {
				// Player has clicked a free turns link after already getting
				// free turns. This should not occur naturally.
				throw new Exception('Account ID ' . $this->accountID . ' attempted vote link abuse');
			}

			// Don't start the timeout until the vote actually goes through.
			$db->replace('vote_links', [
				'account_id' => $db->escapeNumber($this->accountID),
				'link_id' => $db->escapeNumber($this->site->value),
				'timeout' => $db->escapeNumber(0),
				'turns_claimed' => $db->escapeBoolean(false),
			]);
		} finally {
			$db->unlock();
		}
	}

	/**
	 * Register that the player has been awarded their free turns.
	 *
	 * @return bool True if account was eligible for free turns (i.e. in the setClicked state).
	 */
	public function setFreeTurnsAwarded(): bool {
		$db = Database::getInstance();
		$db->write('UPDATE vote_links SET timeout = ' . Epoch::time() . ', turns_claimed = ' . $db->escapeBoolean(true) . ' WHERE account_id = ' . $db->escapeNumber($this->accountID) . ' AND link_id = ' . $db->escapeNumber($this->site->value) . ' AND timeout = 0 AND turns_claimed = ' . $db->escapeBoolean(false));
		return $db->getChangedRows() === 1;
	}

	/**
	 * Returns true if account can currently receive free turns at this site.
	 */
	public function freeTurnsReady(bool $forceUpdate = false): bool {
		return $this->givesFreeTurns() && $this->gameID != 0 && $this->getTimeUntilFreeTurns($forceUpdate) <= 0;
	}

	/**
	 * Returns the image to display for this voting site.
	 */
	public function getImg(): string {
		if (!$this->freeTurnsReady()) {
			return $this->data['img_default'];
		}
		return $this->data['img_star'];
	}

	/**
	 * Returns the URL that should be used for this voting site.
	 */
	public function getUrl(): string {
		if (!$this->freeTurnsReady()) {
			return $this->data['url_base'];
		}
		return $this->data['url_func']($this->data['url_base'], $this->accountID, $this->gameID);
	}

	/**
	 * Returns the SN to redirect the current page to if free turns are
	 * available; otherwise, returns false.
	 */
	public function getSN(): string|false {
		if (!$this->freeTurnsReady()) {
			return false;
		}
		// This page will prepare the account for the voting callback.
		return (new VoteLinkProcessor($this->site))->href();
	}

}
