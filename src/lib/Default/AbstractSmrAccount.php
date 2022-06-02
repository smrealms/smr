<?php declare(strict_types=1);

abstract class AbstractSmrAccount {

	protected const USER_RANKINGS_EACH_STAT_POW = .9;
	protected const USER_RANKINGS_SCORE = [
		// [Stat, a, b]
		// Used as: pow(Stat * a, USER_RANKINGS_EACH_STAT_POW) * b
		[['Trade', 'Experience', 'Total'], .1, 0.5],
		[['Trade', 'Money', 'Profit'], 0.00005, 0.5],
		[['Killing', 'Kills'], 1000, 1],
	];

	protected static array $CACHE_ACCOUNTS = [];
	protected const DEFAULT_HOTKEYS = [
		'MoveUp' => ['w', 'up'],
		'ScanUp' => ['shift+w', 'shift+up'],
		'MoveLeft' => ['a', 'left'],
		'ScanLeft' => ['shift+a', 'shift+left'],
		'MoveRight' => ['d', 'right'],
		'ScanRight' => ['shift+d', 'shift+right'],
		'MoveDown' => ['s', 'down'],
		'ScanDown' => ['shift+s', 'shift+down'],
		'MoveWarp' => ['e', '0'],
		'ScanWarp' => ['shift+e', 'shift+0'],
		'ScanCurrent' => ['shift+1'],
		'CurrentSector' => ['1'],
		'LocalMap' => ['2'],
		'PlotCourse' => ['3'],
		'CurrentPlayers' => ['4'],
		'EnterPort' => ['q'],
		'AttackTrader' => ['f'],
	];

	protected Smr\Database $db;
	protected readonly string $SQL;

	protected string $login;
	protected string $passwordHash;
	protected string $email;
	protected bool $validated;
	protected string $validation_code;
	protected int $last_login;
	protected string $hofName;
	protected ?string $discordId;
	protected ?string $ircNick;
	protected bool $veteranForced;
	protected bool $logging;
	protected int $offset;
	protected bool $images;
	protected int $fontSize;
	protected string $passwordReset;
	protected int $points;
	protected bool $useAJAX;
	protected int $mailBanned;
	/** @var array<string, float> */
	protected array $HOF;
	protected array $individualScores;
	protected int $score;
	protected ?string $cssLink;
	protected bool $defaultCSSEnabled;
	protected ?array $messageNotifications;
	protected bool $centerGalaxyMapOnPlayer;
	protected array $oldAccountIDs = [];
	protected int $maxRankAchieved;
	protected int $referrerID;
	protected int $credits; // SMR credits
	protected int $rewardCredits; // SMR reward credits
	protected string $dateFormat;
	protected string $timeFormat;
	protected string $template;
	protected string $colourScheme;
	protected array $hotkeys;
	protected array $permissions;
	protected string $friendlyColour;
	protected string $neutralColour;
	protected string $enemyColour;

	protected bool $npc;

	protected bool $hasChanged;

	public static function getDefaultHotkeys(): array {
		return self::DEFAULT_HOTKEYS;
	}

	public static function clearCache(): void {
		self::$CACHE_ACCOUNTS = [];
	}

	public static function getAccount(int $accountID, bool $forceUpdate = false): SmrAccount {
		if ($forceUpdate || !isset(self::$CACHE_ACCOUNTS[$accountID])) {
			self::$CACHE_ACCOUNTS[$accountID] = new SmrAccount($accountID);
		}
		return self::$CACHE_ACCOUNTS[$accountID];
	}

	public static function getAccountByLogin(string $login, bool $forceUpdate = false): SmrAccount {
		if (!empty($login)) {
			$db = Smr\Database::getInstance();
			$dbResult = $db->read('SELECT account_id FROM account WHERE login = ' . $db->escapeString($login));
			if ($dbResult->hasRecord()) {
				$accountID = $dbResult->record()->getInt('account_id');
				return self::getAccount($accountID, $forceUpdate);
			}
		}
		throw new Smr\Exceptions\AccountNotFound('Account login not found.');
	}

	public static function getAccountByHofName(string $hofName, bool $forceUpdate = false): SmrAccount {
		if (!empty($hofName)) {
			$db = Smr\Database::getInstance();
			$dbResult = $db->read('SELECT account_id FROM account WHERE hof_name = ' . $db->escapeString($hofName));
			if ($dbResult->hasRecord()) {
				$accountID = $dbResult->record()->getInt('account_id');
				return self::getAccount($accountID, $forceUpdate);
			}
		}
		throw new Smr\Exceptions\AccountNotFound('Account HoF name not found.');
	}

	public static function getAccountByEmail(?string $email, bool $forceUpdate = false): SmrAccount {
		if (!empty($email)) {
			$db = Smr\Database::getInstance();
			$dbResult = $db->read('SELECT account_id FROM account WHERE email = ' . $db->escapeString($email));
			if ($dbResult->hasRecord()) {
				$accountID = $dbResult->record()->getInt('account_id');
				return self::getAccount($accountID, $forceUpdate);
			}
		}
		throw new Smr\Exceptions\AccountNotFound('Account email not found.');
	}

	public static function getAccountByDiscordId(?string $id, bool $forceUpdate = false): SmrAccount {
		if (!empty($id)) {
			$db = Smr\Database::getInstance();
			$dbResult = $db->read('SELECT account_id FROM account where discord_id = ' . $db->escapeString($id));
			if ($dbResult->hasRecord()) {
				$accountID = $dbResult->record()->getInt('account_id');
				return self::getAccount($accountID, $forceUpdate);
			}
		}
		throw new Smr\Exceptions\AccountNotFound('Account discord ID not found.');
	}

