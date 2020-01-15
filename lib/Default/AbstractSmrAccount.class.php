<?php declare(strict_types=1);

// Exception thrown when an account cannot be found in the database
class AccountNotFoundException extends Exception {}

abstract class AbstractSmrAccount {
	const USER_RANKINGS_EACH_STAT_POW = .9;
	const USER_RANKINGS_TOTAL_SCORE_POW = .3;
	const USER_RANKINGS_RANK_BOUNDARY = 5.2;
	protected const USER_RANKINGS_SCORE = array(
		// [Stat, a, b]
		// Used as: pow(Stat * a, USER_RANKINGS_EACH_STAT_POW) * b
		array(array('Trade', 'Experience', 'Total'), .1, 0.5),
		array(array('Trade', 'Money', 'Profit'), 0.00005, 0.5),
		array(array('Killing', 'Kills'), 1000, 1)
		);

	protected static $CACHE_ACCOUNTS = array();
	protected const DEFAULT_HOTKEYS = array(
		'MoveUp' => array('w', 'up'),
		'ScanUp' => array('shift+w', 'shift+up'),
		'MoveLeft' => array('a', 'left'),
		'ScanLeft' => array('shift+a', 'shift+left'),
		'MoveRight' => array('d', 'right'),
		'ScanRight' => array('shift+d', 'shift+right'),
		'MoveDown' => array('s', 'down'),
		'ScanDown' => array('shift+s', 'shift+down'),
		'MoveWarp' => array('e', '0'),
		'ScanWarp' => array('shift+e', 'shift+0'),
		'ScanCurrent' => array('shift+1'),
		'CurrentSector' => array('1'),
		'LocalMap' => array('2'),
		'PlotCourse' => array('3'),
		'CurrentPlayers' => array('4'),
		'EnterPort' => array('q'),
		'AttackTrader' => array('f')
	);

	protected $db;

	protected $account_id;
	protected $login;
	protected $passwordHash;
	protected $email;
	protected $validated;
	protected $validation_code;
	protected $last_login;
	protected $hofName;
	protected $discordId;
	protected $ircNick;
	protected $veteranForced;
	protected $logging;
	protected $offset;
	protected $images;
	protected $fontSize;
	protected $passwordReset;
	protected $points;
	protected $useAJAX;
	protected $mailBanned;
	protected $HOF;
	protected $individualScores;
	protected $score;
	protected $cssLink;
	protected $defaultCSSEnabled;
	protected $messageNotifications;
	protected $centerGalaxyMapOnPlayer;
	protected $oldAccountIDs = array();
	protected $maxRankAchieved;
	protected $referrerID;
	protected $credits; // SMR credits
	protected $rewardCredits; // SMR reward credits
	protected $dateShort;
	protected $timeShort;
	protected $template;
	protected $colourScheme;
	protected $hotkeys = array();
	protected $permissions;
	protected $friendlyColour;
	protected $neutralColour;
	protected $enemyColour;
	protected $SQL;

	protected $npc;

	protected $hasChanged;

	public static function getDefaultHotkeys() {
		return self::DEFAULT_HOTKEYS;
	}

	public static function getAccount($accountID, $forceUpdate = false) {
		if ($forceUpdate || !isset(self::$CACHE_ACCOUNTS[$accountID])) {
			self::$CACHE_ACCOUNTS[$accountID] = new SmrAccount($accountID);
		}
		return self::$CACHE_ACCOUNTS[$accountID];
	}

	public static function getAccountByName($login, $forceUpdate = false) {
		if (empty($login)) { return null; }
		$db = new SmrMySqlDatabase();
		$db->query('SELECT account_id FROM account WHERE login = ' . $db->escapeString($login) . ' LIMIT 1');
		if ($db->nextRecord()) {
			return self::getAccount($db->getInt('account_id'), $forceUpdate);
		}
		$return = null;
		return $return;
	}

	public static function getAccountByEmail($email, $forceUpdate = false) {
		if (empty($email)) { return null; }
		$db = new SmrMySqlDatabase();
		$db->query('SELECT account_id FROM account WHERE email = ' . $db->escapeString($email) . ' LIMIT 1');
		if ($db->nextRecord()) {
			return self::getAccount($db->getInt('account_id'), $forceUpdate);
		} else {
			return null;
		}
	}

	public static function getAccountByDiscordId($id, $forceUpdate = false) {
		if (empty($id)) { return null; }
		$db = new SmrMySqlDatabase();
		$db->query('SELECT account_id FROM account where discord_id = ' . $db->escapeString($id) . ' LIMIT 1');
		if ($db->nextRecord()) {
			return self::getAccount($db->getInt('account_id'), $forceUpdate);
		} else {
			return null;
		}
	}

	public static function getAccountByIrcNick($nick, $forceUpdate = false) {
		if (empty($nick)) { return null; }
		$db = new SmrMySqlDatabase();
		$db->query('SELECT account_id FROM account WHERE irc_nick = ' . $db->escapeString($nick) . ' LIMIT 1');
		if ($db->nextRecord()) {
			return self::getAccount($db->getInt('account_id'), $forceUpdate);
		} else {
			return null;
		}
	}

