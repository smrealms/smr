<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Page;

/**
 * Handles links to external game voting sites.
 */
class VoteSite {

	private static ?array $CACHE_TIMEOUTS = null;

	// NOTE: link IDs should never be changed!
	public const LINK_ID_TWG = 3;
	public const LINK_ID_DOG = 4;
	public const LINK_ID_PBBG = 5;

	public const ACTIVE_LINKS = [
		self::LINK_ID_TWG,
		self::LINK_ID_DOG,
		self::LINK_ID_PBBG,
	];

	// MPOGD no longer exists
	//1 => array('default_img' => 'mpogd.png', 'star_img' => 'mpogd_vote.png', 'base_url' => 'http://www.mpogd.com/games/game.asp?ID=1145'),
	// OMGN no longer do voting - the link actually just redirects to archive site.
	//2 => array('default_img' => 'omgn.png', 'star_img' => 'omgn_vote.png', 'base_url' => 'http://www.omgn.com/topgames/vote.php?Game_ID=30'),

	private static function getSiteData(int $linkID): array {
		// This can't be a static/constant attribute due to `url_func` closures.
		return match ($linkID) {
			self::LINK_ID_TWG => [
				'img_default' => 'twg.png',
				'img_star' => 'twg_vote.png',
				'url_base' => 'http://topwebgames.com/in.aspx?ID=136',
				'url_func' => function($baseUrl, $accountId, $gameId, $linkId) {
					$query = ['account' => $accountId, 'game' => $gameId, 'link' => $linkId, 'alwaysreward' => 1];
					return $baseUrl . '&' . http_build_query($query);
				},
			],
			self::LINK_ID_DOG => [
				'img_default' => 'dog.png',
				'img_star' => 'dog_vote.png',
				'url_base' => 'http://www.directoryofgames.com/main.php?view=topgames&action=vote&v_tgame=2315',
				'url_func' => function($baseUrl, $accountId, $gameId, $linkId) {
					return "$baseUrl&votedef=$accountId,$gameId,$linkId";
				},
			],
			self::LINK_ID_PBBG => [
				'img_default' => 'pbbg.png',
				'url_base' => 'https://pbbg.com/games/space-merchant-realms',
			],
		};
	}

	public static function clearCache(): void {
		self::$CACHE_TIMEOUTS = null;
	}

	public static function getSite(int $linkID, int $accountID) {
		return new self($linkID, $accountID, self::getSiteData($linkID));
	}

	public static function getAllSites(int $accountID): array {
		$allSites = [];
		foreach (self::ACTIVE_LINKS as $linkID) {
			$allSites[$linkID] = self::getSite($linkID, $accountID);
		}
		return $allSites;
	}

	/**
	 * Returns the earliest time (in seconds) until free turns
	 * are available across all voting sites.
	 */
	public static function getMinTimeUntilFreeTurns(int $accountID): int {
		$waitTimes = [];
		foreach (self::getAllSites($accountID) as $site) {
			if ($site->givesFreeTurns()) {
				$waitTimes[] = $site->getTimeUntilFreeTurns();
			}
		}
		return min($waitTimes);
	}

	private function __construct(
		private int $linkID,
		private int $accountID,
		private array $data) {}

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
	public function getTimeUntilFreeTurns(): int {
		if (!$this->givesFreeTurns()) {
			throw new Exception('This vote site cannot award free turns!');
		}

		// Populate timeout cache from the database
		if (!isset(self::$CACHE_TIMEOUTS)) {
			self::$CACHE_TIMEOUTS = []; // ensure this is set
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT link_id, timeout FROM vote_links WHERE account_id=' . $db->escapeNumber($this->accountID) . ' AND link_id IN (' . $db->escapeArray(self::ACTIVE_LINKS) . ')');
			foreach ($dbResult->records() as $dbRecord) {
				// 'timeout' is the last time the player claimed free turns (or 0, if unclaimed)
				self::$CACHE_TIMEOUTS[$dbRecord->getInt('link_id')] = $dbRecord->getInt('timeout');
			}
		}

		// If not in the vote_link database, this site is eligible now.
		$lastClaimTime = self::$CACHE_TIMEOUTS[$this->linkID] ?? 0;
		return $lastClaimTime + TIME_BETWEEN_VOTING - Epoch::time();
	}

	/**
	 * Returns true if account can currently receive free turns at this site.
	 */
	private function freeTurnsReady(int $gameID): bool {
		return $this->givesFreeTurns() && $gameID != 0 && $this->getTimeUntilFreeTurns() <= 0;
	}

	/**
	 * Register that the player has clicked on a vote site that is eligible
	 * for free turns, so that we will accept incoming votes. This ensures
	 * that voting is done through an authenticated SMR session.
	 */
	public function setLinkClicked(): void {
		// We assume that the site is eligible for free turns.
		// Don't start the timeout until the vote actually goes through.
		$db = Database::getInstance();
		$db->write('REPLACE INTO vote_links (account_id, link_id, timeout, turns_claimed) VALUES(' . $db->escapeNumber($this->accountID) . ',' . $db->escapeNumber($this->linkID) . ',' . $db->escapeNumber(0) . ',' . $db->escapeBoolean(false) . ')');
	}

	/**
	 * Checks if setLinkClicked has been called since the last time
	 * free turns were awarded.
	 */
	public function isLinkClicked(): bool {
		// This is intentionally not cached so that we can re-check as needed.
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM vote_links WHERE account_id = ' . $db->escapeNumber($this->accountID) . ' AND link_id = ' . $db->escapeNumber($this->linkID) . ' AND timeout = 0 AND turns_claimed = ' . $db->escapeBoolean(false));
		return $dbResult->hasRecord();
	}

	/**
	 * Register that the player has been awarded their free turns.
	 */
	public function setFreeTurnsAwarded(): void {
		$db = Database::getInstance();
		$db->write('REPLACE INTO vote_links (account_id, link_id, timeout, turns_claimed) VALUES(' . $db->escapeNumber($this->accountID) . ',' . $db->escapeNumber($this->linkID) . ',' . $db->escapeNumber(Epoch::time()) . ',' . $db->escapeBoolean(true) . ')');
	}

	/**
	 * Returns the image to display for this voting site.
	 */
	public function getLinkImg(int $gameID): string {
		if (!$this->freeTurnsReady($gameID)) {
			return $this->data['img_default'];
		}
		return $this->data['img_star'];
	}

	/**
	 * Returns the URL that should be used for this voting site.
	 */
	public function getLinkUrl(int $gameID): string {
		$baseUrl = $this->data['url_base'];
		if (!$this->freeTurnsReady($gameID)) {
			return $baseUrl;
		}
		return $this->data['url_func']($baseUrl, $this->accountID, $gameID, $this->linkID);
	}

	/**
	 * Returns the SN to redirect the current page to if free turns are
	 * available; otherwise, returns false.
	 */
	public function getSN(int $gameID): string|false {
		if (!$this->freeTurnsReady($gameID)) {
			return false;
		}
		// This page will prepare the account for the voting callback.
		$container = Page::create('vote_link.php');
		$container['link_id'] = $this->linkID;
		return $container->href();
	}

}