	public static function getAccountByIrcNick(?string $nick, bool $forceUpdate = false): SmrAccount {
		if (!empty($nick)) {
			$db = Smr\Database::getInstance();
			$dbResult = $db->read('SELECT account_id FROM account WHERE irc_nick = ' . $db->escapeString($nick));
			if ($dbResult->hasRecord()) {
				$accountID = $dbResult->record()->getInt('account_id');
				return self::getAccount($accountID, $forceUpdate);
			}
		}
		throw new Smr\Exceptions\AccountNotFound('Account IRC nick not found.');
	}

	public static function getAccountBySocialLogin(Smr\SocialLogin\SocialLogin $social, bool $forceUpdate = false): SmrAccount {
		if ($social->isValid()) {
			$db = Smr\Database::getInstance();
			$dbResult = $db->read('SELECT account_id FROM account JOIN account_auth USING(account_id)
				WHERE login_type = ' . $db->escapeString($social->getLoginType()) . '
				AND auth_key = ' . $db->escapeString($social->getUserID()));
			if ($dbResult->hasRecord()) {
				$accountID = $dbResult->record()->getInt('account_id');
				return self::getAccount($accountID, $forceUpdate);
			}
		}
		throw new Smr\Exceptions\AccountNotFound('Account social login not found.');
	}

	public static function createAccount(string $login, string $password, string $email, int $timez, int $referral): SmrAccount {
		if ($referral != 0) {
			// Will throw if referral account doesn't exist
			SmrAccount::getAccount($referral);
		}
		$db = Smr\Database::getInstance();
		$passwordHash = password_hash($password, PASSWORD_DEFAULT);
		$db->insert('account', [
			'login' => $db->escapeString($login),
			'password' => $db->escapeString($passwordHash),
			'email' => $db->escapeString($email),
			'validation_code' => $db->escapeString(random_string(10)),
			'last_login' => $db->escapeNumber(Smr\Epoch::time()),
			'offset' => $db->escapeNumber($timez),
			'referral_id' => $db->escapeNumber($referral),
			'hof_name' => $db->escapeString($login),
			'hotkeys' => $db->escapeObject([]),
		]);
		return self::getAccountByLogin($login);
	}

	public static function getUserScoreCaseStatement(Smr\Database $db): array {
		$userRankingTypes = [];
		$case = 'IFNULL(FLOOR(SUM(CASE type ';
		foreach (self::USER_RANKINGS_SCORE as $userRankingScore) {
			$userRankingType = $db->escapeString(implode(':', $userRankingScore[0]));
			$userRankingTypes[] = $userRankingType;
			$case .= ' WHEN ' . $userRankingType . ' THEN POW(amount*' . $userRankingScore[1] . ',' . SmrAccount::USER_RANKINGS_EACH_STAT_POW . ')*' . $userRankingScore[2];
		}
		$case .= ' END)), 0)';
		return ['CASE' => $case, 'IN' => implode(',', $userRankingTypes)];
	}

	protected function __construct(protected readonly int $accountID) {
		$this->db = Smr\Database::getInstance();
		$this->SQL = 'account_id = ' . $this->db->escapeNumber($accountID);
		$dbResult = $this->db->read('SELECT * FROM account WHERE ' . $this->SQL);

		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();

			$this->login = $dbRecord->getField('login');
			$this->passwordHash = $dbRecord->getField('password');
			$this->email = $dbRecord->getField('email');
			$this->validated = $dbRecord->getBoolean('validated');

			$this->last_login = $dbRecord->getInt('last_login');
			$this->validation_code = $dbRecord->getField('validation_code');
			$this->veteranForced = $dbRecord->getBoolean('veteran');
			$this->logging = $dbRecord->getBoolean('logging');
			$this->offset = $dbRecord->getInt('offset');
			$this->images = $dbRecord->getBoolean('images');
			$this->fontSize = $dbRecord->getInt('fontsize');

			$this->passwordReset = $dbRecord->getField('password_reset');
			$this->useAJAX = $dbRecord->getBoolean('use_ajax');
			$this->mailBanned = $dbRecord->getInt('mail_banned');

			$this->friendlyColour = $dbRecord->getField('friendly_colour');
			$this->neutralColour = $dbRecord->getField('neutral_colour');
			$this->enemyColour = $dbRecord->getField('enemy_colour');

			$this->cssLink = $dbRecord->getField('css_link');
			$this->defaultCSSEnabled = $dbRecord->getBoolean('default_css_enabled');
			$this->centerGalaxyMapOnPlayer = $dbRecord->getBoolean('center_galaxy_map_on_player');

			$this->messageNotifications = $dbRecord->getObject('message_notifications', false, true);
			$this->hotkeys = $dbRecord->getObject('hotkeys');
			foreach (self::DEFAULT_HOTKEYS as $hotkey => $binding) {
				if (!isset($this->hotkeys[$hotkey])) {
					$this->hotkeys[$hotkey] = $binding;
				}
			}

			foreach (Globals::getHistoryDatabases() as $databaseName => $oldColumn) {
				$this->oldAccountIDs[$databaseName] = $dbRecord->getInt($oldColumn);
			}

			$this->referrerID = $dbRecord->getInt('referral_id');
			$this->maxRankAchieved = $dbRecord->getInt('max_rank_achieved');

			$this->hofName = $dbRecord->getField('hof_name');
			$this->discordId = $dbRecord->getField('discord_id');
			$this->ircNick = $dbRecord->getField('irc_nick');

			$this->dateFormat = $dbRecord->getField('date_format');
			$this->timeFormat = $dbRecord->getField('time_format');

			$this->template = $dbRecord->getField('template');
			$this->colourScheme = $dbRecord->getField('colour_scheme');

			if (empty($this->hofName)) {
				$this->hofName = $this->login;
			}
		} else {
			throw new Smr\Exceptions\AccountNotFound('Account ID ' . $accountID . ' does not exist!');
		}
	}