	public static function getAccountBySocialLogin(SocialLogin $social, $forceUpdate = false) {
		if (!$social->isValid()) { return null; }
		$db = new SmrMySqlDatabase();
		$db->query('SELECT account_id FROM account JOIN account_auth USING(account_id)
		            WHERE login_type = '.$db->escapeString($social->getLoginType()) . '
		              AND auth_key = '.$db->escapeString($social->getUserID()) . ' LIMIT 1');
		if ($db->nextRecord()) {
			return self::getAccount($db->getInt('account_id'), $forceUpdate);
		} else {
			return null;
		}
	}

	public static function createAccount($login, $password, $email, $timez, $referral) {
		if ($referral != 0) {
			// Will throw if referral account doesn't exist
			SmrAccount::getAccount($referral);
		}
		$db = new SmrMySqlDatabase();
		$passwordHash = password_hash($password, PASSWORD_DEFAULT);
		$db->query('INSERT INTO account (login, password, email, validation_code, last_login, offset,referral_id,hof_name) VALUES(' .
			$db->escapeString($login) . ', ' . $db->escapeString($passwordHash) . ', ' . $db->escapeString($email) . ', ' .
			$db->escapeString(random_string(10)) . ',' . $db->escapeNumber(TIME) . ',' . $db->escapeNumber($timez) . ',' . $db->escapeNumber($referral) . ',' . $db->escapeString($login) . ')');
		return self::getAccountByName($login);
	}

	public static function getUserScoreCaseStatement($db) {
		$userRankingTypes = array();
		$case = 'FLOOR(SUM(CASE type ';
		foreach (self::USER_RANKINGS_SCORE as $userRankingScore) {
			$userRankingType = $db->escapeArray($userRankingScore[0], false, false, ':', false);
			$userRankingTypes[] = $userRankingType;
			$case .= ' WHEN ' . $db->escapeString($userRankingType) . ' THEN POW(amount*' . $userRankingScore[1] . ',' . SmrAccount::USER_RANKINGS_EACH_STAT_POW . ')*' . $userRankingScore[2];
		}
		$case .= ' END))';
		return array('CASE'=>$case, 'IN'=>$db->escapeArray($userRankingTypes));
	}

	protected function __construct($accountID) {
		$this->db = new SmrMySqlDatabase();
		$this->SQL = 'account_id = ' . $this->db->escapeNumber($accountID);
		$this->db->query('SELECT * FROM account WHERE ' . $this->SQL . ' LIMIT 1');

		if ($this->db->nextRecord()) {
			$row = $this->db->getRow();
			$this->account_id = $row['account_id'];

			$this->login			= $row['login'];
			$this->passwordHash = $row['password'];
			$this->email			= $row['email'];
			$this->validated = $this->db->getBoolean('validated');

			$this->last_login = $row['last_login'];
			$this->validation_code = $row['validation_code'];
			$this->veteranForced = $this->db->getBoolean('veteran');
			$this->logging = $this->db->getBoolean('logging');
			$this->offset			= $row['offset'];
			$this->images			= $row['images'];
			$this->fontSize = $row['fontsize'];

			$this->passwordReset = $row['password_reset'];
			$this->useAJAX = $this->db->getBoolean('use_ajax');
			$this->mailBanned = (int)$row['mail_banned'];
			
			$this->friendlyColour = $row['friendly_colour'];
			$this->neutralColour = $row['neutral_colour'];
			$this->enemyColour = $row['enemy_colour'];

			$this->cssLink = $row['css_link'];
			$this->defaultCSSEnabled = $this->db->getBoolean('default_css_enabled');
			$this->centerGalaxyMapOnPlayer = $this->db->getBoolean('center_galaxy_map_on_player');

			$this->messageNotifications = $this->db->getObject('message_notifications');
			$this->hotkeys = $this->db->getObject('hotkeys');
			foreach (self::DEFAULT_HOTKEYS as $hotkey => $binding) {
				if (!isset($this->hotkeys[$hotkey])) {
					$this->hotkeys[$hotkey] = $binding;
				}
			}

			foreach (Globals::getHistoryDatabases() as $databaseName => $oldColumn) {
				$this->oldAccountIDs[$databaseName] = $row[$oldColumn];
			}

			$this->referrerID = $row['referral_id'];
			$this->maxRankAchieved = $row['max_rank_achieved'];

			$this->hofName			= $row['hof_name'];
			$this->discordId		= $row['discord_id'];
			$this->ircNick			= $row['irc_nick'];

			$this->dateShort		= $row['date_short'];
			$this->timeShort		= $row['time_short'];

			$this->template			= $row['template'];
			$this->colourScheme = $row['colour_scheme'];

			if (empty($this->hofName)) {
				$this->hofName = $this->login;
			}
		} else {
			throw new AccountNotFoundException('Account ID ' . $accountID . ' does not exist!');
		}
	}

	public function isDisabled() {
		$this->db->query('SELECT * FROM account_is_closed JOIN closing_reason USING(reason_id) ' .
			'WHERE ' . $this->SQL . ' LIMIT 1');
		if ($this->db->nextRecord()) {
			// get the expire time
			$expireTime = $this->db->getInt('expires');

			// are we over this time?
			if ($expireTime > 0 && $expireTime < TIME) {
				// get rid of the expire entry
				$this->unbanAccount();
				return false;
			}
			return array('Time' => $expireTime,
				'Reason' => $this->db->getField('reason'),
				'ReasonID' => $this->db->getInt('reason_id')
			);
		} else {
			return false;
		}
	}

	public function update() {
		$this->db->query('UPDATE account SET email = ' . $this->db->escapeString($this->email) .
			', validation_code = ' . $this->db->escapeString($this->validation_code) .
			', validated = ' . $this->db->escapeBoolean($this->validated) .
			', password = ' . $this->db->escapeString($this->passwordHash) .
			', images = ' . $this->db->escapeString($this->images) .
			', password_reset = ' . $this->db->escapeString($this->passwordReset) .
			', use_ajax=' . $this->db->escapeBoolean($this->useAJAX) .
			', mail_banned=' . $this->db->escapeNumber($this->mailBanned) .
			', max_rank_achieved=' . $this->db->escapeNumber($this->maxRankAchieved) .
			', default_css_enabled=' . $this->db->escapeBoolean($this->defaultCSSEnabled) .
			', center_galaxy_map_on_player=' . $this->db->escapeBoolean($this->centerGalaxyMapOnPlayer) .
			', message_notifications=' . $this->db->escapeObject($this->messageNotifications) .
			', hotkeys=' . $this->db->escapeObject($this->hotkeys) .
			', last_login = ' . $this->db->escapeNumber($this->last_login) .
			', logging = ' . $this->db->escapeBoolean($this->logging) .
			', time_short = ' . $this->db->escapeString($this->timeShort) .
			', date_short = ' . $this->db->escapeString($this->dateShort) .
			', discord_id = ' . $this->db->escapeString($this->discordId, true, true) .
			', irc_nick = ' . $this->db->escapeString($this->ircNick, true, true) .
			', hof_name = ' . $this->db->escapeString($this->hofName) .
			', colour_scheme = ' . $this->db->escapeString($this->colourScheme) .
			', fontsize = ' . $this->db->escapeNumber($this->fontSize) .
			', css_link = ' . $this->db->escapeString($this->cssLink, true, true) .
			', friendly_colour = ' . $this->db->escapeString($this->friendlyColour, true, true) .
			', neutral_colour = ' . $this->db->escapeString($this->neutralColour, true, true) .
			', enemy_colour = ' . $this->db->escapeString($this->enemyColour, true, true) .
			' WHERE ' . $this->SQL . ' LIMIT 1');
		$this->hasChanged = false;
	}

	public function updateIP() {
		$curr_ip = getIpAddress();
		$this->log(LOG_TYPE_LOGIN, 'logged in from ' . $curr_ip);

		// more than 50 elements in it?

		$this->db->query('SELECT time,ip FROM account_has_ip WHERE ' . $this->SQL . ' ORDER BY time ASC');
		if ($this->db->getNumRows() > 50 && $this->db->nextRecord()) {
			$delete_time = $this->db->getInt('time');
			$delete_ip = $this->db->getField('ip');

			$this->db->query('DELETE FROM account_has_ip
				WHERE '.$this->SQL . ' AND
				time = '.$this->db->escapeNumber($delete_time) . ' AND
				ip = '.$this->db->escapeString($delete_ip));
		}
		list($fi, $se, $th, $fo) = preg_split('/[.\s,]/', $curr_ip, 4);
		if ($curr_ip != 'unknown' && $curr_ip != 'unknown...' && $curr_ip != 'unknown, unknown') {
			$curr_ip = $fi . '.' . $se . '.' . $th . '.' . $fo;
			$host = gethostbyaddr($curr_ip);
		} else {
			$host = 'unknown';
		}

		// save...first make sure there isn't one for these keys (someone could double click and get error)
		$this->db->query('REPLACE INTO account_has_ip (account_id, time, ip, host) VALUES (' . $this->db->escapeNumber($this->account_id) . ', ' . $this->db->escapeNumber(TIME) . ', ' . $this->db->escapeString($curr_ip) . ', ' . $this->db->escapeString($host) . ')');
	}

	public function updateLastLogin() {
		if ($this->last_login == TIME) {
			return;
		}
		$this->last_login = TIME;
		$this->hasChanged = true;
		$this->update();
	}

	public function getLastLogin() {
		return $this->last_login;
	}

	public function setLoggingEnabled($bool) {
		if ($this->logging == $bool) {
			return;
		}
		$this->logging = $bool;
		$this->hasChanged = true;
		$this->update();
	}

	public function isLoggingEnabled() {
		return $this->logging;
	}

	public function isVeteranForced() {
		return $this->veteranForced;
	}

	public function isVeteran() {
		// Use maxRankAchieved to avoid a database call to get user stats.
		// This saves a lot of time on the CPL, Rankings, Rosters, etc.
		return $this->isVeteranForced() || $this->maxRankAchieved >= FLEDGLING;
	}

	public function isNPC() {
		if(!isset($this->npc)) {
			$this->db->query('SELECT login FROM npc_logins WHERE login = '.$this->db->escapeString($this->getLogin()).' LIMIT 1;');
			$this->npc = $this->db->nextRecord();
		}
		return $this->npc;
	}

	protected function getHOFData() {
		if(!isset($this->HOF)) {
			//Get Player HOF
			$this->db->query('SELECT type,sum(amount) as amount FROM player_hof WHERE ' . $this->SQL . ' AND game_id IN (SELECT game_id FROM game WHERE ignore_stats = \'FALSE\') GROUP BY type');
			$this->HOF = array();
			while($this->db->nextRecord()) {
				$hof =& $this->HOF;
				$typeList = explode(':',$this->db->getField('type'));
				foreach($typeList as $type) {
					if(!isset($hof[$type])) {
						$hof[$type] = array();
					}
					$hof =& $hof[$type];
				}
				$hof = $this->db->getFloat('amount');
			}
		}
	}

	public function getHOF(array $typeList = null) {
		$this->getHOFData();
		if ($typeList == null) {
			return $this->HOF;
		}
		$hof = $this->HOF;
		foreach ($typeList as $type) {
			if (!isset($hof[$type])) {
				return 0;
			}
			$hof = $hof[$type];
		}
		return $hof;
	}

	public function getRankName() {
		$rankings = Globals::getUserRanking();
		if (isset($rankings[$this->getRank()])) {
			return $rankings[$this->getRank()];
		} else {
			return end($rankings);
		}
	}

	public function getScore() {
		if (!isset($this->score)) {
			$score = 0;
			foreach ($this->getIndividualScores() as $each) {
				$score += $each['Score'];
			}
			$this->score = round($score);
		}
		return $this->score;
	}

	public function getIndividualScores(SmrPlayer $player = null) {
		$gameID = 0;
		if ($player != null) {
			$gameID = $player->getGameID();
		}
		if (!isset($this->individualScores[$gameID])) {
			$this->individualScores[$gameID] = array();
			foreach (self::USER_RANKINGS_SCORE as $statScore) {
				if ($player == null) {
					$stat = $this->getHOF($statScore[0]);
				} else {
					$stat = $player->getHOF($statScore[0]);
				}
				$this->individualScores[$gameID][] = array('Stat'=>$statScore[0], 'Score'=>pow($stat * $statScore[1], self::USER_RANKINGS_EACH_STAT_POW) * $statScore[2]);
			}
		}
		return $this->individualScores[$gameID];
	}

	public function getRank() : int {
		$rank = ICeil(pow($this->getScore(), self::USER_RANKINGS_TOTAL_SCORE_POW) / self::USER_RANKINGS_RANK_BOUNDARY);
		if ($rank < 1) {
			$rank = 1;
		}
		if ($rank > $this->maxRankAchieved) {
			$this->updateMaxRankAchieved($rank);
		}
		return $rank;
	}

	protected function updateMaxRankAchieved($rank) {
		if ($rank <= $this->maxRankAchieved) {
			throw new Exception('Trying to set max rank achieved to a lower value: ' . $rank);
		}
		$delta = $rank - $this->maxRankAchieved;
		if ($this->hasReferrer()) {
			$this->getReferrer()->increaseSmrRewardCredits($delta * CREDITS_PER_DOLLAR);
		}
		$this->maxRankAchieved += $delta;
		$this->hasChanged = true;
		$this->update();
	}

	public function getReferrerID() {
		return $this->referrerID;
	}

	public function hasReferrer() {
		return $this->referrerID > 0;
	}

	public function getReferrer() {
		return SmrAccount::getAccount($this->getReferrerID());
	}

	public function log($log_type_id, $msg, $sector_id = 0) {
		if ($this->isLoggingEnabled()) {
			$this->db->query('INSERT INTO account_has_logs ' .
				'(account_id, microtime, log_type_id, message, sector_id) ' .
				'VALUES(' . $this->db->escapeNumber($this->account_id) . ', ' . $this->db->escapeMicrotime(MICRO_TIME) . ', ' . $this->db->escapeNumber($log_type_id) . ', ' . $this->db->escapeString($msg) . ', ' . $this->db->escapeNumber($sector_id) . ')');
		}
	}

	protected function getSmrCreditsData() {
		if (!isset($this->credits) || !isset($this->rewardCredits)) {
			$this->credits = 0;
			$this->rewardCredits = 0;
			$this->db->query('SELECT * FROM account_has_credits WHERE ' . $this->SQL . ' LIMIT 1');
			if ($this->db->nextRecord()) {
				$this->credits = $this->db->getInt('credits_left');
				$this->rewardCredits = $this->db->getInt('reward_credits');
			}
		}
	}

	public function getTotalSmrCredits() {
		return $this->getSmrCredits() + $this->getSmrRewardCredits();
	}

	public function decreaseTotalSmrCredits($totalCredits) {
		if ($totalCredits == 0) {
			return;
		}
		if ($totalCredits < 0) {
			throw new Exception('You cannot use negative total credits');
		}
		if ($totalCredits > $this->getTotalSmrCredits()) {
			throw new Exception('You do not have that many credits in total to use');
		}

		$rewardCredits = $this->rewardCredits;
		$credits = $this->credits;
		$rewardCredits -= $totalCredits;
		if ($rewardCredits < 0) {
			$credits += $rewardCredits;
			$rewardCredits = 0;
		}
		if ($this->credits == 0 && $this->rewardCredits == 0) {
			$this->db->query('REPLACE INTO account_has_credits (account_id, credits_left, reward_credits) VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($credits) . ',' . $this->db->escapeNumber($rewardCredits) . ')');
		} else {
			$this->db->query('UPDATE account_has_credits SET credits_left=' . $this->db->escapeNumber($credits) . ', reward_credits=' . $this->db->escapeNumber($rewardCredits) . ' WHERE ' . $this->SQL . ' LIMIT 1');
		}
		$this->credits = $credits;
		$this->rewardCredits = $rewardCredits;
	}

	public function getSmrCredits() {
		$this->getSmrCreditsData();
		return $this->credits;
	}

	public function getSmrRewardCredits() {
		$this->getSmrCreditsData();
		return $this->rewardCredits;
	}

	public function setSmrCredits($credits) {
		if ($this->getSmrCredits() == $credits) {
			return;
		}
		if ($this->credits == 0 && $this->rewardCredits == 0) {
			$this->db->query('REPLACE INTO account_has_credits (account_id, credits_left) VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($credits) . ')');
		} else {
			$this->db->query('UPDATE account_has_credits SET credits_left=' . $this->db->escapeNumber($credits) . ' WHERE ' . $this->SQL . ' LIMIT 1');
		}
		$this->credits = $credits;
	}

	public function increaseSmrCredits($credits) {
		if ($credits == 0) {
			return;
		}
		if ($credits < 0) {
			throw new Exception('You cannot gain negative credits');
		}
		$this->setSmrCredits($this->getSmrCredits() + $credits);
	}

	public function decreaseSmrCredits($credits) {
		if ($credits == 0) {
			return;
		}
		if ($credits < 0) {
			throw new Exception('You cannot use negative credits');
		}
		if ($credits > $this->getSmrCredits()) {
			throw new Exception('You cannot use more credits than you have');
		}
		$this->setSmrCredits($this->getSmrCredits() - $credits);
	}

	public function setSmrRewardCredits($credits) {
		if ($this->getSmrRewardCredits() == $credits) {
			return;
		}
		if ($this->credits == 0 && $this->rewardCredits == 0) {
			$this->db->query('REPLACE INTO account_has_credits (account_id, reward_credits) VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($credits) . ')');
		} else {
			$this->db->query('UPDATE account_has_credits SET reward_credits=' . $this->db->escapeNumber($credits) . ' WHERE ' . $this->SQL . ' LIMIT 1');
		}
		$this->rewardCredits = $credits;
	}

	public function increaseSmrRewardCredits($credits) {
		if ($credits == 0) {
			return;
		}
		if ($credits < 0) {
			throw new Exception('You cannot gain negative reward credits');
		}
		$this->setSmrRewardCredits($this->getSmrRewardCredits() + $credits);
	}

	public function sendMessageToBox($boxTypeID, $message) {
		// send him the message
		self::doMessageSendingToBox($this->getAccountID(), $boxTypeID, $message);
	}

	public static function doMessageSendingToBox($senderID, $boxTypeID, $message, $gameID = 0) {
		$db = new SmrMySqlDatabase();
		// send him the message
		$db->query('INSERT INTO message_boxes
			(box_type_id,game_id,message_text,
			sender_id,send_time) VALUES (' .
			$db->escapeNumber($boxTypeID) . ',' .
			$db->escapeNumber($gameID) . ',' .
			$db->escapeString($message) . ',' .
			$db->escapeNumber($senderID) . ',' .
			$db->escapeNumber(TIME) . ')'
		);
	}

	public function getAccountID() {
		return $this->account_id;
	}

	public function getOldAccountIDs() {
		return $this->oldAccountIDs;
	}

	public function getOldAccountID($dbName) {
		return $this->oldAccountIDs[$dbName] ?? 0;
	}

	public function hasOldAccountID($dbName = false) {
		if ($dbName === false) {
			return count($this->getOldAccountIDs()) != 0;
		}
		return $this->getOldAccountID($dbName) != 0;
	}

	public function getLogin() {
		return $this->login;
	}

	public function getEmail() {
		return $this->email;
	}

	public function setEmail($email) {
		if ($this->email == $email) {
			return;
		}
		$this->email = $email;
		$this->hasChanged = true;
		$this->update();
	}

	/**
	 * Change e-mail address, unvalidate the account, and resend validation code
	 */
	public function changeEmail($email) {
		// get user and host for the provided address
		list($user, $host) = explode('@', $email);

		// check if the host got a MX or at least an A entry
		if (!checkdnsrr($host, 'MX') && !checkdnsrr($host, 'A')) {
			create_error('This is not a valid email address! The domain ' . $host . ' does not exist.');
		}

		if (strstr($email, ' ')) {
			create_error('The email is invalid! It cannot contain any spaces.');
		}

		$this->db->query('SELECT 1 FROM account WHERE email = ' . $this->db->escapeString($email) . ' and account_id != ' . $this->db->escapeNumber($this->getAccountID()) . ' LIMIT 1');
		if ($this->db->getNumRows() > 0) {
			create_error('This email address is already registered.');
		}

		$this->setEmail($email);
		$this->setValidationCode(random_string(10));
		$this->setValidated(false);

		// remember when we sent validation code
		$this->db->query('REPLACE INTO notification (notification_type, account_id, time)
				VALUES(\'validation_code\', '.$this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber(TIME) . ')');

		$emailMessage =
			'You changed your email address registered with SMR and need to revalidate now!' . EOL . EOL .
			'   Your new validation code is: ' . $this->getValidationCode() . EOL . EOL .
			'The Space Merchant Realms server is on the web at ' . URL;

		$mail = setupMailer();
		$mail->Subject = 'Your validation code!';
		$mail->setFrom('support@smrealms.de', 'SMR Support');
		$mail->msgHTML(nl2br($emailMessage));
		$mail->addAddress($this->getEmail(), $this->getHofName());
		$mail->send();

		// Remove an "Invalid email" ban (may or may not have one)
		if ($disabled = $this->isDisabled()) {
			if ($disabled['Reason'] == CLOSE_ACCOUNT_INVALID_EMAIL_REASON) {
				$this->unbanAccount($this);
			}
		}
	}

	public function getOffset() {
		return $this->offset;
	}

	public function getFontSize() {
		return $this->fontSize;
	}

	public function setFontSize($size) {
		if ($this->fontSize == $size) {
			return;
		}
		$this->fontSize = $size;
		$this->hasChanged = true;
		$this->update();
	}

	// gets the extra CSS file linked in preferences
	public function getCssLink() {
		return $this->cssLink;
	}

	// sets the extra CSS file linked in preferences
	public function setCssLink($link) {
		if ($this->cssLink == $link) {
			return;
		}
		$this->cssLink = $link;
		$this->hasChanged = true;
		$this->update();
	}

	public function getTemplate() {
		return $this->template;
	}

	public function setTemplate($template) {
		if ($this->template == $template) {
			return;
		}
		if (!in_array($template, array_keys(Globals::getAvailableTemplates()))) {
			throw new Exception('Template not allowed: ' . $template);
		}
		$this->db->query('UPDATE account SET template = ' . $this->db->escapeString($template) . ' WHERE ' . $this->SQL . ' LIMIT 1');
		$this->template = $template;
		$colourSchemes = Globals::getAvailableColourSchemes($template);
		$this->setColourScheme($colourSchemes[0]);
	}

	public function getColourScheme() {
		return $this->colourScheme;
	}

	public function setColourScheme($colourScheme) {
		if ($this->colourScheme == $colourScheme) {
			return;
		}
		if (!in_array($colourScheme, array_keys(Globals::getAvailableColourSchemes($this->getTemplate())))) {
			throw new Exception('Colour scheme not allowed: ' . $colourScheme);
		}
		$this->colourScheme = $colourScheme;
		$this->hasChanged = true;
		$this->update();
	}

	// gets the CSS URL based on the template name specified in preferences
	public function getCssUrl() {
		return CSS_URLS[$this->getTemplate()];
	}

	// gets the CSS_COLOUR URL based on the template and color scheme specified in preferences
	public function getCssColourUrl() {
		return CSS_COLOUR_URLS[$this->getTemplate()][$this->getColourScheme()];
	}

	/**
	 * The Hall Of Fame name is not html-escaped in the database, so to display
	 * it correctly we must escape html entities.
	 */
	public function getHofDisplayName($linked = false) {
		$hofDisplayName = htmlspecialchars($this->getHofName());
		if ($linked) {
			return '<a href="' . $this->getPersonalHofHREF() . '">' . $hofDisplayName . '</a>';
		} else {
			return $hofDisplayName;
		}
	}

	public function getHofName() {
		return $this->hofName;
	}

	public function setHofName($name) {
		if ($this->hofName == $name) {
			return;
		}
		$this->hofName = $name;
		$this->hasChanged = true;
		$this->update();
	}

	public function getIrcNick() {
		return $this->ircNick;
	}

	public function setIrcNick($nick) {
		if ($this->ircNick == $nick) {
			return;
		}
		$this->ircNick = $nick;
		$this->hasChanged = true;
		$this->update();
	}

	public function getDiscordId() {
		return $this->discordId;
	}

	public function setDiscordId($id) {
		if ($this->discordId == $id) {
			return;
		}
		$this->discordId = $id;
		$this->hasChanged = true;
		$this->update();
	}

	public function getReferralLink() {
		return URL . '/login_create.php?ref=' . $this->getAccountID();
	}

	public function getShortDateFormat() {
		return $this->dateShort;
	}

	public function setShortDateFormat($format) {
		if ($this->dateShort == $format) {
			return;
		}
		$this->dateShort = $format;
		$this->hasChanged = true;
		$this->update();
	}

	public function getShortTimeFormat() {
		return $this->timeShort;
	}

	public function setShortTimeFormat($format) {
		if ($this->timeShort == $format) {
			return;
		}
		$this->timeShort = $format;
		$this->hasChanged = true;
		$this->update();
	}

	public function getValidationCode() {
		return $this->validation_code;
	}

	protected function setValidationCode($code) {
		if ($this->validation_code == $code) {
			return;
		}
		$this->validation_code = $code;
		$this->hasChanged = true;
		$this->update();
	}

	public function setValidated($bool) {
		if ($this->validated == $bool) {
			return;
		}
		$this->validated = $bool;
		$this->hasChanged = true;
		$this->update();
	}

	public function isValidated() {
		return $this->validated;
	}

	public function isLoggedIn() {
		$this->db->query('SELECT 1 FROM active_session WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . ' LIMIT 1');
		return $this->db->nextRecord();
	}

	/**
	 * Check if the given (plain-text) password is correct.
	 * Updates the password hash if necessary.
	 */
	public function checkPassword($password) {
		// New (safe) password hashes will start with a $, but accounts logging
		// in for the first time since the transition from md5 will still have
		// hex-only hashes.
		if (strpos($this->passwordHash, '$') === 0) {
			$result = password_verify($password, $this->passwordHash);
		} else {
			$result = $this->passwordHash === md5($password);
		}

		// If password is correct, but hash algorithm has changed, update the hash.
		// This will also update any obsolete md5 password hashes.
		if ($result && password_needs_rehash($this->passwordHash, PASSWORD_DEFAULT)) {
			$this->setPassword($password);
		}

		return $result;
	}

	/**
	 * Set the (plain-text) password for this account.
	 */
	public function setPassword($password) {
		$hash = password_hash($password, PASSWORD_DEFAULT);
		if ($this->passwordHash === $hash) {
			return;
		}
		$this->passwordHash = $hash;
		$this->generatePasswordReset();
		$this->hasChanged = true;
		$this->update();
	}

	public function addAuthMethod($loginType, $authKey) {
		$this->db->query('SELECT account_id FROM account_auth WHERE login_type=' . $this->db->escapeString($loginType) . ' AND auth_key = ' . $this->db->escapeString($authKey) . ';');
		if ($this->db->nextRecord()) {
			if ($this->db->getInt('account_id') != $this->getAccountID()) {
				throw new Exception('Another account already uses this form of auth.');
			}
			return true;
		}
		$this->db->query('INSERT INTO account_auth values (' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeString($loginType) . ',' . $this->db->escapeString($authKey) . ');');
		return true;
	}

	public function generatePasswordReset() {
		$this->setPasswordReset(random_string(32));
	}

	public function getPasswordReset() {
		return $this->passwordReset;
	}

	protected function setPasswordReset($passwordReset) {
		if ($this->passwordReset == $passwordReset) {
			return;
		}
		$this->passwordReset = $passwordReset;
		$this->hasChanged = true;
		$this->update();
	}

	public function isDisplayShipImages() {
		return $this->images == 'Yes';
	}

	public function setDisplayShipImages($yesNo) {
		if ($this->images == $yesNo) {
			return;
		}
		$this->images = $yesNo;
		$this->hasChanged = true;
		$this->update();
	}

	public function isUseAJAX() {
		return $this->useAJAX;
	}

	public function setUseAJAX($bool) {
		if ($this->useAJAX == $bool) {
			return;
		}
		$this->useAJAX = $bool;
		$this->hasChanged = true;
		$this->update();
	}

	public function isDefaultCSSEnabled() {
		return $this->defaultCSSEnabled;
	}

	public function setDefaultCSSEnabled($bool) {
		if ($this->defaultCSSEnabled == $bool) {
			return;
		}
		$this->defaultCSSEnabled = $bool;
		$this->hasChanged = true;
		$this->update();
	}

	public function getHotkeys($hotkeyType = false) {
		if ($hotkeyType !== false) {
			if (isset($this->hotkeys[$hotkeyType])) {
				return $this->hotkeys[$hotkeyType];
			} else {
				return array();
			}
		}
		return $this->hotkeys;
	}

	public function setHotkey($hotkeyType, $binding) {
		if ($this->getHotkeys($hotkeyType) == $binding) {
			return;
		}
		$this->hotkeys[$hotkeyType] = $binding;
		$this->hasChanged = true;
		$this->update();
	}

	public function isReceivingMessageNotifications($messageTypeID) {
		return $this->getMessageNotifications($messageTypeID) > 0;
	}

	public function getMessageNotifications($messageTypeID) {
		return $this->messageNotifications[$messageTypeID] ?? 0;
	}

	public function setMessageNotifications($messageTypeID, $num) {
		if ($this->getMessageNotifications($messageTypeID) == $num) {
			return;
		}
		$this->messageNotifications[$messageTypeID] = $num;
		$this->hasChanged = true;
		$this->update();
	}

	public function increaseMessageNotifications($messageTypeID, $num) {
		if ($num == 0) {
			return;
		}
		if ($num < 0) {
			throw new Exception('You cannot increase by a negative amount');
		}
		$this->setMessageNotifications($messageTypeID, $this->getMessageNotifications($messageTypeID) + $num);
	}

	public function decreaseMessageNotifications($messageTypeID, $num) {
		if ($num == 0) {
			return;
		}
		if ($num < 0) {
			throw new Exception('You cannot decrease by a negative amount');
		}
		$this->setMessageNotifications($messageTypeID, $this->getMessageNotifications($messageTypeID) - $num);
	}

	public function isCenterGalaxyMapOnPlayer() {
		return $this->centerGalaxyMapOnPlayer;
	}

	public function setCenterGalaxyMapOnPlayer($bool) {
		if ($this->centerGalaxyMapOnPlayer == $bool) {
			return;
		}
		$this->centerGalaxyMapOnPlayer = $bool;
		$this->hasChanged = true;
		$this->update();
	}

	public function getMailBanned() {
		return $this->mailBanned;
	}

	public function isMailBanned() {
		return $this->mailBanned > TIME;
	}

	public function setMailBanned($time) {
		if ($this->mailBanned == $time) {
			return;
		}
		$this->mailBanned = $time;
		$this->hasChanged = true;
	}

	public function increaseMailBanned($increaseTime) {
		$time = max(TIME, $this->getMailBanned());
		$this->setMailBanned($time + $increaseTime);
	}
	
	public function getPermissions() {
		if (!isset($this->permissions)) {
			$this->permissions = array();
			$this->db->query('SELECT permission_id FROM account_has_permission WHERE ' . $this->SQL);
			while ($this->db->nextRecord()) {
				$this->permissions[$this->db->getInt('permission_id')] = true;
			}
		}
		return $this->permissions;
	}

	public function hasPermission($permissionID = false) {
		$permissions = $this->getPermissions();
		if ($permissionID === false) {
			return count($permissions) > 0;
		}
		return $permissions[$permissionID] ?? false;
	}

	public function getPoints() {
		if (!isset($this->points)) {
			$this->points = 0;
			$this->db->lockTable('account_has_points');
			$this->db->query('SELECT * FROM account_has_points WHERE ' . $this->SQL . ' LIMIT 1');
			if ($this->db->nextRecord()) {
				$this->points = $this->db->getInt('points');
				$lastUpdate = $this->db->getInt('last_update');
				//we are gonna check for reducing points...
				if ($this->points > 0 && $lastUpdate < TIME - (7 * 86400)) {
					$removePoints = 0;
					while ($lastUpdate < TIME - (7 * 86400)) {
						$removePoints++;
						$lastUpdate += (7 * 86400);
					}
					$this->removePoints($removePoints, $lastUpdate);
				}
			}
			$this->db->unlock();
		}
		return $this->points;
	}

	public function setPoints($numPoints, $lastUpdate = false) {
		$numPoints = max($numPoints, 0);
		if ($this->getPoints() == $numPoints) {
			return;
		}
		if ($this->points == 0) {
			$this->db->query('INSERT INTO account_has_points (account_id, points, last_update) VALUES (' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($numPoints) . ', ' . $this->db->escapeNumber($lastUpdate ? $lastUpdate : TIME) . ')');
		} elseif ($numPoints <= 0) {
			$this->db->query('DELETE FROM account_has_points WHERE ' . $this->SQL . ' LIMIT 1');
		} else {
			$this->db->query('UPDATE account_has_points SET points = ' . $this->db->escapeNumber($numPoints) . ($lastUpdate ? ', last_update = ' . $this->db->escapeNumber(TIME) : '') . ' WHERE ' . $this->SQL . ' LIMIT 1');
		}
		$this->points = $numPoints;
	}

	public function removePoints($numPoints, $lastUpdate = false) {
		if ($numPoints > 0) {
			$this->setPoints($this->getPoints() - $numPoints, $lastUpdate);
		}
	}

	public function addPoints($numPoints, SmrAccount $admin, $reasonID, $suspicion) {
		//do we have points
		$this->setPoints($this->getPoints() + $numPoints, TIME);
		$totalPoints = $this->getPoints();
		if ($totalPoints < 10) {
			return false; //leave scripts its only a warning
		} elseif ($totalPoints < 20) {
			$days = 2;
		} elseif ($totalPoints < 30) {
			$days = 4;
		} elseif ($totalPoints < 50) {
			$days = 7;
		} elseif ($totalPoints < 75) {
			$days = 15;
		} elseif ($totalPoints < 100) {
			$days = 30;
		} elseif ($totalPoints < 125) {
			$days = 60;
		} elseif ($totalPoints < 150) {
			$days = 120;
		} elseif ($totalPoints < 175) {
			$days = 240;
		} elseif ($totalPoints < 200) {
			$days = 480;
		} else {
			$days = 0; //Forever/indefinite
		}

		if ($days == 0) {
			$expireTime = 0;
		} else {
			$expireTime = TIME + $days * 86400;
		}
		$this->banAccount($expireTime, $admin, $reasonID, $suspicion);

		return $days;
	}
	
	public function getFriendlyColour() {
		return $this->friendlyColour;
	}
	public function setFriendlyColour($colour) {
		$this->friendlyColour = $colour;
		$this->hasChanged = true;
	}
	public function getNeutralColour() {
		return $this->neutralColour;
	}
	public function setNeutralColour($colour) {
		$this->neutralColour = $colour;
		$this->hasChanged = true;
	}
	public function getEnemyColour() {
		return $this->enemyColour;
	}
	public function setEnemyColour($colour) {
		$this->enemyColour = $colour;
		$this->hasChanged = true;
	}

	public function banAccount($expireTime, SmrAccount $admin, $reasonID, $suspicion, $removeExceptions = false) {
		$this->db->query('REPLACE INTO account_is_closed
					(account_id, reason_id, suspicion, expires)
					VALUES('.$this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($reasonID) . ', ' . $this->db->escapeString($suspicion) . ', ' . $this->db->escapeNumber($expireTime) . ')');
		$this->db->lockTable('active_session');
		$this->db->query('DELETE FROM active_session WHERE ' . $this->SQL . ' LIMIT 1');
		$this->db->unlock();

		$this->db->query('INSERT INTO account_has_closing_history
						(account_id, time, admin_id, action)
						VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber(TIME) . ', ' . $this->db->escapeNumber($admin->getAccountID()) . ', ' . $this->db->escapeString('Closed') . ');');
		$this->db->query('UPDATE player SET newbie_turns = 1
						WHERE ' . $this->SQL . '
						AND newbie_turns = 0
						AND land_on_planet = ' . $this->db->escapeBoolean(false));

		$this->db->query('SELECT game_id FROM game JOIN player USING (game_id)
						WHERE ' . $this->SQL . '
						AND end_time >= ' . $this->db->escapeNumber(TIME));
		while ($this->db->nextRecord()) {
			$player = SmrPlayer::getPlayer($this->getAccountID(), $this->db->getInt('game_id'));
			$player->updateTurns();
			$player->update();
		}
		$this->log(LOG_TYPE_ACCOUNT_CHANGES, 'Account closed by ' . $admin->getLogin() . '.');
		if ($removeExceptions !== false) {
			$this->db->query('DELETE FROM account_exceptions WHERE ' . $this->SQL);
		}
	}

	public function unbanAccount(SmrAccount $admin = null, $currException = false) {
		$adminID = 0;
		if ($admin !== null) {
			$adminID = $admin->getAccountID();
		}
		$this->db->query('DELETE FROM account_is_closed WHERE ' . $this->SQL . ' LIMIT 1');
		$this->db->query('INSERT INTO account_has_closing_history
						(account_id, time, admin_id, action)
						VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber(TIME) . ', ' . $this->db->escapeNumber($adminID) . ', ' . $this->db->escapeString('Opened') . ')');
		$this->db->query('UPDATE player SET last_turn_update = GREATEST(' . $this->db->escapeNumber(TIME) . ', last_turn_update) WHERE ' . $this->SQL);
		if ($admin !== null) {
			$this->log(LOG_TYPE_ACCOUNT_CHANGES, 'Account reopened by ' . $admin->getLogin() . '.');
		} else {
			$this->log(LOG_TYPE_ACCOUNT_CHANGES, 'Account automatically reopened.');
		}
		if ($currException !== false) {
			$this->db->query('REPLACE INTO account_exceptions (account_id, reason)
							VALUES (' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeString($currException) . ')');
		}
	}

	public function getToggleAJAXHREF() {
		global $var;
		return SmrSession::getNewHREF(create_container('skeleton.php', 'toggle_processing.php', array('toggle'=>'AJAX', 'referrer'=>$var['body'])));
	}

	public function getUserRankingHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_view.php'));
	}

	public function getPersonalHofHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'hall_of_fame_player_detail.php', array('account_id' => $this->getAccountID())));
	}
}
