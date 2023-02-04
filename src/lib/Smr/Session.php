<?php declare(strict_types=1);

namespace Smr;

use Smr\Container\DiContainer;
use Smr\Page\Page;
use Smr\Pages\Player\AttackPlayerProcessor;
use Smr\Pages\Player\ExamineTrader;
use Smr\Pages\Player\ForcesDrop;
use Smr\Pages\Player\ForcesDropProcessor;
use Smr\Pages\Player\ForcesRefreshProcessor;
use Smr\Pages\Player\HardwareConfigure;
use Smr\Pages\Player\SectorJumpProcessor;
use Smr\Pages\Player\SectorMoveProcessor;
use Smr\Pages\Player\SectorScan;
use Smr\Pages\Player\ShopGoodsProcessor;

class Session {

	private const TIME_BEFORE_EXPIRY = 172800; // 2 days

	private const URL_LOAD_DELAY = [
		HardwareConfigure::class => .4,
		ForcesDrop::class => .4,
		ForcesDropProcessor::class => .5,
		ForcesRefreshProcessor::class => .4,
		SectorJumpProcessor::class => .4,
		SectorMoveProcessor::class => .4,
		SectorScan::class => .4,
		ShopGoodsProcessor::class => .4,
		AttackPlayerProcessor::class => .75,
		ExamineTrader::class => .75,
	];

	private string $sessionID;
	private int $gameID;
	/** @var array<string, Page> */
	private array $links = [];
	private ?Page $currentPage = null;
	/** @var array<string, mixed> */
	private array $requestData = [];
	private bool $generate;
	public readonly bool $ajax;
	private string $SN;
	private string $lastSN;
	private int $accountID;
	private float $lastAccessed;

	/** @var ?array<string, string> */
	protected ?array $previousAjaxReturns;
	/** @var array<string, string> */
	protected array $ajaxReturns = [];

	/**
	 * Return the Smr\Session in the DI container.
	 * If one does not exist yet, it will be created.
	 * This is the intended way to construct this class.
	 */
	public static function getInstance(): self {
		return DiContainer::get(self::class);
	}

	/**
	 * Smr\Session constructor.
	 * Not intended to be constructed by hand. Use Smr\Session::getInstance().
	 */
	public function __construct() {
		$db = Database::getInstance();

		// now try the cookie
		$idLength = 32;
		if (isset($_COOKIE['session_id']) && strlen($_COOKIE['session_id']) === $idLength) {
			$this->sessionID = $_COOKIE['session_id'];
		} else {
			// create a new session id
			do {
				$this->sessionID = random_string($idLength);
				$dbResult = $db->read('SELECT 1 FROM active_session WHERE session_id = ' . $db->escapeString($this->sessionID));
			} while ($dbResult->hasRecord()); //Make sure we haven't somehow clashed with someone else's session.

			// This is a minor hack to make sure that setcookie is not called
			// for CLI programs and tests (to avoid "headers already sent").
			if (headers_sent() === false) {
				setcookie('session_id', $this->sessionID);
			}
		}

		// Delete any expired sessions
		$db->write('DELETE FROM active_session WHERE last_accessed < ' . $db->escapeNumber(time() - self::TIME_BEFORE_EXPIRY));

		// try to get current session
		$this->ajax = Request::getInt('ajax', 0) === 1;
		$this->SN = Request::get('sn', '');
		$this->fetchVarInfo();

		if (!$this->ajax && $this->hasCurrentVar()) {
			$file = $this->getCurrentVar()::class;
			$loadDelay = self::URL_LOAD_DELAY[$file] ?? 0;
			$timeBetweenLoads = microtime(true) - $this->lastAccessed;
			if ($timeBetweenLoads < $loadDelay) {
				$sleepTime = IRound(($loadDelay - $timeBetweenLoads) * 1000000);
				//echo 'Sleeping for: ' . $sleepTime . 'us';
				usleep($sleepTime);
			}
			if (ENABLE_DEBUG) {
				$db->insert('debug', [
					'debug_type' => $db->escapeString('Delay: ' . $file),
					'account_id' => $db->escapeNumber($this->accountID),
					'value' => $db->escapeNumber($timeBetweenLoads),
				]);
			}
		}
	}