	/**
	 * Check if the account is disabled.
	 */
	public function isDisabled(): array|false {
		$dbResult = $this->db->read('SELECT * FROM account_is_closed JOIN closing_reason USING(reason_id) WHERE ' . $this->SQL);
		if (!$dbResult->hasRecord()) {
			return false;
		}
		$dbRecord = $dbResult->record();
		// get the expire time
		$expireTime = $dbRecord->getInt('expires');

		// are we over this time?
		if ($expireTime > 0 && $expireTime < Smr\Epoch::time()) {
			// get rid of the expire entry
			$this->unbanAccount();
			return false;
		}
		return [
			'Time' => $expireTime,
			'Reason' => $dbRecord->getField('reason'),
			'ReasonID' => $dbRecord->getInt('reason_id'),
		];
	}

	public function update(): void {
		$this->db->write('UPDATE account SET email = ' . $this->db->escapeString($this->email) .
			', validation_code = ' . $this->db->escapeString($this->validation_code) .
			', validated = ' . $this->db->escapeBoolean($this->validated) .
			', password = ' . $this->db->escapeString($this->passwordHash) .
			', images = ' . $this->db->escapeBoolean($this->images) .
			', password_reset = ' . $this->db->escapeString($this->passwordReset) .
			', use_ajax=' . $this->db->escapeBoolean($this->useAJAX) .
			', mail_banned=' . $this->db->escapeNumber($this->mailBanned) .
			', max_rank_achieved=' . $this->db->escapeNumber($this->maxRankAchieved) .
			', default_css_enabled=' . $this->db->escapeBoolean($this->defaultCSSEnabled) .
			', center_galaxy_map_on_player=' . $this->db->escapeBoolean($this->centerGalaxyMapOnPlayer) .
			', message_notifications=' . $this->db->escapeObject($this->messageNotifications, false, true) .
			', hotkeys=' . $this->db->escapeObject($this->hotkeys) .
			', last_login = ' . $this->db->escapeNumber($this->last_login) .
			', logging = ' . $this->db->escapeBoolean($this->logging) .
			', time_format = ' . $this->db->escapeString($this->timeFormat) .
			', date_format = ' . $this->db->escapeString($this->dateFormat) .
			', discord_id = ' . $this->db->escapeString($this->discordId, true) .
			', irc_nick = ' . $this->db->escapeString($this->ircNick, true) .
			', hof_name = ' . $this->db->escapeString($this->hofName) .
			', template = ' . $this->db->escapeString($this->template) .
			', colour_scheme = ' . $this->db->escapeString($this->colourScheme) .
			', fontsize = ' . $this->db->escapeNumber($this->fontSize) .
			', css_link = ' . $this->db->escapeString($this->cssLink, true) .
			', friendly_colour = ' . $this->db->escapeString($this->friendlyColour, true) .
			', neutral_colour = ' . $this->db->escapeString($this->neutralColour, true) .
			', enemy_colour = ' . $this->db->escapeString($this->enemyColour, true) .
			' WHERE ' . $this->SQL);
		$this->hasChanged = false;
	}

