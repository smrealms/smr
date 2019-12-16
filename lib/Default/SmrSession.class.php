<?php declare(strict_types=1);

if (!defined('USING_AJAX')) {
	define('USING_AJAX', false);
}

class SmrSession {
	const ALWAYS_AVAILABLE = 999999;
	const TIME_BEFORE_EXPIRY = 3600;

	// Defines the number of pages that can be loaded after
	// this page before the links on this page become invalid
	// (i.e. before you get a back button error).
	private const URL_DEFAULT_REMAINING_PAGE_LOADS = array(
			'alliance_broadcast.php' => self::ALWAYS_AVAILABLE,
			'alliance_forces.php' => self::ALWAYS_AVAILABLE,
			'alliance_list.php' => self::ALWAYS_AVAILABLE,
			'alliance_message_view.php' => self::ALWAYS_AVAILABLE,
			'alliance_message.php' => self::ALWAYS_AVAILABLE,
			'alliance_mod.php' => self::ALWAYS_AVAILABLE,
			'alliance_option.php' => self::ALWAYS_AVAILABLE,
			'alliance_pick.php' => self::ALWAYS_AVAILABLE,
			'alliance_remove_member.php' => self::ALWAYS_AVAILABLE,
			'alliance_roster.php' => self::ALWAYS_AVAILABLE,
			'beta_functions.php' => self::ALWAYS_AVAILABLE,
			'bug_report.php' => self::ALWAYS_AVAILABLE,
			'cargo_dump.php' => self::ALWAYS_AVAILABLE,
			'council_list.php' => self::ALWAYS_AVAILABLE,
			'course_plot.php' => self::ALWAYS_AVAILABLE,
			'changelog_view.php' => self::ALWAYS_AVAILABLE,
			'chat_rules.php' => self::ALWAYS_AVAILABLE,
			'chess_play.php' => self::ALWAYS_AVAILABLE,
			'combat_log_list.php' => self::ALWAYS_AVAILABLE,
			'combat_log_viewer.php' => self::ALWAYS_AVAILABLE,
			'current_sector.php' => self::ALWAYS_AVAILABLE,
			'configure_hardware.php' => self::ALWAYS_AVAILABLE,
			'contact.php' => self::ALWAYS_AVAILABLE,
			'council_embassy.php' => self::ALWAYS_AVAILABLE,
			'council_list.php' => self::ALWAYS_AVAILABLE,
			'council_politics.php' => self::ALWAYS_AVAILABLE,
			'council_send_message.php' => self::ALWAYS_AVAILABLE,
			'council_vote.php' => self::ALWAYS_AVAILABLE,
			'current_players.php' => self::ALWAYS_AVAILABLE,
			'donation.php' => self::ALWAYS_AVAILABLE,
			'feature_request_comments.php' => self::ALWAYS_AVAILABLE,
			'feature_request.php' => self::ALWAYS_AVAILABLE,
			'forces_list.php' => self::ALWAYS_AVAILABLE,
			'forces_mass_refresh.php' => self::ALWAYS_AVAILABLE,
			'government.php' => 1,
			'hall_of_fame_player_new.php' => self::ALWAYS_AVAILABLE,
			'hall_of_fame_player_detail.php' => self::ALWAYS_AVAILABLE,
			'leave_newbie.php' => self::ALWAYS_AVAILABLE,
			'logoff.php' => self::ALWAYS_AVAILABLE,
			'map_local.php' => self::ALWAYS_AVAILABLE,
			'message_view.php' => self::ALWAYS_AVAILABLE,
			'message_send.php' => self::ALWAYS_AVAILABLE,
			'news_read_advanced.php' => self::ALWAYS_AVAILABLE,
			'news_read_current.php' => 1,
			'news_read.php' => self::ALWAYS_AVAILABLE,
			'planet_construction.php' => self::ALWAYS_AVAILABLE,
			'planet_defense.php' => self::ALWAYS_AVAILABLE,
			'planet_financial.php' => self::ALWAYS_AVAILABLE,
			'planet_main.php' => self::ALWAYS_AVAILABLE,
			'planet_ownership.php' => self::ALWAYS_AVAILABLE,
			'planet_stockpile.php' => self::ALWAYS_AVAILABLE,
			'planet_list.php' => self::ALWAYS_AVAILABLE,
			'planet_list_financial.php' => self::ALWAYS_AVAILABLE,
			'preferences.php' => self::ALWAYS_AVAILABLE,
			'rankings_alliance_death.php' => self::ALWAYS_AVAILABLE,
			'rankings_alliance_experience.php' => self::ALWAYS_AVAILABLE,
			'rankings_alliance_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_alliance_vs_alliance.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_death.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_experience.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_profit.php' => self::ALWAYS_AVAILABLE,
			'rankings_race_death.php' => self::ALWAYS_AVAILABLE,
			'rankings_race_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_race.php' => self::ALWAYS_AVAILABLE,
			'rankings_sector_kill.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_view.php' => self::ALWAYS_AVAILABLE,
			'sector_scan.php' => self::ALWAYS_AVAILABLE,
			'trader_bounties.php' => self::ALWAYS_AVAILABLE,
			'trader_relations.php' => self::ALWAYS_AVAILABLE,
			'trader_savings.php' => self::ALWAYS_AVAILABLE,
			'trader_search_result.php' => self::ALWAYS_AVAILABLE,
			'trader_search.php' => self::ALWAYS_AVAILABLE,
			'trader_status.php' => self::ALWAYS_AVAILABLE,
			'weapon_reorder.php' => self::ALWAYS_AVAILABLE,
			//Processing pages
			'alliance_message_add_processing.php' => self::ALWAYS_AVAILABLE,
			'alliance_message_delete_processing.php' => self::ALWAYS_AVAILABLE,
			'alliance_pick_processing.php' => self::ALWAYS_AVAILABLE,
			'chess_move_processing.php' => self::ALWAYS_AVAILABLE,
			'toggle_processing.php' => self::ALWAYS_AVAILABLE,
			//Admin pages
			'account_edit.php' => self::ALWAYS_AVAILABLE,
			'album_moderate.php' => self::ALWAYS_AVAILABLE,
			'box_view.php' => self::ALWAYS_AVAILABLE,
			'changelog.php' => self::ALWAYS_AVAILABLE,
			'comp_share.php' => self::ALWAYS_AVAILABLE,
			'form_open.php' => self::ALWAYS_AVAILABLE,
			'ip_view_results.php' => self::ALWAYS_AVAILABLE,
			'ip_view.php' => self::ALWAYS_AVAILABLE,
			'permission_manage.php' => self::ALWAYS_AVAILABLE,
			'word_filter.php' => self::ALWAYS_AVAILABLE,
			//Uni gen
			'1.6/universe_create_locations.php' => self::ALWAYS_AVAILABLE,
			'1.6/universe_create_planets.php' => self::ALWAYS_AVAILABLE,
			'1.6/universe_create_ports.php' => self::ALWAYS_AVAILABLE,
			'1.6/universe_create_sector_details.php' => self::ALWAYS_AVAILABLE,
			'1.6/universe_create_sectors.php' => self::ALWAYS_AVAILABLE,
			'1.6/universe_create_warps.php' => self::ALWAYS_AVAILABLE,
		);

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