	public function fetchVarInfo(): void {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM active_session WHERE session_id = ' . $db->escapeString($this->sessionID));
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$this->generate = false;
			$this->sessionID = $dbRecord->getString('session_id');
			$this->accountID = $dbRecord->getInt('account_id');
			$this->gameID = $dbRecord->getInt('game_id');
			$this->lastAccessed = $dbRecord->getFloat('last_accessed');
			$this->lastSN = $dbRecord->getString('last_sn');
			// We may not have ajax_returns if ajax was disabled
			$this->previousAjaxReturns = $dbRecord->getObject('ajax_returns', true, true);

			[$this->links, $lastPage, $lastRequestData] = $dbRecord->getObject('session_var', true);

			$ajaxRefresh = $this->ajax && !$this->hasChangedSN();
			if ($ajaxRefresh) {
				$this->currentPage = $lastPage;
				$this->requestData = $lastRequestData;
			} elseif (isset($this->links[$this->SN])) {
				// If the current page is modified during page processing, we need
				// to make sure the original link is unchanged. So we clone it here.
				$this->currentPage = clone $this->links[$this->SN];
			} elseif (!$this->hasChangedSN()) {
				// If we're manually refreshing the page (F5), but the SN is not
				// reusable, it is safe to use the previous displayed page.
				$this->currentPage = $lastPage;
			}

			if (!$ajaxRefresh) { // since form pages don't ajax refresh properly
				foreach ($this->links as $sn => $link) {
					if ($link->isLinkReusable() === false) {
						// This link is no longer valid
						unset($this->links[$sn]);
					}
				}
			}
		} else {
			$this->generate = true;
			$this->accountID = 0;
			$this->gameID = 0;
		}
	}

	public function update(): void {
		$sessionVar = [$this->links, $this->currentPage, $this->requestData];
		$db = Database::getInstance();
		if (!$this->generate) {
			$db->write('UPDATE active_session SET account_id=' . $db->escapeNumber($this->accountID) . ',game_id=' . $db->escapeNumber($this->gameID) . (!$this->ajax ? ',last_accessed=' . $db->escapeNumber(Epoch::microtime()) : '') . ',session_var=' . $db->escapeObject($sessionVar, true) .
					',last_sn=' . $db->escapeString($this->SN) .
					' WHERE session_id=' . $db->escapeString($this->sessionID) . ($this->ajax ? ' AND last_sn=' . $db->escapeString($this->lastSN) : ''));
		} else {
			$db->write('DELETE FROM active_session WHERE account_id = ' . $db->escapeNumber($this->accountID) . ' AND game_id = ' . $db->escapeNumber($this->gameID));
			$db->insert('active_session', [
				'session_id' => $db->escapeString($this->sessionID),
				'account_id' => $db->escapeNumber($this->accountID),
				'game_id' => $db->escapeNumber($this->gameID),
				'last_accessed' => $db->escapeNumber(Epoch::microtime()),
				'session_var' => $db->escapeObject($sessionVar, true),
			]);
			$this->generate = false;
		}
	}

	/**
	 * Uniquely identifies the session in the database.
	 */
	public function getSessionID(): string {
		return $this->sessionID;
	}

	/**
	 * Returns the Game ID associated with the session.
	 */
	public function getGameID(): int {
		return $this->gameID;
	}

	/**
	 * Returns true if the session is inside a game, false otherwise.
	 */
	public function hasGame(): bool {
		return $this->gameID != 0;
	}

	public function hasAccount(): bool {
		return $this->accountID > 0;
	}

	public function getAccountID(): int {
		return $this->accountID;
	}

	public function getAccount(): Account {
		return Account::getAccount($this->accountID);
	}

	public function getPlayer(bool $forceUpdate = false): AbstractPlayer {
		return Player::getPlayer($this->accountID, $this->gameID, $forceUpdate);
	}

	/**
	 * Sets the `accountID` attribute of this session.
	 */
	public function setAccount(Account $account): void {
		$this->accountID = $account->getAccountID();
	}

	/**
	 * Updates the `gameID` attribute of the session and deletes any other
	 * active sessions in this game for this account.
	 */
	public function updateGame(int $gameID): void {
		if ($this->gameID == $gameID) {
			return;
		}
		$this->gameID = $gameID;
		$db = Database::getInstance();
		$db->write('DELETE FROM active_session WHERE account_id = ' . $db->escapeNumber($this->accountID) . ' AND game_id = ' . $this->gameID);
		$db->write('UPDATE active_session SET game_id=' . $db->escapeNumber($this->gameID) . ' WHERE session_id=' . $db->escapeString($this->sessionID));
	}

	/**
	 * The SN is the URL parameter that defines the page being requested.
	 */
	public function getSN(): string {
		return $this->SN;
	}

	/**
	 * Returns true if the current SN is different than the previous SN.
	 */
	public function hasChangedSN(): bool {
		return $this->SN != $this->lastSN;
	}

	public function destroy(): void {
		$db = Database::getInstance();
		$db->write('DELETE FROM active_session WHERE session_id = ' . $db->escapeString($this->sessionID));
		unset($this->sessionID);
		unset($this->accountID);
		unset($this->gameID);
	}

	public function getLastAccessed(): float {
		return $this->lastAccessed;
	}

	/**
	 * Check if the session has a var associated with the current SN.
	 */
	public function hasCurrentVar(): bool {
		return $this->currentPage !== null;
	}

	/**
	 * Returns the session var associated with the current SN.
	 */
	public function getCurrentVar(): Page {
		return $this->currentPage;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getRequestData(): array {
		return $this->requestData;
	}

	/**
	 * Gets a var from $var, $_REQUEST, or $default. Then stores it in the
	 * session so that it can still be retrieved when the page auto-refreshes.
	 * This is the recommended way to get $_REQUEST data for display pages.
	 * For processing pages, see the Request class.
	 */
	public function getRequestVar(string $varName, string $default = null): string {
		$result = Request::getVar($varName, $default);
		$this->requestData[$varName] = $result;
		return $result;
	}

	public function getRequestVarInt(string $varName, int $default = null): int {
		$result = Request::getVarInt($varName, $default);
		$this->requestData[$varName] = $result;
		return $result;
	}

	/**
	 * @param ?array<int> $default
	 * @return array<int>
	 */
	public function getRequestVarIntArray(string $varName, array $default = null): array {
		$result = Request::getVarIntArray($varName, $default);
		$this->requestData[$varName] = $result;
		return $result;
	}

	/**
	 * Replace the global $var with the given $container.
	 */
	public function setCurrentVar(Page $container): void {
		$this->currentPage = $container;
	}

	public function clearLinks(): void {
		$this->links = [];
	}

	/**
	 * Add a page to the session so that it can be used on next page load.
	 * It will be associated with an SN that will be used for linking.
	 */
	public function addLink(Page $container): string {
		// If we already had a link to this exact page, use the existing SN for it.
		foreach ($this->links as $sn => $link) {
			if (objects_equal($link, $container)) {
				return $sn;
			}
		}
		// This page isn't an existing link, so give it a new SN.
		// Don't allow it to be the current SN, even if it's no longer valid,
		// so that we can guarantee that an unchanged SN implies the same page.
		do {
			$sn = random_alphabetic_string(6);
		} while (isset($this->links[$sn]) && $sn === $this->SN);
		$this->links[$sn] = $container;
		return $sn;
	}

	public function addAjaxReturns(string $element, string $contents): bool {
		$this->ajaxReturns[$element] = $contents;
		return isset($this->previousAjaxReturns[$element]) && $this->previousAjaxReturns[$element] == $contents;
	}

	public function saveAjaxReturns(): void {
		if (empty($this->ajaxReturns)) {
			return;
		}
		$db = Database::getInstance();
		$db->write('UPDATE active_session SET ajax_returns=' . $db->escapeObject($this->ajaxReturns, true) .
				' WHERE session_id=' . $db->escapeString($this->sessionID));
	}

}