	public function updateIP(): void {
		$curr_ip = getIpAddress();
		$this->log(LOG_TYPE_LOGIN, 'logged in from ' . $curr_ip);

		// more than 50 elements in it?

		$dbResult = $this->db->read('SELECT time,ip FROM account_has_ip WHERE ' . $this->SQL . ' ORDER BY time ASC');
		if ($dbResult->getNumRecords() > 50) {
			$dbRecord = $dbResult->records()->current();
			$delete_time = $dbRecord->getInt('time');
			$delete_ip = $dbRecord->getField('ip');

			$this->db->write('DELETE FROM account_has_ip
				WHERE ' . $this->SQL . ' AND
				time = ' . $this->db->escapeNumber($delete_time) . ' AND
				ip = ' . $this->db->escapeString($delete_ip));
		}
		[$fi, $se, $th, $fo] = preg_split('/[.\s,]/', $curr_ip, 4);
		if ($curr_ip != 'unknown' && $curr_ip != 'unknown...' && $curr_ip != 'unknown, unknown') {
			$curr_ip = $fi . '.' . $se . '.' . $th . '.' . $fo;
			$host = gethostbyaddr($curr_ip);
		} else {
			$host = 'unknown';
		}

		// save...first make sure there isn't one for these keys (someone could double click and get error)
		$this->db->replace('account_has_ip', [
			'account_id' => $this->db->escapeNumber($this->accountID),
			'time' => $this->db->escapeNumber(Smr\Epoch::time()),
			'ip' => $this->db->escapeString($curr_ip),
			'host' => $this->db->escapeString($host),
		]);
	}

	public function updateLastLogin(): void {
		if ($this->last_login == Smr\Epoch::time()) {
			return;
		}
		$this->last_login = Smr\Epoch::time();
		$this->hasChanged = true;
		$this->update();
	}

	public function getLastLogin(): int {
		return $this->last_login;
	}

	public function setLoggingEnabled(bool $bool): void {
		if ($this->logging === $bool) {
			return;
		}
		$this->logging = $bool;
		$this->hasChanged = true;
	}

	public function isLoggingEnabled(): bool {
		return $this->logging;
	}

	public function isVeteranForced(): bool {
		return $this->veteranForced;
	}

	public function isVeteran(): bool {
		// Use maxRankAchieved to avoid a database call to get user stats.
		// This saves a lot of time on the CPL, Rankings, Rosters, etc.
		return $this->isVeteranForced() || $this->maxRankAchieved >= FLEDGLING;
	}

	public function isNPC(): bool {
		if (!isset($this->npc)) {
			$dbResult = $this->db->read('SELECT 1 FROM npc_logins WHERE login = ' . $this->db->escapeString($this->getLogin()));
			$this->npc = $dbResult->hasRecord();
		}
		return $this->npc;
	}

	protected function getHOFData(): void {
		if (!isset($this->HOF)) {
			//Get Player HOF
			$dbResult = $this->db->read('SELECT type,sum(amount) as amount FROM player_hof WHERE ' . $this->SQL . ' AND game_id IN (SELECT game_id FROM game WHERE ignore_stats = \'FALSE\') GROUP BY type');
			$this->HOF = [];
			foreach ($dbResult->records() as $dbRecord) {
				$this->HOF[$dbRecord->getString('type')] = $dbRecord->getFloat('amount');
			}
		}
	}

	public function getHOF(array $typeList): float {
		$this->getHOFData();
		return $this->HOF[implode(':', $typeList)] ?? 0;
	}

	public function getRankName(): string {
		return Smr\UserRanking::getName($this->getRank());
	}

	public function getScore(): int {
		if (!isset($this->score)) {
			$score = 0;
			foreach ($this->getIndividualScores() as $each) {
				$score += $each['Score'];
			}
			$this->score = IRound($score);
		}
		return $this->score;
	}

	public function getIndividualScores(SmrPlayer $player = null): array {
		$gameID = 0;
		if ($player != null) {
			$gameID = $player->getGameID();
		}
		if (!isset($this->individualScores[$gameID])) {
			$this->individualScores[$gameID] = [];
			foreach (self::USER_RANKINGS_SCORE as $statScore) {
				if ($player == null) {
					$stat = $this->getHOF($statScore[0]);
				} else {
					$stat = $player->getHOF($statScore[0]);
				}
				$this->individualScores[$gameID][] = ['Stat' => $statScore[0], 'Score' => pow($stat * $statScore[1], self::USER_RANKINGS_EACH_STAT_POW) * $statScore[2]];
			}
		}
		return $this->individualScores[$gameID];
	}

	public function getRank(): int {
		$rank = Smr\UserRanking::getRankFromScore($this->getScore());
		if ($rank > $this->maxRankAchieved) {
			$this->updateMaxRankAchieved($rank);
		}
		return $rank;
	}

	protected function updateMaxRankAchieved(int $rank): void {
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

	public function getReferrerID(): int {
		return $this->referrerID;
	}

	public function hasReferrer(): bool {
		return $this->referrerID > 0;
	}

	public function getReferrer(): SmrAccount {
		return SmrAccount::getAccount($this->getReferrerID());
	}

	public function log(int $log_type_id, string $msg, int $sector_id = 0): void {
		if ($this->isLoggingEnabled()) {
			$this->db->insert('account_has_logs', [
				'account_id' => $this->db->escapeNumber($this->accountID),
				'microtime' => $this->db->escapeMicrotime(Smr\Epoch::microtime()),
				'log_type_id' => $this->db->escapeNumber($log_type_id),
				'message' => $this->db->escapeString($msg),
				'sector_id' => $this->db->escapeNumber($sector_id),
			]);
		}
	}

	protected function getSmrCreditsData(): void {
		if (!isset($this->credits) || !isset($this->rewardCredits)) {
			$this->credits = 0;
			$this->rewardCredits = 0;
			$dbResult = $this->db->read('SELECT * FROM account_has_credits WHERE ' . $this->SQL);
			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
				$this->credits = $dbRecord->getInt('credits_left');
				$this->rewardCredits = $dbRecord->getInt('reward_credits');
			}
		}
	}

	public function getTotalSmrCredits(): int {
		return $this->getSmrCredits() + $this->getSmrRewardCredits();
	}

	public function decreaseTotalSmrCredits(int $totalCredits): void {
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
			$this->db->replace('account_has_credits', [
				'account_id' => $this->db->escapeNumber($this->getAccountID()),
				'credits_left' => $this->db->escapeNumber($credits),
				'reward_credits' => $this->db->escapeNumber($rewardCredits),
			]);
		} else {
			$this->db->write('UPDATE account_has_credits SET credits_left=' . $this->db->escapeNumber($credits) . ', reward_credits=' . $this->db->escapeNumber($rewardCredits) . ' WHERE ' . $this->SQL);
		}
		$this->credits = $credits;
		$this->rewardCredits = $rewardCredits;
	}

	public function getSmrCredits(): int {
		$this->getSmrCreditsData();
		return $this->credits;
	}

	public function getSmrRewardCredits(): int {
		$this->getSmrCreditsData();
		return $this->rewardCredits;
	}