	protected static $db;

	private static $session_id;
	private static $game_id;
	private static $var;
	private static $commonIDs;
	private static $generate;
	private static $SN = '';
	private static $lastSN;
	private static $account_id;
	public static $last_accessed;

	protected static $previousAjaxReturns;
	protected static $ajaxReturns = array();

	public static function init() {
		// Return immediately if the SmrSession is already initialized
		if (isset(self::$session_id)) {
			return;
		}

		// Initialize the db connector here, since `init` is always called
		self::$db = new SmrMySqlDatabase();

		// now try the cookie
		if (isset($_COOKIE['session_id']) && strlen($_COOKIE['session_id']) === 32) {
			self::$session_id = $_COOKIE['session_id'];
		}
		else {
			// create a new session id
			do {
				self::$session_id = md5(uniqid(strval(rand())));
				self::$db->query('SELECT 1 FROM active_session WHERE session_id = ' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
			} while (self::$db->nextRecord()); //Make sure we haven't somehow clashed with someone else's session.
			if (!defined('NPC_SCRIPT')) {
				setcookie('session_id', self::$session_id);
			}
		}

		// try to get current session
		self::$db->query('DELETE FROM active_session WHERE last_accessed < ' . self::$db->escapeNumber(time() - self::TIME_BEFORE_EXPIRY));
		self::fetchVarInfo();

		if (!USING_AJAX && isset($_REQUEST['sn']) && isset(self::$var[$_REQUEST['sn']]) && !empty(self::$var[$_REQUEST['sn']])) {
			$var = self::$var[$_REQUEST['sn']];
			$currentPage = $var['url'] == 'skeleton.php' ? $var['body'] : $var['url'];
			$loadDelay = isset(self::URL_LOAD_DELAY[$currentPage]) ? self::URL_LOAD_DELAY[$currentPage] : 0;
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

	public static function fetchVarInfo() {
		self::$db->query('SELECT * FROM active_session WHERE session_id = ' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
		if (self::$db->nextRecord()) {
			self::$generate = false;
			self::$session_id = self::$db->getField('session_id');
			self::$account_id = self::$db->getInt('account_id');
			self::$game_id = self::$db->getInt('game_id');
			self::$last_accessed = self::$db->getInt('last_accessed');
			self::$commonIDs = array();
			self::$lastSN = self::$db->getField('last_sn');
			// We may not have ajax_returns if ajax was disabled
			$ajaxReturns = self::$db->getField('ajax_returns');
			if (!empty($ajaxReturns)) {
				self::$previousAjaxReturns = unserialize(gzuncompress($ajaxReturns));
			}
			self::$var = self::$db->getField('session_var');
			if (self::$var != '') {
				self::$var = unserialize(gzuncompress(self::$var));
			}
			if (!is_array(self::$var)) {
				self::$account_id = 0;
				self::$game_id = 0;
				self::$var = array();
			}
			else {
				foreach (self::$var as $key => &$value) {
					if ($value['Expires'] > 0 && $value['Expires'] <= TIME) { // Use 0 for infinity
						//This link is no longer valid
						unset(self::$var[$key]);
					}
					else if ($value['RemainingPageLoads'] < 0) {
						//This link is no longer valid
						unset(self::$var[$key]);
					}
					else {
						--$value['RemainingPageLoads'];
						if (isset($value['CommonID'])) {
							self::$commonIDs[$value['CommonID']] = $key;
						}
					}
				} unset($value);
			}
		}
		else {
			self::$generate = true;
			self::$account_id = 0;
			self::$game_id = 0;
			self::$var = array();
			self::$commonIDs = array();
		}
	}

	public static function update() {
		foreach (self::$var as $key => &$value) {
			if ($value['RemainingPageLoads'] <= 0) {
				//This link was valid this load but will not be in the future, removing it now saves database space and data transfer.
				unset(self::$var[$key]);
			}
		} unset($value);
		$compressed = gzcompress(serialize(self::$var));
		if (!self::$generate) {
			self::$db->query('UPDATE active_session SET account_id=' . self::$db->escapeNumber(self::$account_id) . ',game_id=' . self::$db->escapeNumber(self::$game_id) . (!USING_AJAX ? ',last_accessed=' . self::$db->escapeNumber(TIME) : '') . ',session_var=' . self::$db->escapeBinary($compressed) .
					',last_sn=' . self::$db->escapeString(self::$SN) .
					' WHERE session_id=' . self::$db->escapeString(self::$session_id) . (USING_AJAX ? ' AND last_sn=' . self::$db->escapeString(self::$lastSN) : '') . ' LIMIT 1');
		}
		else {
			self::$db->query('DELETE FROM active_session WHERE account_id = ' . self::$db->escapeNumber(self::$account_id) . ' AND game_id = ' . self::$db->escapeNumber(self::$game_id));
			self::$db->query('INSERT INTO active_session (session_id, account_id, game_id, last_accessed, session_var) VALUES(' . self::$db->escapeString(self::$session_id) . ',' . self::$db->escapeNumber(self::$account_id) . ',' . self::$db->escapeNumber(self::$game_id) . ',' . self::$db->escapeNumber(TIME) . ',' . self::$db->escapeBinary($compressed) . ')');
			self::$generate = false;
		}
	}

	/**
	 * Returns the Game ID associated with the session.
	 */
	public static function getGameID() {
		return self::$game_id;
	}

	/**
	 * Returns true if the session is inside a game, false otherwise.
	 */
	public static function hasGame() {
		return self::$game_id != 0;
	}

	public static function hasAccount() {
		return self::$account_id > 0;
	}

	public static function getAccountID() {
		return self::$account_id;
	}

	public static function getAccount() {
		return SmrAccount::getAccount(self::$account_id);
	}

	/**
	 * Sets the `account_id` attribute of this session.
	 */
	public static function setAccount(AbstractSmrAccount $account) {
		self::$account_id = $account->getAccountID();
	}

	/**
	 * Updates the `game_id` attribute of the session and deletes any other
	 * active sessions in this game for this account.
	 */
	public static function updateGame($gameID) {
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
	public static function hasChangedSN() {
		return self::$SN != self::$lastSN;
	}

	private static function updateSN() {
		if (!USING_AJAX) {
			self::$db->query('UPDATE active_session SET last_sn=' . self::$db->escapeString(self::$SN) .
				' WHERE session_id=' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
		}
	}

	public static function destroy() {
		self::$db->query('UPDATE active_session SET account_id=0,game_id=0,session_var=\'\',ajax_returns=\'\' WHERE session_id = ' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
		self::$session_id = '';
		self::$account_id = 0;
		self::$game_id = 0;
	}

	/**
	 * Retrieve the session var for the page given by $sn.
	 * If $sn is not specified, use the current page (i.e. self::$SN).
	 */
	public static function retrieveVar($sn = null) {
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
	 * Gets a var from $_REQUEST (or $default) and then stores it in the
	 * session so that it can still be retrieved when the page auto-refreshes.
	 * This is the recommended way to get $_REQUEST data.
	 */
	public static function getRequestVar($varName, $default = null) {
		global $var;
		// Set the session var, if in $_REQUESTS or if a default is given
		if (isset($_REQUEST[$varName])) {
			self::updateVar($varName, $_REQUEST[$varName]);
		} elseif (isset($default) && !isset($var[$varName])) {
			self::updateVar($varName, $default);
		}
		// Return the possibly updated session var
		if (isset($var[$varName])) {
			return $var[$varName];
		}
	}

	public static function resetLink($container, $sn) { //Do not allow sharing SN, useful for forwarding.
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

	public static function updateVar($key, $value) {
		global $var;
		if ($value === null) {
			unset($var[$key]);
			unset(self::$var[self::$SN][$key]);
		}
		else {
			$var[$key] = $value;
			self::$var[self::$SN][$key] = $value;
		}
	}

	public static function clearLinks() {
		self::$var = array(self::$SN => self::$var[self::$SN]);
		self::$commonIDs = array();
	}

	protected static function addLink($container, $sn = false) { // Container['ID'] MUST be unique to a specific action, if there will be two different outcomes from containers given the same ID then problems will likely arise.
		if (!isset($container['Expires'])) {
			$container['Expires'] = 0; // Lasts forever
		}
		if (!isset($container['RemainingPageLoads'])) {
			$pageURL = $container['url'] == 'skeleton.php' ? $container['body'] : $container['url'];
			$container['RemainingPageLoads'] = isset(self::URL_DEFAULT_REMAINING_PAGE_LOADS[$pageURL]) ? self::URL_DEFAULT_REMAINING_PAGE_LOADS[$pageURL] : 1; // Allow refreshing
		}

		if ($sn === false) {
			$sn = self::generateSN($container);
		}
		else {
			// If we've been provided an SN to use then copy over the existing 'PreviousRequestTime'
			$container['PreviousRequestTime'] = self::$var[$sn]['PreviousRequestTime'];
		}
		self::$var[$sn] = $container;
		return $sn;
	}

	protected static function generateSN(&$container) {
		$container['CommonID'] = self::getCommonID($container);
		if (isset(self::$commonIDs[$container['CommonID']])) {
			$sn = self::$commonIDs[$container['CommonID']];
			$container['PreviousRequestTime'] = isset(self::$var[$sn]) ? self::$var[$sn]['PreviousRequestTime'] : MICRO_TIME;
		}
		else {
			do {
				$sn = substr(md5(strval(rand())), 0, 8);
			} while (isset(self::$var[$sn]));
			$container['PreviousRequestTime'] = MICRO_TIME;
		}
		self::$commonIDs[$container['CommonID']] = $sn;
		return $sn;
	}

	protected static function getCommonID($commonContainer) {
		unset($commonContainer['Expires']);
		unset($commonContainer['RemainingPageLoads']);
		unset($commonContainer['PreviousRequestTime']);
		unset($commonContainer['CommonID']);
		// NOTE: This ID will change if the order of elements in the container
		// changes. If this causes unnecessary SN changes, sort the container!
		return md5(serialize($commonContainer));
	}

	public static function getNewHREF($container, $forceFullURL = false) {
		$sn = self::addLink($container);
		if ($forceFullURL === true || stripos($_SERVER['REQUEST_URI'], 'loader.php') === false) {
			return '/loader.php?sn=' . $sn;
		}
		return '?sn=' . $sn;
	}

	public static function addAjaxReturns($element, $contents) {
		self::$ajaxReturns[$element] = $contents;
		return isset(self::$previousAjaxReturns[$element]) && self::$previousAjaxReturns[$element] == $contents;
	}

	public static function saveAjaxReturns() {
		if (empty(self::$ajaxReturns)) {
			return;
		}
		$compressed = gzcompress(serialize(self::$ajaxReturns));
		self::$db->query('UPDATE active_session SET ajax_returns=' . self::$db->escapeBinary($compressed) .
				' WHERE session_id=' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
	}
}

SmrSession::init();
