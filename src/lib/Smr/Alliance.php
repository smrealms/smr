<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\Exceptions\AllianceNotFound;
use Smr\Exceptions\UserError;

class Alliance {

	/** @var array<int, array<int, self>> */
	protected static array $CACHE_ALLIANCES = [];

	public const SQL = 'alliance_id = :alliance_id AND game_id = :game_id';
	/** @var array{alliance_id: int, game_id: int} */
	public readonly array $SQLID;

	protected string $allianceName;
	protected ?string $description;
	protected string $password;
	protected bool $recruiting;
	protected int $leaderID;
	protected int $bank;
	protected int $kills;
	protected int $deaths;
	protected string $motd;
	protected string $imgSrc;
	protected ?string $discordServer;
	protected ?string $discordChannel;
	protected string $ircChannel;
	protected int $flagshipID;

	/** @var array<int> */
	protected array $memberList;
	/** @var array<int> */
	protected array $seedlist;

	// Recruit type constants
	public const RECRUIT_OPEN = 'open';
	public const RECRUIT_CLOSED = 'closed';
	public const RECRUIT_PASSWORD = 'password';

	public static function clearCache(): void {
		self::$CACHE_ALLIANCES = [];
	}

	public static function getAlliance(int $allianceID, int $gameID, bool $forceUpdate = false, DatabaseRecord $dbRecord = null): self {
		if ($forceUpdate || !isset(self::$CACHE_ALLIANCES[$gameID][$allianceID])) {
			self::$CACHE_ALLIANCES[$gameID][$allianceID] = new self($allianceID, $gameID, $dbRecord);
		}
		return self::$CACHE_ALLIANCES[$gameID][$allianceID];
	}