	public function setSmrCredits(int $credits): void {
		if ($this->getSmrCredits() == $credits) {
			return;
		}
		if ($this->credits == 0 && $this->rewardCredits == 0) {
			$this->db->replace('account_has_credits', [
				'account_id' => $this->db->escapeNumber($this->getAccountID()),
				'credits_left' => $this->db->escapeNumber($credits),
			]);
		} else {
			$this->db->write('UPDATE account_has_credits SET credits_left=' . $this->db->escapeNumber($credits) . ' WHERE ' . $this->SQL);
		}
		$this->credits = $credits;
	}

	public function increaseSmrCredits(int $credits): void {
		if ($credits == 0) {
			return;
		}
		if ($credits < 0) {
			throw new Exception('You cannot gain negative credits');
		}
		$this->setSmrCredits($this->getSmrCredits() + $credits);
	}

	public function decreaseSmrCredits(int $credits): void {
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

	public function setSmrRewardCredits(int $credits): void {
		if ($this->getSmrRewardCredits() === $credits) {
			return;
		}
		if ($this->credits == 0 && $this->rewardCredits == 0) {
			$this->db->replace('account_has_credits', [
				'account_id' => $this->db->escapeNumber($this->getAccountID()),
				'reward_credits' => $this->db->escapeNumber($credits),
			]);
		} else {
			$this->db->write('UPDATE account_has_credits SET reward_credits=' . $this->db->escapeNumber($credits) . ' WHERE ' . $this->SQL);
		}
		$this->rewardCredits = $credits;
	}

	public function increaseSmrRewardCredits(int $credits): void {
		if ($credits == 0) {
			return;
		}
		if ($credits < 0) {
			throw new Exception('You cannot gain negative reward credits');
		}
		$this->setSmrRewardCredits($this->getSmrRewardCredits() + $credits);
	}

	public function sendMessageToBox(int $boxTypeID, string $message): void {
		// send him the message
		self::doMessageSendingToBox($this->getAccountID(), $boxTypeID, $message);
	}

	public static function doMessageSendingToBox(int $senderID, int $boxTypeID, string $message, int $gameID = 0): void {
		$db = Smr\Database::getInstance();
		$db->insert('message_boxes', [
			'box_type_id' => $db->escapeNumber($boxTypeID),
			'game_id' => $db->escapeNumber($gameID),
			'message_text' => $db->escapeString($message),
			'sender_id' => $db->escapeNumber($senderID),
			'send_time' => $db->escapeNumber(Smr\Epoch::time()),
		]);
	}

	public function equals(self $other): bool {
		return $this->getAccountID() == $other->getAccountID();
	}

	public function getAccountID(): int {
		return $this->accountID;
	}

	/**
	 * Return the ID associated with this account in the given history database.
	 */
	public function getOldAccountID(string $dbName): int {
		return $this->oldAccountIDs[$dbName] ?? 0;
	}

	public function getLogin(): string {
		return $this->login;
	}

	public function getEmail(): string {
		return $this->email;
	}

	protected function setEmail(string $email): void {
		if ($this->email === $email) {
			return;
		}
		$this->email = $email;
		$this->hasChanged = true;
	}

	/**
	 * Change e-mail address, unvalidate the account, and resend validation code
	 */
	public function changeEmail(string $email): void {
		// get user and host for the provided address
		[$user, $host] = explode('@', $email);

		// check if the host got a MX or at least an A entry
		if (!checkdnsrr($host, 'MX') && !checkdnsrr($host, 'A')) {
			throw new Smr\Exceptions\UserError('This is not a valid email address! The domain ' . $host . ' does not exist.');
		}

		if (strstr($email, ' ')) {
			throw new Smr\Exceptions\UserError('The email is invalid! It cannot contain any spaces.');
		}

		try {
			$other = self::getAccountByEmail($email);
			if (!$this->equals($other)) {
				throw new Smr\Exceptions\UserError('This email address is already registered.');
			}
		} catch (Smr\Exceptions\AccountNotFound) {
			// Proceed, this email is not yet in use
		}

		$this->setEmail($email);
		$this->setValidationCode(random_string(10));
		$this->setValidated(false);
		$this->sendValidationEmail();

		// Remove an "Invalid email" ban (may or may not have one)
		$disabled = $this->isDisabled();
		if ($disabled !== false) {
			if ($disabled['Reason'] == CLOSE_ACCOUNT_INVALID_EMAIL_REASON) {
				$this->unbanAccount($this);
			}
		}
	}

	public function sendValidationEmail(): void {
		// remember when we sent validation code
		$this->db->replace('notification', [
			'notification_type' => $this->db->escapeString('validation_code'),
			'account_id' => $this->db->escapeNumber($this->getAccountID()),
			'time' => $this->db->escapeNumber(Smr\Epoch::time()),
		]);

		$emailMessage =
			'Your validation code is: ' . $this->getValidationCode() . EOL . EOL .
			'The Space Merchant Realms server is on the web at ' . URL;

		$mail = setupMailer();
		$mail->Subject = 'Space Merchant Realms Validation Code';
		$mail->setFrom('support@smrealms.de', 'SMR Support');
		$mail->msgHTML(nl2br($emailMessage));
		$mail->addAddress($this->getEmail(), $this->getHofName());
		$mail->send();
	}

	public function getOffset(): int {
		return $this->offset;
	}

	public function getFontSize(): int {
		return $this->fontSize;
	}

	public function setFontSize(int $size): void {
		if ($this->fontSize === $size) {
			return;
		}
		$this->fontSize = $size;
		$this->hasChanged = true;
	}

	// gets the extra CSS file linked in preferences
	public function getCssLink(): ?string {
		return $this->cssLink;
	}

	// sets the extra CSS file linked in preferences
	public function setCssLink(string $link): void {
		if ($this->cssLink === $link) {
			return;
		}
		$this->cssLink = $link;
		$this->hasChanged = true;
	}

	public function getTemplate(): string {
		return $this->template;
	}

	public function setTemplate(string $template): void {
		if ($this->template === $template) {
			return;
		}
		if (!in_array($template, Globals::getAvailableTemplates())) {
			throw new Exception('Template not allowed: ' . $template);
		}
		$this->template = $template;
		$this->hasChanged = true;
	}

	public function getColourScheme(): string {
		return $this->colourScheme;
	}

	public function setColourScheme(string $colourScheme): void {
		if ($this->colourScheme === $colourScheme) {
			return;
		}
		if (!in_array($colourScheme, Globals::getAvailableColourSchemes($this->getTemplate()))) {
			throw new Exception('Colour scheme not allowed: ' . $colourScheme);
		}
		$this->colourScheme = $colourScheme;
		$this->hasChanged = true;
	}

	// gets the CSS URL based on the template name specified in preferences
	public function getCssUrl(): string {
		return CSS_URLS[$this->getTemplate()];
	}

	// gets the CSS_COLOUR URL based on the template and color scheme specified in preferences
	public function getCssColourUrl(): string {
		return CSS_COLOUR_URLS[$this->getTemplate()][$this->getColourScheme()];
	}

	/**
	 * The Hall Of Fame name is not html-escaped in the database, so to display
	 * it correctly we must escape html entities.
	 */
	public function getHofDisplayName(bool $linked = false): string {
		$hofDisplayName = htmlspecialchars($this->getHofName());
		if ($linked) {
			return '<a href="' . $this->getPersonalHofHREF() . '">' . $hofDisplayName . '</a>';
		}
		return $hofDisplayName;
	}

	public function getHofName(): string {
		return $this->hofName;
	}

	public function setHofName(string $name): void {
		if ($this->hofName === $name) {
			return;
		}
		$this->hofName = $name;
		$this->hasChanged = true;
	}

	public function getIrcNick(): ?string {
		return $this->ircNick;
	}

	public function setIrcNick(?string $nick): void {
		if ($this->ircNick === $nick) {
			return;
		}
		$this->ircNick = $nick;
		$this->hasChanged = true;
	}

	public function getDiscordId(): ?string {
		return $this->discordId;
	}

	public function setDiscordId(?string $id): void {
		if ($this->discordId === $id) {
			return;
		}
		$this->discordId = $id;
		$this->hasChanged = true;
	}

	public function getReferralLink(): string {
		return URL . '/login_create.php?ref=' . $this->getAccountID();
	}

	/**
	 * Get the epoch format string including both date and time.
	 */
	public function getDateTimeFormat(): string {
		return $this->getDateFormat() . ' ' . $this->getTimeFormat();
	}

	/**
	 * Get the (HTML-only) epoch format string including both date and time,
	 * split across two lines.
	 */
	public function getDateTimeFormatSplit(): string {
		// We need to escape 'r' because it is a format specifier
		return $this->getDateFormat() . '\<b\r /\>' . $this->getTimeFormat();
	}

	public function getDateFormat(): string {
		return $this->dateFormat;
	}

	public function setDateFormat(string $format): void {
		if ($this->dateFormat === $format) {
			return;
		}
		$this->dateFormat = $format;
		$this->hasChanged = true;
	}

	public function getTimeFormat(): string {
		return $this->timeFormat;
	}

	public function setTimeFormat(string $format): void {
		if ($this->timeFormat === $format) {
			return;
		}
		$this->timeFormat = $format;
		$this->hasChanged = true;
	}

	public function getValidationCode(): string {
		return $this->validation_code;
	}

	protected function setValidationCode(string $code): void {
		if ($this->validation_code === $code) {
			return;
		}
		$this->validation_code = $code;
		$this->hasChanged = true;
	}

	public function setValidated(bool $bool): void {
		if ($this->validated === $bool) {
			return;
		}
		$this->validated = $bool;
		$this->hasChanged = true;
	}

	public function isValidated(): bool {
		return $this->validated;
	}

	public function isLoggedIn(): bool {
		$dbResult = $this->db->read('SELECT 1 FROM active_session WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . ' LIMIT 1');
		return $dbResult->hasRecord();
	}

	/**
	 * Check if the given (plain-text) password is correct.
	 * Updates the password hash if necessary.
	 */
	public function checkPassword(string $password): bool {
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
			$this->update();
		}

		return $result;
	}

	/**
	 * Set the (plain-text) password for this account.
	 */
	public function setPassword(string $password): void {
		$hash = password_hash($password, PASSWORD_DEFAULT);
		if ($this->passwordHash === $hash) {
			return;
		}
		$this->passwordHash = $hash;
		$this->generatePasswordReset();
		$this->hasChanged = true;
	}

	public function addAuthMethod(string $loginType, string $authKey): void {
		$dbResult = $this->db->read('SELECT account_id FROM account_auth WHERE login_type=' . $this->db->escapeString($loginType) . ' AND auth_key = ' . $this->db->escapeString($authKey) . ';');
		if ($dbResult->hasRecord()) {
			if ($dbResult->record()->getInt('account_id') != $this->getAccountID()) {
				throw new Exception('Another account already uses this form of auth.');
			}
			return;
		}
		$this->db->insert('account_auth', [
			'account_id' => $this->db->escapeNumber($this->getAccountID()),
			'login_type' => $this->db->escapeString($loginType),
			'auth_key' => $this->db->escapeString($authKey),
		]);
	}

	public function generatePasswordReset(): void {
		$this->setPasswordReset(random_string(32));
	}

	public function getPasswordReset(): string {
		return $this->passwordReset;
	}

	protected function setPasswordReset(string $passwordReset): void {
		if ($this->passwordReset === $passwordReset) {
			return;
		}
		$this->passwordReset = $passwordReset;
		$this->hasChanged = true;
	}

	public function isDisplayShipImages(): bool {
		return $this->images;
	}

	public function setDisplayShipImages(bool $bool): void {
		if ($this->images === $bool) {
			return;
		}
		$this->images = $bool;
		$this->hasChanged = true;
	}

	public function isUseAJAX(): bool {
		return $this->useAJAX;
	}

	public function setUseAJAX(bool $bool): void {
		if ($this->useAJAX === $bool) {
			return;
		}
		$this->useAJAX = $bool;
		$this->hasChanged = true;
	}

	public function isDefaultCSSEnabled(): bool {
		return $this->defaultCSSEnabled;
	}

	public function setDefaultCSSEnabled(bool $bool): void {
		if ($this->defaultCSSEnabled === $bool) {
			return;
		}
		$this->defaultCSSEnabled = $bool;
		$this->hasChanged = true;
	}

	public function getHotkeys(string $hotkeyType = null): array {
		if ($hotkeyType !== null) {
			return $this->hotkeys[$hotkeyType] ?? [];
		}
		return $this->hotkeys;
	}

	public function setHotkey(string $hotkeyType, array $bindings): void {
		if ($this->getHotkeys($hotkeyType) === $bindings) {
			return;
		}
		$this->hotkeys[$hotkeyType] = $bindings;
		$this->hasChanged = true;
	}

	public function isReceivingMessageNotifications(int $messageTypeID): bool {
		return $this->getMessageNotifications($messageTypeID) > 0;
	}

	public function getMessageNotifications(int $messageTypeID): int {
		return $this->messageNotifications[$messageTypeID] ?? 0;
	}

	public function setMessageNotifications(int $messageTypeID, int $num): void {
		if ($this->getMessageNotifications($messageTypeID) == $num) {
			return;
		}
		$this->messageNotifications[$messageTypeID] = $num;
		$this->hasChanged = true;
	}

	public function increaseMessageNotifications(int $messageTypeID, int $num): void {
		if ($num == 0) {
			return;
		}
		if ($num < 0) {
			throw new Exception('You cannot increase by a negative amount');
		}
		$this->setMessageNotifications($messageTypeID, $this->getMessageNotifications($messageTypeID) + $num);
	}

	public function decreaseMessageNotifications(int $messageTypeID, int $num): void {
		if ($num == 0) {
			return;
		}
		if ($num < 0) {
			throw new Exception('You cannot decrease by a negative amount');
		}
		$this->setMessageNotifications($messageTypeID, $this->getMessageNotifications($messageTypeID) - $num);
	}

	public function isCenterGalaxyMapOnPlayer(): bool {
		return $this->centerGalaxyMapOnPlayer;
	}

	public function setCenterGalaxyMapOnPlayer(bool $bool): void {
		if ($this->centerGalaxyMapOnPlayer === $bool) {
			return;
		}
		$this->centerGalaxyMapOnPlayer = $bool;
		$this->hasChanged = true;
	}

	public function getMailBanned(): int {
		return $this->mailBanned;
	}

	public function isMailBanned(): bool {
		return $this->mailBanned > Smr\Epoch::time();
	}

	public function setMailBanned(int $time): void {
		if ($this->mailBanned === $time) {
			return;
		}
		$this->mailBanned = $time;
		$this->hasChanged = true;
	}

	public function increaseMailBanned(int $increaseTime): void {
		$time = max(Smr\Epoch::time(), $this->getMailBanned());
		$this->setMailBanned($time + $increaseTime);
	}

	public function getPermissions(): array {
		if (!isset($this->permissions)) {
			$this->permissions = [];
			$dbResult = $this->db->read('SELECT permission_id FROM account_has_permission WHERE ' . $this->SQL);
			foreach ($dbResult->records() as $dbRecord) {
				$this->permissions[$dbRecord->getInt('permission_id')] = true;
			}
		}
		return $this->permissions;
	}

	public function hasPermission(int $permissionID = null): bool {
		$permissions = $this->getPermissions();
		if ($permissionID === null) {
			return count($permissions) > 0;
		}
		return $permissions[$permissionID] ?? false;
	}

	public function getPoints(): int {
		if (!isset($this->points)) {
			$this->points = 0;
			$this->db->lockTable('account_has_points');
			$dbResult = $this->db->read('SELECT * FROM account_has_points WHERE ' . $this->SQL);
			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
				$this->points = $dbRecord->getInt('points');
				$lastUpdate = $dbRecord->getInt('last_update');
				//we are gonna check for reducing points...
				if ($this->points > 0 && $lastUpdate < Smr\Epoch::time() - (7 * 86400)) {
					$removePoints = 0;
					while ($lastUpdate < Smr\Epoch::time() - (7 * 86400)) {
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

	public function setPoints(int $numPoints, ?int $lastUpdate = null): void {
		$numPoints = max($numPoints, 0);
		if ($this->getPoints() == $numPoints) {
			return;
		}
		if ($this->points == 0) {
			$this->db->insert('account_has_points', [
				'account_id' => $this->db->escapeNumber($this->getAccountID()),
				'points' => $this->db->escapeNumber($numPoints),
				'last_update' => $this->db->escapeNumber($lastUpdate ?? Smr\Epoch::time()),
			]);
		} elseif ($numPoints <= 0) {
			$this->db->write('DELETE FROM account_has_points WHERE ' . $this->SQL);
		} else {
			$this->db->write('UPDATE account_has_points SET points = ' . $this->db->escapeNumber($numPoints) . (isset($lastUpdate) ? ', last_update = ' . $this->db->escapeNumber(Smr\Epoch::time()) : '') . ' WHERE ' . $this->SQL);
		}
		$this->points = $numPoints;
	}

	public function removePoints(int $numPoints, ?int $lastUpdate = null): void {
		if ($numPoints > 0) {
			$this->setPoints($this->getPoints() - $numPoints, $lastUpdate);
		}
	}

	public function addPoints(int $numPoints, SmrAccount $admin, int $reasonID, string $suspicion): int|false {
		//do we have points
		$this->setPoints($this->getPoints() + $numPoints, Smr\Epoch::time());
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
			$expireTime = Smr\Epoch::time() + $days * 86400;
		}
		$this->banAccount($expireTime, $admin, $reasonID, $suspicion);

		return $days;
	}

	public function getFriendlyColour(): string {
		return $this->friendlyColour;
	}
	public function setFriendlyColour(string $colour): void {
		$this->friendlyColour = $colour;
		$this->hasChanged = true;
	}
	public function getNeutralColour(): string {
		return $this->neutralColour;
	}
	public function setNeutralColour(string $colour): void {
		$this->neutralColour = $colour;
		$this->hasChanged = true;
	}
	public function getEnemyColour(): string {
		return $this->enemyColour;
	}
	public function setEnemyColour(string $colour): void {
		$this->enemyColour = $colour;
		$this->hasChanged = true;
	}

	public function banAccount(int $expireTime, SmrAccount $admin, int $reasonID, string $suspicion, bool $removeExceptions = false): void {
		$this->db->replace('account_is_closed', [
			'account_id' => $this->db->escapeNumber($this->getAccountID()),
			'reason_id' => $this->db->escapeNumber($reasonID),
			'suspicion' => $this->db->escapeString($suspicion),
			'expires' => $this->db->escapeNumber($expireTime),
		]);
		$this->db->lockTable('active_session');
		$this->db->write('DELETE FROM active_session WHERE ' . $this->SQL . ' LIMIT 1');
		$this->db->unlock();

		$this->db->insert('account_has_closing_history', [
			'account_id' => $this->db->escapeNumber($this->getAccountID()),
			'time' => $this->db->escapeNumber(Smr\Epoch::time()),
			'admin_id' => $this->db->escapeNumber($admin->getAccountID()),
			'action' => $this->db->escapeString('Closed'),
		]);
		$this->db->write('UPDATE player SET newbie_turns = 1
						WHERE ' . $this->SQL . '
						AND newbie_turns = 0
						AND land_on_planet = ' . $this->db->escapeBoolean(false));

		$dbResult = $this->db->read('SELECT game_id FROM game JOIN player USING (game_id)
						WHERE ' . $this->SQL . '
						AND end_time >= ' . $this->db->escapeNumber(Smr\Epoch::time()));
		foreach ($dbResult->records() as $dbRecord) {
			$player = SmrPlayer::getPlayer($this->getAccountID(), $dbRecord->getInt('game_id'));
			$player->updateTurns();
			$player->update();
		}
		$this->log(LOG_TYPE_ACCOUNT_CHANGES, 'Account closed by ' . $admin->getLogin() . '.');
		if ($removeExceptions !== false) {
			$this->db->write('DELETE FROM account_exceptions WHERE ' . $this->SQL);
		}
	}

	public function unbanAccount(SmrAccount $admin = null, string $currException = null): void {
		$adminID = 0;
		if ($admin !== null) {
			$adminID = $admin->getAccountID();
		}
		$this->db->write('DELETE FROM account_is_closed WHERE ' . $this->SQL);
		$this->db->insert('account_has_closing_history', [
			'account_id' => $this->db->escapeNumber($this->getAccountID()),
			'time' => $this->db->escapeNumber(Smr\Epoch::time()),
			'admin_id' => $this->db->escapeNumber($adminID),
			'action' => $this->db->escapeString('Opened'),
		]);
		$this->db->write('UPDATE player SET last_turn_update = GREATEST(' . $this->db->escapeNumber(Smr\Epoch::time()) . ', last_turn_update) WHERE ' . $this->SQL);
		if ($admin !== null) {
			$this->log(LOG_TYPE_ACCOUNT_CHANGES, 'Account reopened by ' . $admin->getLogin() . '.');
		} else {
			$this->log(LOG_TYPE_ACCOUNT_CHANGES, 'Account automatically reopened.');
		}
		if ($currException !== null) {
			$this->db->replace('account_exceptions', [
				'account_id' => $this->db->escapeNumber($this->getAccountID()),
				'reason' => $this->db->escapeString($currException),
			]);
		}
	}

	public function getToggleAJAXHREF(): string {
		$var = Smr\Session::getInstance()->getCurrentVar();
		return Page::create('toggle_processing.php', '', ['toggle' => 'AJAX', 'referrer' => $var['body']])->href();
	}

	public function getUserRankingHREF(): string {
		return Page::create('skeleton.php', 'rankings_view.php')->href();
	}

	public function getPersonalHofHREF(): string {
		return Page::create('skeleton.php', 'hall_of_fame_player_detail.php', ['account_id' => $this->getAccountID()])->href();
	}

}
