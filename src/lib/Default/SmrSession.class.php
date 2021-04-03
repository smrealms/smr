<?php declare(strict_types=1);

if (!defined('USING_AJAX')) {
	define('USING_AJAX', false);
}

class SmrSession {

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

	protected static MySqlDatabase $db;

	private static ?string $session_id;
	private static int $game_id;
	private static array $var;
	private static array $commonIDs = [];
	private static bool $generate;
	private static string $SN = '';
	private static string $lastSN;
	private static int $account_id;
	public static int $last_accessed;
	private static Time $pageRequestTime;

	protected static ?array $previousAjaxReturns;
	protected static array $ajaxReturns = array();

	/**
	 * Returns the time (in seconds) associated with this page request.
	 */
	public static function getTime() : int {
		return self::$pageRequestTime->getTime();
	}

	/**
	 * Returns the time (in seconds, with microsecond-level precision)
	 * associated with this page request.
	 */
	public static function getMicroTime() : float {
		return self::$pageRequestTime->getMicroTime();
	}

	/**
	 * Update the time associated with this page request.
	 *
	 * NOTE: This should never be called by normal page requests, and should
	 * only be used by the CLI programs that run continuously.
	 */
	public static function updateTime() : void {
		if (!defined('NPC_SCRIPT')) {
			throw new Exception('Only call this function from CLI programs!');
		}
		self::$pageRequestTime = new Time();
	}

