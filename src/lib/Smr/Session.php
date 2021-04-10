<?php declare(strict_types=1);

namespace Smr;

use AbstractSmrAccount;
use AbstractSmrPlayer;
use Page;
use Request;
use Smr\Container\DiContainer;
use Smr\Database;
use Smr\Epoch;
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
	private string $SN = '';
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
				$this->db->query('SELECT 1 FROM active_session WHERE session_id = ' . $this->db->escapeString($this->sessionID) . ' LIMIT 1');
			} while ($this->db->nextRecord()); //Make sure we haven't somehow clashed with someone else's session.

			// This is a minor hack to make sure that setcookie is not called
			// for CLI programs and tests (to avoid "headers already sent").
			if (headers_sent() === false) {
				setcookie('session_id', $this->sessionID);
			}
		}

		// try to get current session
		$this->db->query('DELETE FROM active_session WHERE last_accessed < ' . $this->db->escapeNumber(time() - self::TIME_BEFORE_EXPIRY));
		$this->fetchVarInfo();

		$sn = Request::get('sn', '');
		if (!USING_AJAX && !empty($sn) && !empty($this->var[$sn])) {
			$var = $this->var[$sn];
			$currentPage = $var['url'] == 'skeleton.php' ? $var['body'] : $var['url'];
			$loadDelay = self::URL_LOAD_DELAY[$currentPage] ?? 0;
			$initialTimeBetweenLoads = microtime(true) - $var['PreviousRequestTime'];
			while (($timeBetweenLoads = microtime(true) - $var['PreviousRequestTime']) < $loadDelay) {
				$sleepTime = IRound(($loadDelay - $timeBetweenLoads) * 1000000);
			//	echo 'Sleeping for: ' . $sleepTime . 'us';
				usleep($sleepTime);
			}
			if (ENABLE_DEBUG) {
				$this->db->query('INSERT INTO debug VALUES (' . $this->db->escapeString('Delay: ' . $currentPage) . ',' . $this->db->escapeNumber($this->accountID) . ',' . $this->db->escapeNumber($initialTimeBetweenLoads) . ',' . $this->db->escapeNumber($timeBetweenLoads) . ')');
			}
		}
	}

	public function fetchVarInfo() : void {
		$this->db->query('SELECT * FROM active_session WHERE session_id = ' . $this->db->escapeString($this->sessionID) . ' LIMIT 1');
		if ($this->db->nextRecord()) {
			$this->generate = false;
			$this->sessionID = $this->db->getField('session_id');
			$this->accountID = $this->db->getInt('account_id');
			$this->gameID = $this->db->getInt('game_id');
			$this->lastAccessed = $this->db->getInt('last_accessed');
			$this->lastSN = $this->db->getField('last_sn');
			// We may not have ajax_returns if ajax was disabled
			$this->previousAjaxReturns = $this->db->getObject('ajax_returns', true, true);

			$this->var = $this->db->getObject('session_var', true);

			foreach ($this->var as $key => $value) {
				if ($value['RemainingPageLoads'] < 0) {
					//This link is no longer valid
					unset($this->var[$key]);
				} else {
					$this->var[$key]['RemainingPageLoads'] -= 1;
					if (isset($value['CommonID'])) {
						$this->commonIDs[$value['CommonID']] = $key;
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
			$this->db->query('UPDATE active_session SET account_id=' . $this->db->escapeNumber($this->accountID) . ',game_id=' . $this->db->escapeNumber($this->gameID) . (!USING_AJAX ? ',last_accessed=' . $this->db->escapeNumber(Epoch::time()) : '') . ',session_var=' . $this->db->escapeObject($this->var, true) .
					',last_sn=' . $this->db->escapeString($this->SN) .
					' WHERE session_id=' . $this->db->escapeString($this->sessionID) . (USING_AJAX ? ' AND last_sn=' . $this->db->escapeString($this->lastSN) : '') . ' LIMIT 1');
		} else {
			$this->db->query('DELETE FROM active_session WHERE account_id = ' . $this->db->escapeNumber($this->accountID) . ' AND game_id = ' . $this->db->escapeNumber($this->gameID));
			$this->db->query('INSERT INTO active_session (session_id, account_id, game_id, last_accessed, session_var) VALUES(' . $this->db->escapeString($this->sessionID) . ',' . $this->db->escapeNumber($this->accountID) . ',' . $this->db->escapeNumber($this->gameID) . ',' . $this->db->escapeNumber(Epoch::time()) . ',' . $this->db->escapeObject($this->var, true) . ')');
			$this->generate = false;
		}
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
		$this->db->query('DELETE FROM active_session WHERE account_id = ' . $this->db->escapeNumber($this->accountID) . ' AND game_id = ' . $this->gameID);
		$this->db->query('UPDATE active_session SET game_id=' . $this->db->escapeNumber($this->gameID) . ' WHERE session_id=' . $this->db->escapeString($this->sessionID));
	}

	/**
	 * Returns true if the current SN is different than the previous SN.
	 */
	public function hasChangedSN() : bool {
		return $this->SN != $this->lastSN;
	}

	private function updateSN() : void {
		if (!USING_AJAX) {
			$this->db->query('UPDATE active_session SET last_sn=' . $this->db->escapeString($this->SN) .
				' WHERE session_id=' . $this->db->escapeString($this->sessionID) . ' LIMIT 1');
		}
	}

	public function destroy() : void {
		$this->db->query('DELETE FROM active_session WHERE session_id = ' . $this->db->escapeString($this->sessionID));
		unset($this->sessionID);
		unset($this->accountID);
		unset($this->gameID);
	}

	public function getLastAccessed() : int {
		return $this->lastAccessed;
	}

	/**
	 * Check if the session has a var associated with the given $sn.
	 * If $sn is not specified, use the current SN (i.e. $this->SN).
	 */
	public function findCurrentVar(string $sn = null) : bool {
		if (is_null($sn)) {
			$sn = $this->SN;
		}
		if (empty($this->var[$sn])) {
			return false;
		}
		$this->SN = $sn;
		$this->updateSN();
		if (isset($this->var[$sn]['body']) && isset($this->var[$sn]['CommonID'])) {
//			if(preg_match('/processing/',$this->var[$sn]['body']))
			unset($this->commonIDs[$this->var[$sn]['CommonID']]); //Do not store common id for current page
			unset($this->var[$sn]['CommonID']);
		}

		$this->var[$sn]['RemainingPageLoads'] += 1; // Allow refreshing

		return true;
	}

	/**
	 * Returns the session var associated with the current SN.
	 * Must be called after Session::findCurrentVar sets the current SN.
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
		$this->updateVar($varName, $result);
		return $result;
	}

	public function getRequestVarInt(string $varName, int $default = null) : int {
		$result = Request::getVarInt($varName, $default);
		$this->updateVar($varName, $result);
		return $result;
	}

	public function getRequestVarIntArray(string $varName, array $default = null) : array {
		$result = Request::getVarIntArray($varName, $default);
		$this->updateVar($varName, $result);
		return $result;
	}

	public function resetLink(Page $container, string $sn) : string {
		//Do not allow sharing SN, useful for forwarding.
		global $lock;
		if (isset($this->var[$sn]['CommonID'])) {
			unset($this->commonIDs[$this->var[$sn]['CommonID']]); //Do not store common id for reset page, to allow refreshing to always give the same page in response
		}
		$this->SN = $sn;
		if (!isset($container['RemainingPageLoads'])) {
			$container['RemainingPageLoads'] = 1; // Allow refreshing
		}
		if (!isset($container['PreviousRequestTime'])) {
			if (isset($this->var[$sn]['PreviousRequestTime'])) {
				$container['PreviousRequestTime'] = $this->var[$sn]['PreviousRequestTime']; // Copy across the previous request time if not explicitly set.
			}
		}

		$this->var[$sn] = $container;
		if (!$lock && !USING_AJAX) {
			$this->update();
		}
		return $sn;
	}

	public function updateVar(string $key, mixed $value) : void {
		global $var;
		if ($value === null) {
			if (isset($var[$key])) {
				unset($var[$key]);
			}
			if (isset($var[$this->SN][$key])) {
				unset($this->var[$this->SN][$key]);
			}
		} else {
			$var[$key] = $value;
			$this->var[$this->SN][$key] = $value;
		}
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
		$this->db->query('UPDATE active_session SET ajax_returns=' . $this->db->escapeObject($this->ajaxReturns, true) .
				' WHERE session_id=' . $this->db->escapeString($this->sessionID) . ' LIMIT 1');
	}
}
