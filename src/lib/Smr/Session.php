<?php declare(strict_types=1);

namespace Smr;

use AbstractSmrAccount;
use AbstractSmrPlayer;
use Page;
use Smr\Container\DiContainer;
use Smr\Database;
use Smr\Epoch;
use Smr\Request;
use SmrAccount;
use SmrPlayer;

class Session {

	const TIME_BEFORE_EXPIRY = 3600;

	private const URL_LOAD_DELAY = array(
		'configure_hardware.php' => .4,
		'forces_drop.php' => .4,
		'forces_drop_processing.php' => .5,
		'forces_refresh_processing.php' => .4,
		'sector_jump_processing.php' => .4,
		'sector_move_processing.php' => .4,
		'sector_scan.php' => .4,
		'shop_goods_processing.php' => .4,
		'trader_attack_processing.php' => .75,
		'trader_examine.php' => .75
	);

	protected Database $db;

	private string $sessionID;
	private int $gameID;
	private array $var;
	private array $commonIDs = [];
	private bool $generate;
	private string $SN;
	private string $lastSN;
	private int $accountID;
	private int $lastAccessed;

	protected ?array $previousAjaxReturns;
	protected array $ajaxReturns = array();

	/**
	 * Return the Smr\Session in the DI container.
	 * If one does not exist yet, it will be created.
	 * This is the intended way to construct this class.
	 */
	public static function getInstance() : self {
		return DiContainer::get(self::class);
	}

	/**
	 * Smr\Session constructor.
	 * Not intended to be constructed by hand. Use Smr\Session::getInstance().
	 */
	public function __construct() {

		// Initialize the db connector here
		$this->db = Database::getInstance();

		// now try the cookie
		if (isset($_COOKIE['session_id']) && strlen($_COOKIE['session_id']) === 32) {
			$this->sessionID = $_COOKIE['session_id'];
		} else {
			// create a new session id
			do {
				$this->sessionID = md5(uniqid(strval(rand())));
				$dbResult = $this->db->read('SELECT 1 FROM active_session WHERE session_id = ' . $this->db->escapeString($this->sessionID) . ' LIMIT 1');
			} while ($dbResult->hasRecord()); //Make sure we haven't somehow clashed with someone else's session.

			// This is a minor hack to make sure that setcookie is not called
			// for CLI programs and tests (to avoid "headers already sent").
			if (headers_sent() === false) {
				setcookie('session_id', $this->sessionID);
			}
		}

		// Delete any expired sessions
		$this->db->write('DELETE FROM active_session WHERE last_accessed < ' . $this->db->escapeNumber(time() - self::TIME_BEFORE_EXPIRY));

		// try to get current session
		$this->SN = Request::get('sn', '');
		$this->fetchVarInfo();

		if (!USING_AJAX && !empty($this->SN) && !empty($this->var[$this->SN])) {
			$var = $this->var[$this->SN];
			$currentPage = $var['url'] == 'skeleton.php' ? $var['body'] : $var['url'];
			$loadDelay = self::URL_LOAD_DELAY[$currentPage] ?? 0;
			$initialTimeBetweenLoads = microtime(true) - $var['PreviousRequestTime'];
			while (($timeBetweenLoads = microtime(true) - $var['PreviousRequestTime']) < $loadDelay) {
				$sleepTime = IRound(($loadDelay - $timeBetweenLoads) * 1000000);
			//	echo 'Sleeping for: ' . $sleepTime . 'us';
				usleep($sleepTime);
			}
			if (ENABLE_DEBUG) {
				$this->db->insert('debug', [
					'debug_type' => $this->db->escapeString('Delay: ' . $currentPage),
					'account_id' => $this->db->escapeNumber($this->accountID),
					'value' => $this->db->escapeNumber($initialTimeBetweenLoads),
					'value_2' => $this->db->escapeNumber($timeBetweenLoads),
				]);
			}
		}
	}