	public static function init() : void {
		// Return immediately if the SmrSession is already initialized
		if (isset(self::$session_id)) {
			return;
		}

		// Initialize the page request time
		self::$pageRequestTime = new Time();

		// Initialize the db connector here, since `init` is always called
		self::$db = MySqlDatabase::getInstance();

		// now try the cookie
		if (isset($_COOKIE['session_id']) && strlen($_COOKIE['session_id']) === 32) {
			self::$session_id = $_COOKIE['session_id'];
		} else {
			// create a new session id
			do {
				self::$session_id = md5(uniqid(strval(rand())));
				self::$db->query('SELECT 1 FROM active_session WHERE session_id = ' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
			} while (self::$db->nextRecord()); //Make sure we haven't somehow clashed with someone else's session.

			// This is a minor hack to make sure that setcookie is not called
			// for CLI programs and tests (to avoid "headers already sent").
			if (headers_sent() === false) {
				setcookie('session_id', self::$session_id);
			}
		}

		// try to get current session
		self::$db->query('DELETE FROM active_session WHERE last_accessed < ' . self::$db->escapeNumber(time() - self::TIME_BEFORE_EXPIRY));
		self::fetchVarInfo();

		$sn = Request::get('sn', '');
		if (!USING_AJAX && !empty($sn) && !empty(self::$var[$sn])) {
			$var = self::$var[$sn];
			$currentPage = $var['url'] == 'skeleton.php' ? $var['body'] : $var['url'];
			$loadDelay = self::URL_LOAD_DELAY[$currentPage] ?? 0;
			$initialTimeBetweenLoads = microtime(true) - $var['PreviousRequestTime'];
			while (($timeBetweenLoads = microtime(true) - $var['PreviousRequestTime']) < $loadDelay) {
				$sleepTime = IRound(($loadDelay - $timeBetweenLoads) * 1000000);
			//	echo 'Sleeping for: ' . $sleepTime . 'us';
				usleep($sleepTime);
			}
			if (ENABLE_DEBUG) {
				self::$db->query('INSERT INTO debug VALUES (' . self::$db->escapeString('Delay: ' . $currentPage) . ',' . self::$db->escapeNumber(self::$account_id) . ',' . self::$db->escapeNumber($initialTimeBetweenLoads) . ',' . self::$db->escapeNumber($timeBetweenLoads) . ')');
			}
		}
	}

	public static function fetchVarInfo() : void {
		self::$db->query('SELECT * FROM active_session WHERE session_id = ' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
		if (self::$db->nextRecord()) {
			self::$generate = false;
			self::$session_id = self::$db->getField('session_id');
			self::$account_id = self::$db->getInt('account_id');
			self::$game_id = self::$db->getInt('game_id');
			self::$last_accessed = self::$db->getInt('last_accessed');
			self::$lastSN = self::$db->getField('last_sn');
			// We may not have ajax_returns if ajax was disabled
			self::$previousAjaxReturns = self::$db->getObject('ajax_returns', true, true);

			self::$var = self::$db->getObject('session_var', true);

			foreach (self::$var as $key => $value) {
				if ($value['Expires'] > 0 && $value['Expires'] <= self::getTime()) { // Use 0 for infinity
					//This link is no longer valid
					unset(self::$var[$key]);
				} elseif ($value['RemainingPageLoads'] < 0) {
					//This link is no longer valid
					unset(self::$var[$key]);
				} else {
					--self::$var[$key]['RemainingPageLoads'];
					if (isset($value['CommonID'])) {
						self::$commonIDs[$value['CommonID']] = $key;
					}
				}
			}
		} else {
			self::$generate = true;
			self::$account_id = 0;
			self::$game_id = 0;
			self::$var = array();
		}
	}

	public static function update() : void {
		foreach (self::$var as $key => $value) {
			if ($value['RemainingPageLoads'] <= 0) {
				//This link was valid this load but will not be in the future, removing it now saves database space and data transfer.
				unset(self::$var[$key]);
			}
		}
		if (!self::$generate) {
			self::$db->query('UPDATE active_session SET account_id=' . self::$db->escapeNumber(self::$account_id) . ',game_id=' . self::$db->escapeNumber(self::$game_id) . (!USING_AJAX ? ',last_accessed=' . self::$db->escapeNumber(self::getTime()) : '') . ',session_var=' . self::$db->escapeObject(self::$var, true) .
					',last_sn=' . self::$db->escapeString(self::$SN) .
					' WHERE session_id=' . self::$db->escapeString(self::$session_id) . (USING_AJAX ? ' AND last_sn=' . self::$db->escapeString(self::$lastSN) : '') . ' LIMIT 1');
		} else {
			self::$db->query('DELETE FROM active_session WHERE account_id = ' . self::$db->escapeNumber(self::$account_id) . ' AND game_id = ' . self::$db->escapeNumber(self::$game_id));
			self::$db->query('INSERT INTO active_session (session_id, account_id, game_id, last_accessed, session_var) VALUES(' . self::$db->escapeString(self::$session_id) . ',' . self::$db->escapeNumber(self::$account_id) . ',' . self::$db->escapeNumber(self::$game_id) . ',' . self::$db->escapeNumber(self::getTime()) . ',' . self::$db->escapeObject(self::$var, true) . ')');
			self::$generate = false;
		}
	}

	/**
	 * Returns the Game ID associated with the session.
	 */
	public static function getGameID() : int {
		return self::$game_id;
	}

	/**
	 * Returns true if the session is inside a game, false otherwise.
	 */
	public static function hasGame() : bool {
		return self::$game_id != 0;
	}

	public static function hasAccount() : bool {
		return self::$account_id > 0;
	}

	public static function getAccountID() : int {
		return self::$account_id;
	}

	public static function getAccount() : SmrAccount {
		return SmrAccount::getAccount(self::$account_id);
	}

	/**
	 * Sets the `account_id` attribute of this session.
	 */
	public static function setAccount(AbstractSmrAccount $account) : void {
		self::$account_id = $account->getAccountID();
	}

	/**
	 * Updates the `game_id` attribute of the session and deletes any other
	 * active sessions in this game for this account.
	 */
	public static function updateGame(int $gameID) : void {
		if (self::$game_id == $gameID) {
			return;
		}
		self::$game_id = $gameID;
		self::$db->query('DELETE FROM active_session WHERE account_id = ' . self::$db->escapeNumber(self::$account_id) . ' AND game_id = ' . self::$game_id);
		self::$db->query('UPDATE active_session SET game_id=' . self::$db->escapeNumber(self::$game_id) . ' WHERE session_id=' . self::$db->escapeString(self::$session_id));
	}

	/**
	 * Returns true if the current SN is different than the previous SN.
	 */
	public static function hasChangedSN() : bool {
		return self::$SN != self::$lastSN;
	}

	private static function updateSN() : void {
		if (!USING_AJAX) {
			self::$db->query('UPDATE active_session SET last_sn=' . self::$db->escapeString(self::$SN) .
				' WHERE session_id=' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
		}
	}

	public static function destroy() : void {
		self::$db->query('DELETE FROM active_session WHERE session_id = ' . self::$db->escapeString(self::$session_id));
		self::$session_id = null;
		self::$account_id = 0;
		self::$game_id = 0;
	}

	/**
	 * Retrieve the session var for the page given by $sn.
	 * If $sn is not specified, use the current page (i.e. self::$SN).
	 */
	public static function retrieveVar(string $sn = null) : Page|false {
		if (is_null($sn)) {
			$sn = self::$SN;
		}
		if (empty(self::$var[$sn])) {
			return false;
		}
		self::$SN = $sn;
		SmrSession::updateSN();
		if (isset(self::$var[$sn]['body']) && isset(self::$var[$sn]['CommonID'])) {
//			if(preg_match('/processing/',self::$var[$sn]['body']))
			unset(self::$commonIDs[self::$var[$sn]['CommonID']]); //Do not store common id for current page
			unset(self::$var[$sn]['CommonID']);
		}

		self::$var[$sn]['RemainingPageLoads'] += 1; // Allow refreshing
		self::$var[$sn]['Expires'] = 0; // Allow refreshing forever
		return self::$var[$sn];
	}

	/**
	 * Gets a var from $var, $_REQUEST, or $default. Then stores it in the
	 * session so that it can still be retrieved when the page auto-refreshes.
	 * This is the recommended way to get $_REQUEST data for display pages.
	 * For processing pages, see the Request class.
	 */
	public static function getRequestVar(string $varName, string $default = null) : string {
		$result = Request::getVar($varName, $default);
		self::updateVar($varName, $result);
		return $result;
	}

	public static function getRequestVarInt(string $varName, int $default = null) : int {
		$result = Request::getVarInt($varName, $default);
		self::updateVar($varName, $result);
		return $result;
	}

	public static function getRequestVarIntArray(string $varName, array $default = null) : array {
		$result = Request::getVarIntArray($varName, $default);
		self::updateVar($varName, $result);
		return $result;
	}

	public static function resetLink(Page $container, string $sn) : string {
		//Do not allow sharing SN, useful for forwarding.
		global $lock;
		if (isset(self::$var[$sn]['CommonID'])) {
			unset(self::$commonIDs[self::$var[$sn]['CommonID']]); //Do not store common id for reset page, to allow refreshing to always give the same page in response
		}
		self::$SN = $sn;
		if (!isset($container['Expires'])) {
			$container['Expires'] = 0; // Lasts forever
		}
		if (!isset($container['RemainingPageLoads'])) {
			$container['RemainingPageLoads'] = 1; // Allow refreshing
		}
		if (!isset($container['PreviousRequestTime'])) {
			if (isset(self::$var[$sn]['PreviousRequestTime'])) {
				$container['PreviousRequestTime'] = self::$var[$sn]['PreviousRequestTime']; // Copy across the previous request time if not explicitly set.
			}
		}

		self::$var[$sn] = $container;
		if (!$lock && !USING_AJAX) {
			self::update();
		}
		return $sn;
	}

	public static function updateVar(string $key, mixed $value) : void {
		global $var;
		if ($value === null) {
			if (isset($var[$key])) {
				unset($var[$key]);
			}
			if (isset($var[self::$SN][$key])) {
				unset(self::$var[self::$SN][$key]);
			}
		} else {
			$var[$key] = $value;
			self::$var[self::$SN][$key] = $value;
		}
	}

	public static function clearLinks() : void {
		self::$var = array(self::$SN => self::$var[self::$SN]);
		self::$commonIDs = array();
	}

	public static function addLink(Page $container) : string {
		$sn = self::generateSN($container);
		self::$var[$sn] = $container;
		return $sn;
	}

	protected static function generateSN(Page $container) : string {
		if (isset(self::$commonIDs[$container['CommonID']])) {
			$sn = self::$commonIDs[$container['CommonID']];
			$container['PreviousRequestTime'] = isset(self::$var[$sn]) ? self::$var[$sn]['PreviousRequestTime'] : self::getMicroTime();
		} else {
			do {
				$sn = random_alphabetic_string(6);
			} while (isset(self::$var[$sn]));
			$container['PreviousRequestTime'] = self::getMicroTime();
		}
		self::$commonIDs[$container['CommonID']] = $sn;
		return $sn;
	}

	public static function addAjaxReturns(string $element, string $contents) : bool {
		self::$ajaxReturns[$element] = $contents;
		return isset(self::$previousAjaxReturns[$element]) && self::$previousAjaxReturns[$element] == $contents;
	}

	public static function saveAjaxReturns() : void {
		if (empty(self::$ajaxReturns)) {
			return;
		}
		self::$db->query('UPDATE active_session SET ajax_returns=' . self::$db->escapeObject(self::$ajaxReturns, true) .
				' WHERE session_id=' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
	}
}

SmrSession::init();