	public static function getAllianceByDiscordChannel(string $channel, bool $forceUpdate = false): self {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT alliance.* FROM alliance JOIN game USING(game_id) WHERE discord_channel = :discord_channel AND game.end_time > :now ORDER BY game_id DESC LIMIT 1', [
			'discord_channel' => $db->escapeString($channel),
			'now' => $db->escapeNumber(time()),
		]);
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			return self::getAlliance($dbRecord->getInt('alliance_id'), $dbRecord->getInt('game_id'), $forceUpdate, $dbRecord);
		}
		throw new AllianceNotFound('Alliance Discord Channel not found');
	}

	public static function getAllianceByIrcChannel(string $channel, bool $forceUpdate = false): self {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT alliance_id, game_id FROM irc_alliance_has_channel WHERE channel = :irc_channel LIMIT 1', [
			'irc_channel' => $db->escapeString($channel),
		]);
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			return self::getAlliance($dbRecord->getInt('alliance_id'), $dbRecord->getInt('game_id'), $forceUpdate);
		}
		throw new AllianceNotFound('Alliance IRC Channel not found');
	}

	public static function getAllianceByName(string $name, int $gameID, bool $forceUpdate = false): self {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM alliance WHERE alliance_name = :alliance_name AND game_id = :game_id', [
			'alliance_name' => $db->escapeString($name),
			'game_id' => $db->escapeNumber($gameID),
		]);
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			return self::getAlliance($dbRecord->getInt('alliance_id'), $gameID, $forceUpdate, $dbRecord);
		}
		throw new AllianceNotFound('Alliance name not found');
	}

	protected function __construct(
		protected readonly int $allianceID,
		protected readonly int $gameID,
		DatabaseRecord $dbRecord = null,
	) {
		if ($allianceID !== 0) {
			$db = Database::getInstance();
			$this->SQLID = [
				'alliance_id' => $db->escapeNumber($allianceID),
				'game_id' => $db->escapeNumber($gameID),
			];

			if ($dbRecord === null) {
				$dbResult = $db->read('SELECT * FROM alliance WHERE ' . self::SQL, $this->SQLID);
				if ($dbResult->hasRecord()) {
					$dbRecord = $dbResult->record();
				}
			}
			if ($dbRecord === null) {
				throw new AllianceNotFound('Invalid allianceID: ' . $allianceID . ' OR gameID: ' . $gameID);
			}

			$this->allianceName = $dbRecord->getString('alliance_name');
			$this->password = $dbRecord->getString('alliance_password');
			$this->recruiting = $dbRecord->getBoolean('recruiting');
			$this->description = $dbRecord->getNullableString('alliance_description');
			$this->leaderID = $dbRecord->getInt('leader_id');
			$this->bank = $dbRecord->getInt('alliance_account');
			$this->kills = $dbRecord->getInt('alliance_kills');
			$this->deaths = $dbRecord->getInt('alliance_deaths');
			$this->motd = $dbRecord->getString('mod');
			$this->imgSrc = $dbRecord->getString('img_src');
			$this->discordServer = $dbRecord->getNullableString('discord_server');
			$this->discordChannel = $dbRecord->getNullableString('discord_channel');
			$this->flagshipID = $dbRecord->getInt('flagship_id');
		}
	}

	/**
	 * Create an alliance and return the new object.
	 * Starts alliance with "closed" recruitment (for safety).
	 */
	public static function createAlliance(int $gameID, string $name, bool $allowNHA = false): self {
		$db = Database::getInstance();
		$db->lockTable('alliance');

		// check if the alliance name already exists
		try {
			self::getAllianceByName($name, $gameID);
			$db->unlock();
			throw new UserError('That alliance name already exists.');
		} catch (AllianceNotFound) {
			// alliance with this name does not yet exist
		}

		if (!$allowNHA && trim($name) === NHA_ALLIANCE_NAME) {
			$db->unlock();
			throw new UserError('That alliance name is reserved.');
		}

		// get the next alliance id (start at 1 if there are no alliances yet)
		$dbResult = $db->read('SELECT IFNULL(max(alliance_id), 0) AS alliance_id FROM alliance WHERE game_id = :game_id', [
			'game_id' => $db->escapeNumber($gameID),
		]);
		$allianceID = $dbResult->record()->getInt('alliance_id') + 1;

		// actually create the alliance here
		$db->insert('alliance', [
			'alliance_id' => $allianceID,
			'game_id' => $gameID,
			'alliance_name' => $name,
			'alliance_password' => '',
			'recruiting' => $db->escapeBoolean(false),
		]);
		$db->unlock();

		return self::getAlliance($allianceID, $gameID);
	}

	/**
	 * Returns true if the alliance ID is associated with allianceless players.
	 */
	public function isNone(): bool {
		return $this->allianceID === 0;
	}

	/**
	 * Returns true if the alliance is the Newbie Help Alliance.
	 */
	public function isNHA(): bool {
		return $this->allianceName === NHA_ALLIANCE_NAME;
	}

	public function getAllianceID(): int {
		return $this->allianceID;
	}

	public function getAllianceBBLink(): string {
		return '[alliance=' . $this->allianceID . ']';
	}

	public function getAllianceDisplayName(bool $linked = false, bool $includeAllianceID = false): string {
		$name = htmlentities($this->allianceName);
		if ($includeAllianceID) {
			$name .= ' (' . $this->allianceID . ')';
		}
		if ($linked === true && !$this->hasDisbanded()) {
			return create_link(Globals::getAllianceRosterHREF($this->getAllianceID()), $name);
		}
		return $name;
	}

	/**
	 * Returns the alliance name.
	 * Use getAllianceDisplayName for an HTML-safe version.
	 */
	public function getAllianceName(): string {
		return $this->allianceName;
	}

	public function getGameID(): int {
		return $this->gameID;
	}

	public function getGame(): Game {
		return Game::getGame($this->gameID);
	}

	public function hasDisbanded(): bool {
		return !$this->hasLeader();
	}

	public function hasLeader(): bool {
		return $this->getLeaderID() !== 0;
	}

	public function getLeaderID(): int {
		return $this->leaderID;
	}

	public function getLeader(): AbstractPlayer {
		return Player::getPlayer($this->getLeaderID(), $this->getGameID());
	}

	public function setLeaderID(int $leaderID): void {
		$this->leaderID = $leaderID;
	}

	public function getDiscordServer(): ?string {
		return $this->discordServer;
	}

	public function setDiscordServer(string $serverId): void {
		$this->discordServer = $serverId;
	}

	public function getDiscordChannel(): ?string {
		return $this->discordChannel;
	}

	public function setDiscordChannel(?string $channelId): void {
		$this->discordChannel = $channelId;
	}

	public function getIrcChannel(): string {
		if (!isset($this->ircChannel)) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT channel FROM irc_alliance_has_channel WHERE ' . self::SQL, $this->SQLID);
			if ($dbResult->hasRecord()) {
				$this->ircChannel = $dbResult->record()->getString('channel');
			} else {
				$this->ircChannel = '';
			}
		}
		return $this->ircChannel;
	}

	public function setIrcChannel(string $ircChannel): void {
		$this->getIrcChannel(); // to populate the class attribute
		if ($this->ircChannel === $ircChannel) {
			return;
		}
		$db = Database::getInstance();
		if (strlen($ircChannel) > 0 && $ircChannel !== '#') {
			if ($ircChannel[0] !== '#') {
				$ircChannel = '#' . $ircChannel;
			}
			if ($ircChannel === '#smr' || $ircChannel === '#smr-bar') {
				throw new UserError('Please enter a valid irc channel for your alliance.');
			}

			$db->replace('irc_alliance_has_channel', [
				'channel' => $ircChannel,
				'alliance_id' => $this->getAllianceID(),
				'game_id' => $this->getGameID(),
			]);
		} else {
			$db->delete('irc_alliance_has_channel', $this->SQLID);
		}
		$this->ircChannel = $ircChannel;
	}

	public function hasImageURL(): bool {
		return $this->imgSrc !== '';
	}

	public function getImageURL(): string {
		return $this->imgSrc;
	}

	public function setImageURL(string $url): void {
		$this->imgSrc = $url;
	}

	/**
	 * Get the total credits in the alliance bank account.
	 */
	public function getBank(): int {
		return $this->bank;
	}

	/**
	 * Increases alliance bank account up to the maximum allowed credits.
	 * Returns the amount that was actually added to handle overflow.
	 */
	public function increaseBank(int $credits): int {
		$newTotal = min($this->bank + $credits, MAX_MONEY);
		$actualAdded = $newTotal - $this->bank;
		$this->setBank($newTotal);
		return $actualAdded;
	}

	public function decreaseBank(int $credits): void {
		$newTotal = $this->bank - $credits;
		$this->setBank($newTotal);
	}

	public function setBank(int $credits): void {
		$this->bank = $credits;
	}

	/**
	 * Get (HTML-safe) alliance Message of the Day for display.
	 */
	public function getMotD(): string {
		return htmlentities($this->motd);
	}

	public function setMotD(string $motd): void {
		$this->motd = $motd;
	}

	public function getPassword(): string {
		return $this->password;
	}

	public function hasPassword(): bool {
		return $this->password !== '';
	}

	public function isRecruiting(): bool {
		return $this->recruiting;
	}

	/**
	 * Set the password and recruiting attributes.
	 * The input $password is ignored except for the "password" $type.
	 */
	public function setRecruitType(string $type, string $password): void {
		if ($type === self::RECRUIT_CLOSED) {
			$this->recruiting = false;
			$this->password = '';
		} elseif ($type === self::RECRUIT_OPEN) {
			$this->recruiting = true;
			$this->password = '';
		} elseif ($type === self::RECRUIT_PASSWORD) {
			if ($password === '') {
				throw new Exception('Password must not be empty here');
			}
			$this->recruiting = true;
			$this->password = $password;
		} else {
			throw new Exception('Unknown recruit type: ' . $type);
		}
	}

	public function getRecruitType(): string {
		return match (true) {
			!$this->isRecruiting() => self::RECRUIT_CLOSED,
			!$this->hasPassword() => self::RECRUIT_OPEN,
			default => self::RECRUIT_PASSWORD,
		};
	}

	/**
	 * List of all recruitment types and their descriptions.
	 * Do not change the order of elements in the list!
	 *
	 * @return array<string, string>
	 */
	public static function allRecruitTypes(): array {
		// The first type is the default option when creating new alliances
		return [
			self::RECRUIT_PASSWORD => 'Players can join by password or invitation',
			self::RECRUIT_CLOSED => 'Players can join by invitation only',
			self::RECRUIT_OPEN => 'Anyone can join (no password needed)',
		];
	}

	public function getKills(): int {
		return $this->kills;
	}

	public function getDeaths(): int {
		return $this->deaths;
	}

	/**
	 * Get (HTML-safe) alliance description for display.
	 */
	public function getDescription(): string {
		if ($this->description === null) {
			return '';
		}
		return htmlentities($this->description);
	}

	public function setAllianceDescription(string $description, AbstractPlayer $player = null): void {
		$description = word_filter($description);
		if ($description === $this->description) {
			return;
		}
		if ($player !== null) {
			$boxDescription = 'Alliance ' . $this->getAllianceBBLink() . ' had their description changed to:' . EOL . EOL . $description;
			$player->sendMessageToBox(BOX_ALLIANCE_DESCRIPTIONS, $boxDescription);
		}
		$this->description = $description;
	}

	public function hasFlagship(): bool {
		return $this->flagshipID !== 0;
	}

	/**
	 * Get account ID of the player designated as the alliance flagship.
	 * Returns 0 if no flagship.
	 */
	public function getFlagshipID(): int {
		return $this->flagshipID;
	}

	/**
	 * Designate a player as the alliance flagship by their account ID.
	 */
	public function setFlagshipID(int $accountID): void {
		if ($this->flagshipID === $accountID) {
			return;
		}
		$this->flagshipID = $accountID;
	}

	public function getJoinRestriction(AbstractPlayer $player, bool $doAllianceCheck = true, bool $doRecruitingCheck = true): string|false {
		if ($player->getGame()->isGameType(Game::GAME_TYPE_DRAFT)) {
			return 'Alliance members will be selected by the Draft Leaders.';
		}
		if (!$player->getAccount()->isValidated()) {
			return 'You cannot join an alliance until you validate your account.';
		}
		if ($this->hasDisbanded()) {
			return 'This alliance has disbanded!';
		}
		if ($doAllianceCheck && $player->hasAlliance()) {
			return 'You are already in an alliance!';
		}
		if ($doRecruitingCheck && !$this->isRecruiting()) {
			return 'This alliance is not currently accepting new recruits.';
		}
		if ($player->getAllianceJoinable() > Epoch::time()) {
			return 'You cannot join another alliance for ' . format_time($player->getAllianceJoinable() - Epoch::time()) . '.';
		}
		if ($this->getNumMembers() < $this->getGame()->getAllianceMaxPlayers()) {
			if ($player->hasNewbieStatus()) {
				return false;
			}
			$maxVets = $this->getGame()->getAllianceMaxVets();
			if ($this->getNumMembers() < $maxVets) {
				return false;
			}
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT status FROM player_joined_alliance WHERE account_id = :account_id AND ' . self::SQL, [
				'account_id' => $db->escapeNumber($player->getAccountID()),
				...$this->SQLID,
			]);
			if ($dbResult->hasRecord()) {
				if ($dbResult->record()->getString('status') === 'NEWBIE') {
					return false;
				}
			}
			$dbResult = $db->read('SELECT COUNT(*) AS num_orig_vets
							FROM player_joined_alliance
							JOIN player USING (account_id, alliance_id, game_id)
							WHERE ' . self::SQL . ' AND status=\'VETERAN\'', $this->SQLID);
			if (!$dbResult->hasRecord() || $dbResult->record()->getInt('num_orig_vets') < $maxVets) {
				return false;
			}
		}
		return 'There is not currently enough room for you in this alliance.';
	}

	public function getNumVeterans(): int {
		$numVeterans = 0;
		foreach ($this->getMembers() as $player) {
			if (!$player->hasNewbieStatus()) {
				$numVeterans++;
			}
		}
		return $numVeterans;
	}

	public function getNumMembers(): int {
		return count($this->getMemberIDs());
	}

	public function update(): void {
		$db = Database::getInstance();
		$db->update(
			'alliance',
			[
				'alliance_password' => $this->password,
				'recruiting' => $db->escapeBoolean($this->recruiting),
				'alliance_account' => $this->bank,
				'alliance_description' => $this->description,
				'`mod`' => $this->motd,
				'img_src' => $this->imgSrc,
				'alliance_kills' => $this->kills,
				'alliance_deaths' => $this->deaths,
				'discord_server' => $this->discordServer,
				'discord_channel' => $this->discordChannel,
				'flagship_id' => $this->flagshipID,
				'leader_id' => $this->leaderID,
			],
			$this->SQLID,
		);
	}

	/**
	 * Returns the members of this alliance as an array of Player objects.
	 *
	 * @return array<int, Player>
	 */
	public function getMembers(): array {
		return Player::getAlliancePlayers($this->getGameID(), $this->getAllianceID());
	}

	/**
	 * @return array<int>
	 */
	public function getMemberIDs(): array {
		if (!isset($this->memberList)) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT account_id FROM player WHERE ' . self::SQL, $this->SQLID);

			//we have the list of players put them in an array now
			$this->memberList = [];
			foreach ($dbResult->records() as $dbRecord) {
				$this->memberList[] = $dbRecord->getInt('account_id');
			}
		}
		return $this->memberList;
	}

	/**
	 * @return array<int>
	 */
	public function getActiveIDs(): array {
		$activeIDs = [];

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT account_id
						FROM active_session
						JOIN player USING(account_id, game_id)
						WHERE ' . self::SQL . ' AND last_accessed >= :inactive_time', [
			...$this->SQLID,
			'inactive_time' => $db->escapeNumber(Epoch::time() - TIME_BEFORE_INACTIVE),
		]);

		foreach ($dbResult->records() as $dbRecord) {
			$activeIDs[] = $dbRecord->getInt('account_id');
		}

		return $activeIDs;
	}

	/**
	 * Return all planets owned by members of this alliance.
	 *
	 * @return array<Planet>
	 */
	public function getPlanets(): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT planet.*
			FROM player
			JOIN planet ON player.game_id = planet.game_id AND player.account_id = planet.owner_id
			WHERE player.game_id = :game_id
			AND player.alliance_id = :alliance_id
			ORDER BY planet.sector_id
		', $this->SQLID);
		$planets = [];
		foreach ($dbResult->records() as $dbRecord) {
			$planets[] = Planet::getPlanet($this->gameID, $dbRecord->getInt('sector_id'), false, $dbRecord);
		}
		return $planets;
	}

	/**
	 * Return array of sector_id for sectors in the alliance seedlist.
	 *
	 * @return array<int>
	 */
	public function getSeedlist(): array {
		if (!isset($this->seedlist)) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT sector_id FROM alliance_has_seedlist WHERE ' . self::SQL, $this->SQLID);
			$this->seedlist = [];
			foreach ($dbResult->records() as $dbRecord) {
				$this->seedlist[] = $dbRecord->getInt('sector_id');
			}
		}
		return $this->seedlist;
	}

	/**
	 * Is the given sector in the alliance seedlist?
	 */
	public function isInSeedlist(Sector $sector): bool {
		return in_array($sector->getSectorID(), $this->getSeedlist(), true);
	}

	/**
	 * Create the default roles for this alliance.
	 * This should only be called once after the alliance is created.
	 */
	public function createDefaultRoles(string $newMemberPermission = 'basic'): void {
		// Create leader role
		$withPerDay = ALLIANCE_BANK_UNLIMITED;
		$removeMember = true;
		$changePass = true;
		$changeMOD = true;
		$changeRoles = true;
		$planetAccess = true;
		$exemptWith = true;
		$mbMessages = true;
		$sendAllMsg = true;
		$opLeader = true;
		$viewBonds = true;
		$db = Database::getInstance();
		$db->insert('alliance_has_roles', [
			'alliance_id' => $this->getAllianceID(),
			'game_id' => $this->getGameID(),
			'role_id' => ALLIANCE_ROLE_LEADER,
			'role' => 'Leader',
			'with_per_day' => $withPerDay,
			'remove_member' => $db->escapeBoolean($removeMember),
			'change_pass' => $db->escapeBoolean($changePass),
			'change_mod' => $db->escapeBoolean($changeMOD),
			'change_roles' => $db->escapeBoolean($changeRoles),
			'planet_access' => $db->escapeBoolean($planetAccess),
			'exempt_with' => $db->escapeBoolean($exemptWith),
			'mb_messages' => $db->escapeBoolean($mbMessages),
			'send_alliance_msg' => $db->escapeBoolean($sendAllMsg),
			'op_leader' => $db->escapeBoolean($opLeader),
			'view_bonds' => $db->escapeBoolean($viewBonds),
		]);

		// Create new member role
		switch ($newMemberPermission) {
			case 'full':
				//do nothing, perms already set above.
				break;
			case 'none':
				$withPerDay = 0;
				$removeMember = false;
				$changePass = false;
				$changeMOD = false;
				$changeRoles = false;
				$planetAccess = false;
				$exemptWith = false;
				$mbMessages = false;
				$sendAllMsg = false;
				$opLeader = false;
				$viewBonds = false;
				break;
			case 'basic':
				$withPerDay = ALLIANCE_BANK_UNLIMITED;
				$removeMember = false;
				$changePass = false;
				$changeMOD = false;
				$changeRoles = false;
				$planetAccess = true;
				$exemptWith = false;
				$mbMessages = false;
				$sendAllMsg = false;
				$opLeader = false;
				$viewBonds = false;
				break;
		}
		$db->insert('alliance_has_roles', [
			'alliance_id' => $this->getAllianceID(),
			'game_id' => $this->getGameID(),
			'role_id' => ALLIANCE_ROLE_NEW_MEMBER,
			'role' => 'New Member',
			'with_per_day' => $withPerDay,
			'remove_member' => $db->escapeBoolean($removeMember),
			'change_pass' => $db->escapeBoolean($changePass),
			'change_mod' => $db->escapeBoolean($changeMOD),
			'change_roles' => $db->escapeBoolean($changeRoles),
			'planet_access' => $db->escapeBoolean($planetAccess),
			'exempt_with' => $db->escapeBoolean($exemptWith),
			'mb_messages' => $db->escapeBoolean($mbMessages),
			'send_alliance_msg' => $db->escapeBoolean($sendAllMsg),
			'op_leader' => $db->escapeBoolean($opLeader),
			'view_bonds' => $db->escapeBoolean($viewBonds),
		]);
	}

}