	public function fetchVarInfo() : void {
		$dbResult = $this->db->read('SELECT * FROM active_session WHERE session_id = ' . $this->db->escapeString($this->sessionID) . ' LIMIT 1');
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$this->generate = false;
			$this->sessionID = $dbRecord->getField('session_id');
			$this->accountID = $dbRecord->getInt('account_id');
			$this->gameID = $dbRecord->getInt('game_id');
			$this->lastAccessed = $dbRecord->getInt('last_accessed');
			$this->lastSN = $dbRecord->getField('last_sn');
			// We may not have ajax_returns if ajax was disabled
			$this->previousAjaxReturns = $dbRecord->getObject('ajax_returns', true, true);

			$this->var = $dbRecord->getObject('session_var', true);

			foreach ($this->var as $key => $value) {
				if ($value['RemainingPageLoads'] < 0) {
					//This link is no longer valid
					unset($this->var[$key]);
				} else {
					// The following is skipped for the current SN, because:
					// a) If we decremented RemainingPageLoads, we wouldn't be
					//    able to refresh the current page.
					// b) If we register its CommonID and then subsequently
					//    modify its data (which is quite common for the
					//    "current var"), the CommonID is not updated. Then any
					//    var with the same data as the original will wrongly
					//    share its CommonID.
					if ($key !== $this->SN) {
						$this->var[$key]['RemainingPageLoads'] -= 1;
						if (isset($value['CommonID'])) {
							$this->commonIDs[$value['CommonID']] = $key;
						}
					}
				}
			}
		} else {
			$this->generate = true;
			$this->accountID = 0;
			$this->gameID = 0;
			$this->var = array();
		}
	}

	public function update() : void {
		foreach ($this->var as $key => $value) {
			if ($value['RemainingPageLoads'] <= 0) {
				//This link was valid this load but will not be in the future, removing it now saves database space and data transfer.
				unset($this->var[$key]);
			}
		}
		if (!$this->generate) {
			$this->db->write('UPDATE active_session SET account_id=' . $this->db->escapeNumber($this->accountID) . ',game_id=' . $this->db->escapeNumber($this->gameID) . (!USING_AJAX ? ',last_accessed=' . $this->db->escapeNumber(Epoch::time()) : '') . ',session_var=' . $this->db->escapeObject($this->var, true) .
					',last_sn=' . $this->db->escapeString($this->SN) .
					' WHERE session_id=' . $this->db->escapeString($this->sessionID) . (USING_AJAX ? ' AND last_sn=' . $this->db->escapeString($this->lastSN) : '') . ' LIMIT 1');
		} else {
			$this->db->write('DELETE FROM active_session WHERE account_id = ' . $this->db->escapeNumber($this->accountID) . ' AND game_id = ' . $this->db->escapeNumber($this->gameID));
			$this->db->insert('active_session', [
				'session_id' => $this->db->escapeString($this->sessionID),
				'account_id' => $this->db->escapeNumber($this->accountID),
				'game_id' => $this->db->escapeNumber($this->gameID),
				'last_accessed' => $this->db->escapeNumber(Epoch::time()),
				'session_var' => $this->db->escapeObject($this->var, true),
			]);
			$this->generate = false;
		}
	}

	/**
	 * Uniquely identifies the session in the database.
	 */
	public function getSessionID() : string {
		return $this->sessionID;
	}

	/**
	 * Returns the Game ID associated with the session.
	 */
	public function getGameID() : int {
		return $this->gameID;
	}

	/**
	 * Returns true if the session is inside a game, false otherwise.
	 */
	public function hasGame() : bool {
		return $this->gameID != 0;
	}

	public function hasAccount() : bool {
		return $this->accountID > 0;
	}

	public function getAccountID() : int {
		return $this->accountID;
	}

	public function getAccount() : AbstractSmrAccount {
		return SmrAccount::getAccount($this->accountID);
	}

	public function getPlayer(bool $forceUpdate = false) : AbstractSmrPlayer {
		return SmrPlayer::getPlayer($this->accountID, $this->gameID, $forceUpdate);
	}

	/**
	 * Sets the `accountID` attribute of this session.
	 */
	public function setAccount(AbstractSmrAccount $account) : void {
		$this->accountID = $account->getAccountID();
	}

	/**
	 * Updates the `gameID` attribute of the session and deletes any other
	 * active sessions in this game for this account.
	 */
	public function updateGame(int $gameID) : void {
		if ($this->gameID == $gameID) {
			return;
		}
		$this->gameID = $gameID;
		$this->db->write('DELETE FROM active_session WHERE account_id = ' . $this->db->escapeNumber($this->accountID) . ' AND game_id = ' . $this->gameID);
		$this->db->write('UPDATE active_session SET game_id=' . $this->db->escapeNumber($this->gameID) . ' WHERE session_id=' . $this->db->escapeString($this->sessionID));
	}

	/**
	 * The SN is the URL parameter that defines the page being requested.
	 */
	public function getSN() : string {
		return $this->SN;
	}

	/**
	 * Returns true if the current SN is different than the previous SN.
	 */
	public function hasChangedSN() : bool {
		return $this->SN != $this->lastSN;
	}

	public function destroy() : void {
		$this->db->write('DELETE FROM active_session WHERE session_id = ' . $this->db->escapeString($this->sessionID));
		unset($this->sessionID);
		unset($this->accountID);
		unset($this->gameID);
	}

	public function getLastAccessed() : int {
		return $this->lastAccessed;
	}

	/**
	 * Check if the session has a var associated with the current SN.
	 */
	public function hasCurrentVar() : bool {
		return isset($this->var[$this->SN]);
	}

	/**
	 * Returns the session var associated with the current SN.
	 */
	public function getCurrentVar() : Page {
		return $this->var[$this->SN];
	}

	/**
	 * Gets a var from $var, $_REQUEST, or $default. Then stores it in the
	 * session so that it can still be retrieved when the page auto-refreshes.
	 * This is the recommended way to get $_REQUEST data for display pages.
	 * For processing pages, see the Request class.
	 */
	public function getRequestVar(string $varName, string $default = null) : string {
		$result = Request::getVar($varName, $default);
		$var = $this->getCurrentVar();
		$var[$varName] = $result;
		return $result;
	}

	public function getRequestVarInt(string $varName, int $default = null) : int {
		$result = Request::getVarInt($varName, $default);
		$var = $this->getCurrentVar();
		$var[$varName] = $result;
		return $result;
	}

	public function getRequestVarIntArray(string $varName, array $default = null) : array {
		$result = Request::getVarIntArray($varName, $default);
		$var = $this->getCurrentVar();
		$var[$varName] = $result;
		return $result;
	}

	/**
	 * Replace the global $var with the given $container.
	 */
	public function setCurrentVar(Page $container) : void {
		$var = $this->hasCurrentVar() ? $this->getCurrentVar() : null;

		//Do not allow sharing SN, useful for forwarding.
		if (isset($var['CommonID'])) {
			unset($this->commonIDs[$var['CommonID']]); //Do not store common id for reset page, to allow refreshing to always give the same page in response
		}
		if (!isset($container['RemainingPageLoads'])) {
			$container['RemainingPageLoads'] = 1; // Allow refreshing
		}
		if (!isset($container['PreviousRequestTime'])) {
			if (isset($var['PreviousRequestTime'])) {
				$container['PreviousRequestTime'] = $var['PreviousRequestTime']; // Copy across the previous request time if not explicitly set.
			}
		}

		$this->var[$this->SN] = $container;
	}

	public function clearLinks() : void {
		$this->var = array($this->SN => $this->var[$this->SN]);
		$this->commonIDs = array();
	}

	public function addLink(Page $container) : string {
		$sn = $this->generateSN($container);
		$this->var[$sn] = $container;
		return $sn;
	}

	protected function generateSN(Page $container) : string {
		if (isset($this->commonIDs[$container['CommonID']])) {
			$sn = $this->commonIDs[$container['CommonID']];
			$container['PreviousRequestTime'] = isset($this->var[$sn]) ? $this->var[$sn]['PreviousRequestTime'] : Epoch::microtime();
		} else {
			do {
				$sn = random_alphabetic_string(6);
			} while (isset($this->var[$sn]));
			$container['PreviousRequestTime'] = Epoch::microtime();
		}
		$this->commonIDs[$container['CommonID']] = $sn;
		return $sn;
	}

	public function addAjaxReturns(string $element, string $contents) : bool {
		$this->ajaxReturns[$element] = $contents;
		return isset($this->previousAjaxReturns[$element]) && $this->previousAjaxReturns[$element] == $contents;
	}

	public function saveAjaxReturns() : void {
		if (empty($this->ajaxReturns)) {
			return;
		}
		$this->db->write('UPDATE active_session SET ajax_returns=' . $this->db->escapeObject($this->ajaxReturns, true) .
				' WHERE session_id=' . $this->db->escapeString($this->sessionID) . ' LIMIT 1');
	}
}
