<?php declare(strict_types=1);
require_once('missions.inc.php');

use Smr\BountyType;
use Smr\Database;
use Smr\DatabaseRecord;
use Smr\DisplayNameValidator;
use Smr\Epoch;
use Smr\Exceptions\AccountNotFound;
use Smr\Exceptions\PlayerNotFound;
use Smr\Exceptions\UserError;
use Smr\Messages;
use Smr\Pages\Player\ExamineTrader;
use Smr\Pages\Player\NewbieLeaveProcessor;
use Smr\Pages\Player\Planet\KickProcessor;
use Smr\Pages\Player\SearchForTraderResult;
use Smr\Pages\Player\WeaponDisplayToggleProcessor;
use Smr\Path;
use Smr\Race;
use Smr\ScoutMessageGroupType;
use Smr\StoredDestination;
use Smr\TurnsLevel;

abstract class AbstractSmrPlayer {

	use Traits\RaceID;

	protected const TIME_FOR_FEDERAL_BOUNTY_ON_PR = 10800;
	protected const TIME_FOR_ALLIANCE_SWITCH = 0;

	protected const SHIP_INSURANCE_FRACTION = 0.25; // ship value regained on death

	protected const HOF_CHANGED = 1;
	protected const HOF_NEW = 2;

	/** @var array<int, array<int, array<int, SmrPlayer>>> */
	protected static array $CACHE_SECTOR_PLAYERS = [];
	/** @var array<int, array<int, array<int, SmrPlayer>>> */
	protected static array $CACHE_PLANET_PLAYERS = [];
	/** @var array<int, array<int, array<int, SmrPlayer>>> */
	protected static array $CACHE_ALLIANCE_PLAYERS = [];
	/** @var array<int, array<int, SmrPlayer>> */
	protected static array $CACHE_PLAYERS = [];

	protected Database $db;
	protected readonly string $SQL;

	protected string $playerName;
	protected int $playerID;
	protected int $sectorID;
	protected int $lastSectorID;
	protected int $newbieTurns;
	protected bool $dead;
	protected bool $npc = false; // initialized for legacy combat logs
	protected bool $newbieStatus;
	protected bool $newbieWarning;
	protected bool $landedOnPlanet;
	protected int $lastActive;
	protected int $credits;
	protected int $alignment;
	protected int $experience;
	protected ?int $level;
	protected int $allianceID;
	protected int $shipID;
	protected int $kills;
	protected int $deaths;
	protected int $assists;
	/** @var array<int, int> */
	protected array $personalRelations;
	/** @var array<int, int> */
	protected array $relations;
	protected int $militaryPayment;
	/** @var array<int, array<string, mixed>> */
	protected array $bounties;
	protected int $turns;
	protected int $lastCPLAction;
	/** @var array<int, array<string, mixed>> */
	protected array $missions;

	/** @var array<string, array<string, mixed>> */
	protected array $tickers;
	protected int $lastTurnUpdate;
	protected int $lastNewsUpdate;
	protected string $attackColour;
	protected int $allianceJoinable;
	protected int $lastPort;
	protected int $bank;
	protected int $zoom;
	protected bool $displayMissions;
	protected bool $displayWeapons;
	protected bool $forceDropMessages;
	protected ScoutMessageGroupType $scoutMessageGroupType;
	protected bool $ignoreGlobals;
	protected ?Path $plottedCourse;
	protected bool $nameChanged;
	protected bool $raceChanged;
	protected bool $combatDronesKamikazeOnMines;
	protected string|false $customShipName;
	/** @var array<int, StoredDestination> */
	protected array $storedDestinations;
	/** @var array<int, bool> */
	protected array $canFed;
	protected bool $underAttack;

	/** @var array<int> */
	protected array $unvisitedSectors;
	/** @var array<int, int> */
	protected array $allianceRoles = [
		0 => 0,
	];

	protected bool $draftLeader;
	protected string|false $gpWriter;
	/** @var array<string, float> */
	protected array $HOF;
	/** @var array<string, string> */
	protected static array $HOFVis;

	protected bool $hasChanged = false;
	/** @var array<string, int> */
	protected array $hasHOFChanged = [];
	/** @var array<string, int> */
	protected static array $hasHOFVisChanged = [];
	/** @var array<int, bool> */
	protected array $hasBountyChanged = [];

	public static function clearCache(): void {
		self::$CACHE_PLAYERS = [];
		self::$CACHE_SECTOR_PLAYERS = [];
		self::$CACHE_PLANET_PLAYERS = [];
		self::$CACHE_ALLIANCE_PLAYERS = [];
	}

	public static function savePlayers(): void {
		foreach (self::$CACHE_PLAYERS as $gamePlayers) {
			foreach ($gamePlayers as $player) {
				$player->save();
			}
		}
	}

	/**
	 * @param array<int> $allianceIDs
	 * @return array<int, SmrPlayer>
	 */
	public static function getSectorPlayersByAlliances(int $gameID, int $sectorID, array $allianceIDs, bool $forceUpdate = false): array {
		$players = self::getSectorPlayers($gameID, $sectorID, $forceUpdate); // Don't use & as we do an unset
		foreach ($players as $accountID => $player) {
			if (!in_array($player->getAllianceID(), $allianceIDs)) {
				unset($players[$accountID]);
			}
		}
		return $players;
	}

	/**
	 * Returns the same players as getSectorPlayers (e.g. not on planets),
	 * but for an entire galaxy rather than a single sector. This is useful
	 * for reducing the number of queries in galaxy-wide processing.
	 *
	 * @return array<int, array<int, SmrPlayer>>
	 */
	public static function getGalaxyPlayers(int $gameID, int $galaxyID, bool $forceUpdate = false): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT player.* FROM player LEFT JOIN sector USING(game_id, sector_id) WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND land_on_planet = ' . $db->escapeBoolean(false) . ' AND (last_cpl_action > ' . $db->escapeNumber(Epoch::time() - TIME_BEFORE_HIDDEN) . ' OR newbie_turns = 0) AND galaxy_id = ' . $db->escapeNumber($galaxyID));
		$galaxyPlayers = [];
		foreach ($dbResult->records() as $dbRecord) {
			$sectorID = $dbRecord->getInt('sector_id');
			$accountID = $dbRecord->getInt('account_id');
			$player = self::getPlayer($accountID, $gameID, $forceUpdate, $dbRecord);
			self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID][$accountID] = $player;
			$galaxyPlayers[$sectorID][$accountID] = $player;
		}
		return $galaxyPlayers;
	}

	/**
	 * @return array<int, SmrPlayer>
	 */
	public static function getSectorPlayers(int $gameID, int $sectorID, bool $forceUpdate = false): array {
		if ($forceUpdate || !isset(self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID])) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM player WHERE sector_id = ' . $db->escapeNumber($sectorID) . ' AND game_id=' . $db->escapeNumber($gameID) . ' AND land_on_planet = ' . $db->escapeBoolean(false) . ' AND (last_cpl_action > ' . $db->escapeNumber(Epoch::time() - TIME_BEFORE_HIDDEN) . ' OR newbie_turns = 0) AND account_id NOT IN (' . $db->escapeArray(Globals::getHiddenPlayers()) . ') ORDER BY last_cpl_action DESC');
			$players = [];
			foreach ($dbResult->records() as $dbRecord) {
				$accountID = $dbRecord->getInt('account_id');
				$players[$accountID] = self::getPlayer($accountID, $gameID, $forceUpdate, $dbRecord);
			}
			self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID] = $players;
		}
		return self::$CACHE_SECTOR_PLAYERS[$gameID][$sectorID];
	}

	/**
	 * @return array<int, SmrPlayer>
	 */
	public static function getPlanetPlayers(int $gameID, int $sectorID, bool $forceUpdate = false): array {
		if ($forceUpdate || !isset(self::$CACHE_PLANET_PLAYERS[$gameID][$sectorID])) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM player WHERE sector_id = ' . $db->escapeNumber($sectorID) . ' AND game_id=' . $db->escapeNumber($gameID) . ' AND land_on_planet = ' . $db->escapeBoolean(true) . ' AND account_id NOT IN (' . $db->escapeArray(Globals::getHiddenPlayers()) . ') ORDER BY last_cpl_action DESC');
			$players = [];
			foreach ($dbResult->records() as $dbRecord) {
				$accountID = $dbRecord->getInt('account_id');
				$players[$accountID] = self::getPlayer($accountID, $gameID, $forceUpdate, $dbRecord);
			}
			self::$CACHE_PLANET_PLAYERS[$gameID][$sectorID] = $players;
		}
		return self::$CACHE_PLANET_PLAYERS[$gameID][$sectorID];
	}

	/**
	 * @return array<int, SmrPlayer>
	 */
	public static function getAlliancePlayers(int $gameID, int $allianceID, bool $forceUpdate = false): array {
		if ($forceUpdate || !isset(self::$CACHE_ALLIANCE_PLAYERS[$gameID][$allianceID])) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM player WHERE alliance_id = ' . $db->escapeNumber($allianceID) . ' AND game_id=' . $db->escapeNumber($gameID) . ' ORDER BY experience DESC');
			$players = [];
			foreach ($dbResult->records() as $dbRecord) {
				$accountID = $dbRecord->getInt('account_id');
				$players[$accountID] = self::getPlayer($accountID, $gameID, $forceUpdate, $dbRecord);
			}
			self::$CACHE_ALLIANCE_PLAYERS[$gameID][$allianceID] = $players;
		}
		return self::$CACHE_ALLIANCE_PLAYERS[$gameID][$allianceID];
	}

	public static function getPlayer(int $accountID, int $gameID, bool $forceUpdate = false, DatabaseRecord $dbRecord = null): SmrPlayer {
		if ($forceUpdate || !isset(self::$CACHE_PLAYERS[$gameID][$accountID])) {
			self::$CACHE_PLAYERS[$gameID][$accountID] = new SmrPlayer($gameID, $accountID, $dbRecord);
		}
		return self::$CACHE_PLAYERS[$gameID][$accountID];
	}

	public static function getPlayerByPlayerID(int $playerID, int $gameID, bool $forceUpdate = false): SmrPlayer {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM player WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND player_id = ' . $db->escapeNumber($playerID));
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			return self::getPlayer($dbRecord->getInt('account_id'), $gameID, $forceUpdate, $dbRecord);
		}
		throw new PlayerNotFound('Player ID not found.');
	}

	public static function getPlayerByPlayerName(string $playerName, int $gameID, bool $forceUpdate = false): SmrPlayer {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM player WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND player_name = ' . $db->escapeString($playerName));
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			return self::getPlayer($dbRecord->getInt('account_id'), $gameID, $forceUpdate, $dbRecord);
		}
		throw new PlayerNotFound('Player Name not found.');
	}

	protected function __construct(
		protected readonly int $gameID,
		protected readonly int $accountID,
		DatabaseRecord $dbRecord = null
	) {
		$this->db = Database::getInstance();
		$this->SQL = 'account_id = ' . $this->db->escapeNumber($accountID) . ' AND game_id = ' . $this->db->escapeNumber($gameID);

		if ($dbRecord === null) {
			$dbResult = $this->db->read('SELECT * FROM player WHERE ' . $this->SQL);
			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
			}
		}
		if ($dbRecord === null) {
			throw new PlayerNotFound('Invalid accountID: ' . $accountID . ' OR gameID: ' . $gameID);
		}

		$this->playerName = $dbRecord->getString('player_name');
		$this->playerID = $dbRecord->getInt('player_id');
		$this->sectorID = $dbRecord->getInt('sector_id');
		$this->lastSectorID = $dbRecord->getInt('last_sector_id');
		$this->turns = $dbRecord->getInt('turns');
		$this->lastTurnUpdate = $dbRecord->getInt('last_turn_update');
		$this->newbieTurns = $dbRecord->getInt('newbie_turns');
		$this->lastNewsUpdate = $dbRecord->getInt('last_news_update');
		$this->attackColour = $dbRecord->getString('attack_warning');
		$this->dead = $dbRecord->getBoolean('dead');
		$this->npc = $dbRecord->getBoolean('npc');
		$this->newbieStatus = $dbRecord->getBoolean('newbie_status');
		$this->landedOnPlanet = $dbRecord->getBoolean('land_on_planet');
		$this->lastActive = $dbRecord->getInt('last_active');
		$this->lastCPLAction = $dbRecord->getInt('last_cpl_action');
		$this->raceID = $dbRecord->getInt('race_id');
		$this->credits = $dbRecord->getInt('credits');
		$this->experience = $dbRecord->getInt('experience');
		$this->alignment = $dbRecord->getInt('alignment');
		$this->militaryPayment = $dbRecord->getInt('military_payment');
		$this->allianceID = $dbRecord->getInt('alliance_id');
		$this->allianceJoinable = $dbRecord->getInt('alliance_join');
		$this->shipID = $dbRecord->getInt('ship_type_id');
		$this->kills = $dbRecord->getInt('kills');
		$this->deaths = $dbRecord->getInt('deaths');
		$this->assists = $dbRecord->getInt('assists');
		$this->lastPort = $dbRecord->getInt('last_port');
		$this->bank = $dbRecord->getInt('bank');
		$this->zoom = $dbRecord->getInt('zoom');
		$this->displayMissions = $dbRecord->getBoolean('display_missions');
		$this->displayWeapons = $dbRecord->getBoolean('display_weapons');
		$this->forceDropMessages = $dbRecord->getBoolean('force_drop_messages');
		$this->scoutMessageGroupType = ScoutMessageGroupType::from($dbRecord->getString('group_scout_messages'));
		$this->ignoreGlobals = $dbRecord->getBoolean('ignore_globals');
		$this->newbieWarning = $dbRecord->getBoolean('newbie_warning');
		$this->nameChanged = $dbRecord->getBoolean('name_changed');
		$this->raceChanged = $dbRecord->getBoolean('race_changed');
		$this->combatDronesKamikazeOnMines = $dbRecord->getBoolean('combat_drones_kamikaze_on_mines');
		$this->underAttack = $dbRecord->getBoolean('under_attack');
	}

	/**
	 * Insert a new player into the database. Returns the new player object.
	 */
	public static function createPlayer(int $accountID, int $gameID, string $playerName, int $raceID, bool $isNewbie, bool $npc = false): self {
		$time = Epoch::time();
		$db = Database::getInstance();
		$db->lockTable('player');

		// Player names must be unique within each game
		try {
			self::getPlayerByPlayerName($playerName, $gameID);
			$db->unlock();
			throw new UserError('That player name already exists.');
		} catch (PlayerNotFound) {
			// Player name does not yet exist, we may proceed
		}

		// Get the next available player ID (start at 1 if no players yet)
		$dbResult = $db->read('SELECT IFNULL(MAX(player_id), 0) AS player_id FROM player WHERE game_id = ' . $db->escapeNumber($gameID));
		$playerID = $dbResult->record()->getInt('player_id') + 1;

		$startSectorID = 0; // Temporarily put player into non-existent sector
		$db->insert('player', [
			'account_id' => $db->escapeNumber($accountID),
			'game_id' => $db->escapeNumber($gameID),
			'player_id' => $db->escapeNumber($playerID),
			'player_name' => $db->escapeString($playerName),
			'race_id' => $db->escapeNumber($raceID),
			'sector_id' => $db->escapeNumber($startSectorID),
			'last_cpl_action' => $db->escapeNumber($time),
			'last_active' => $db->escapeNumber($time),
			'npc' => $db->escapeBoolean($npc),
			'newbie_status' => $db->escapeBoolean($isNewbie),
		]);
		$db->unlock();

		$player = self::getPlayer($accountID, $gameID);
		$player->setSectorID($player->getHome());
		return $player;
	}

	/**
	 * Get array of players whose info can be accessed by this player.
	 * Skips players who are not in the same alliance as this player.
	 *
	 * @return array<AbstractSmrPlayer>
	 */
	public function getSharingPlayers(bool $forceUpdate = false): array {
		$results = [$this];

		// Only return this player if not in an alliance
		if (!$this->hasAlliance()) {
			return $results;
		}

		// Get other players who are sharing info for this game.
		// NOTE: game_id=0 means that player shares info for all games.
		$dbResult = $this->db->read('SELECT from_account_id FROM account_shares_info WHERE to_account_id=' . $this->db->escapeNumber($this->getAccountID()) . ' AND (game_id=0 OR game_id=' . $this->db->escapeNumber($this->getGameID()) . ')');
		foreach ($dbResult->records() as $dbRecord) {
			try {
				$otherPlayer = self::getPlayer($dbRecord->getInt('from_account_id'), $this->getGameID(), $forceUpdate);
			} catch (PlayerNotFound) {
				// Skip players that have not joined this game
				continue;
			}

			// players must be in the same alliance
			if ($this->sameAlliance($otherPlayer)) {
				$results[] = $otherPlayer;
			}
		}
		return $results;
	}

	public function getSQL(): string {
		return $this->SQL;
	}

	public function getZoom(): int {
		return $this->zoom;
	}

	protected function setZoom(int $zoom): void {
		// Set the zoom level between [1, 9]
		$zoom = max(1, min(9, $zoom));
		if ($this->zoom == $zoom) {
			return;
		}
		$this->zoom = $zoom;
		$this->hasChanged = true;
	}

	public function increaseZoom(int $zoom): void {
		if ($zoom < 0) {
			throw new Exception('Trying to increase negative zoom.');
		}
		$this->setZoom($this->getZoom() + $zoom);
	}

	public function decreaseZoom(int $zoom): void {
		if ($zoom < 0) {
			throw new Exception('Trying to decrease negative zoom.');
		}
		$this->setZoom($this->getZoom() - $zoom);
	}

	public function getAttackColour(): string {
		return $this->attackColour;
	}

	public function setAttackColour(string $colour): void {
		if ($this->attackColour == $colour) {
			return;
		}
		$this->attackColour = $colour;
		$this->hasChanged = true;
	}

	public function isIgnoreGlobals(): bool {
		return $this->ignoreGlobals;
	}

	public function setIgnoreGlobals(bool $bool): void {
		if ($this->ignoreGlobals == $bool) {
			return;
		}
		$this->ignoreGlobals = $bool;
		$this->hasChanged = true;
	}

	public function getAccount(): SmrAccount {
		return SmrAccount::getAccount($this->getAccountID());
	}

	public function getAccountID(): int {
		return $this->accountID;
	}

	public function getGameID(): int {
		return $this->gameID;
	}

	public function getGame(bool $forceUpdate = false): SmrGame {
		return SmrGame::getGame($this->gameID, $forceUpdate);
	}

	public function getNewbieTurns(): int {
		return $this->newbieTurns;
	}

	public function hasNewbieTurns(): bool {
		return $this->getNewbieTurns() > 0;
	}

	public function setNewbieTurns(int $newbieTurns): void {
		if ($this->newbieTurns == $newbieTurns) {
			return;
		}
		$this->newbieTurns = $newbieTurns;
		$this->hasChanged = true;
	}

	public function getShip(bool $forceUpdate = false): AbstractSmrShip {
		return SmrShip::getShip($this, $forceUpdate);
	}

	public function getShipTypeID(): int {
		return $this->shipID;
	}

	/**
	 * Do not call directly. Use SmrShip::setTypeID instead.
	 */
	public function setShipTypeID(int $shipID): void {
		if ($this->shipID == $shipID) {
			return;
		}
		$this->shipID = $shipID;
		$this->hasChanged = true;
	}

	public function hasCustomShipName(): bool {
		return $this->getCustomShipName() !== false;
	}

	public function getCustomShipName(): string|false {
		if (!isset($this->customShipName)) {
			$dbResult = $this->db->read('SELECT * FROM ship_has_name WHERE ' . $this->SQL);
			if ($dbResult->hasRecord()) {
				$this->customShipName = $dbResult->record()->getString('ship_name');
			} else {
				$this->customShipName = false;
			}
		}
		return $this->customShipName;
	}

	public function setCustomShipName(string $name): void {
		$this->db->replace('ship_has_name', [
			'game_id' => $this->db->escapeNumber($this->getGameID()),
			'account_id' => $this->db->escapeNumber($this->getAccountID()),
			'ship_name' => $this->db->escapeString($name),
		]);
	}

	/**
	 * Get planet owned by this player.
	 * Returns null if this player does not own a planet.
	 */
	public function getPlanet(): ?SmrPlanet {
		$dbResult = $this->db->read('SELECT * FROM planet WHERE game_id=' . $this->db->escapeNumber($this->getGameID()) . ' AND owner_id=' . $this->db->escapeNumber($this->getAccountID()));
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			return SmrPlanet::getPlanet($this->getGameID(), $dbRecord->getInt('sector_id'), false, $dbRecord);
		}
		return null;
	}

	public function getSectorPlanet(): SmrPlanet {
		return SmrPlanet::getPlanet($this->getGameID(), $this->getSectorID());
	}

	public function getSectorPort(): AbstractSmrPort {
		return SmrPort::getPort($this->getGameID(), $this->getSectorID());
	}

	public function getSectorID(): int {
		return $this->sectorID;
	}

	public function getSector(): SmrSector {
		return SmrSector::getSector($this->getGameID(), $this->getSectorID());
	}

	public function setSectorID(int $sectorID): void {
		if ($this->sectorID == $sectorID) {
			return;
		}

		$port = SmrPort::getPort($this->getGameID(), $this->getSectorID());
		$port->addCachePort($this->getAccountID()); //Add port of sector we were just in, to make sure it is left totally up to date.

		$this->setLastSectorID($this->getSectorID());
		$this->actionTaken('LeaveSector', ['SectorID' => $this->getSectorID()]);
		$this->sectorID = $sectorID;
		$this->actionTaken('EnterSector', ['SectorID' => $this->getSectorID()]);
		$this->hasChanged = true;

		$port = SmrPort::getPort($this->getGameID(), $this->getSectorID());
		$port->addCachePort($this->getAccountID()); //Add the port of sector we are now in.
	}

	public function getLastSectorID(): int {
		return $this->lastSectorID;
	}

	public function setLastSectorID(int $lastSectorID): void {
		if ($this->lastSectorID == $lastSectorID) {
			return;
		}
		$this->lastSectorID = $lastSectorID;
		$this->hasChanged = true;
	}

	public function getHome(): int {
		// Draft games may have customized home sectors
		if ($this->getGame()->isGameType(SmrGame::GAME_TYPE_DRAFT) && $this->hasAlliance()) {
			$leaderID = $this->getAlliance()->getLeaderID();
			$dbResult = $this->db->read('SELECT home_sector_id FROM draft_leaders WHERE account_id = ' . $this->db->escapeNumber($leaderID) . ' AND game_id = ' . $this->db->escapeNumber($this->getGameID()));
			if ($dbResult->hasRecord()) {
				return $dbResult->record()->getInt('home_sector_id');
			}
		}

		// get his home sector
		$hq_id = GOVERNMENT + $this->getRaceID();
		$raceHqSectors = SmrSector::getLocationSectors($this->getGameID(), $hq_id);
		if (empty($raceHqSectors)) {
			// No HQ, default to sector 1
			return 1;
		}
		// If race has multiple HQ's for some reason, use the first one
		return key($raceHqSectors);
	}

	public function isDead(): bool {
		return $this->dead;
	}

	public function isNPC(): bool {
		return $this->npc;
	}

	/**
	 * Does the player have Newbie status?
	 */
	public function hasNewbieStatus(): bool {
		return $this->newbieStatus;
	}

	/**
	 * Update the player's newbie status if it has changed.
	 * This function queries the account, so use sparingly.
	 */
	public function updateNewbieStatus(): void {
		$accountNewbieStatus = !$this->getAccount()->isVeteran();
		if ($this->newbieStatus != $accountNewbieStatus) {
			$this->newbieStatus = $accountNewbieStatus;
			$this->hasChanged = true;
		}
	}

	/**
	 * Has this player been designated as the alliance flagship?
	 */
	public function isFlagship(): bool {
		return $this->hasAlliance() && $this->getAlliance()->getFlagshipID() == $this->getAccountID();
	}

	public function isPresident(): bool {
		return Council::getPresidentID($this->getGameID(), $this->getRaceID()) == $this->getAccountID();
	}

	public function isOnCouncil(): bool {
		return Council::isOnCouncil($this->getGameID(), $this->getRaceID(), $this->getAccountID());
	}

	public function isDraftLeader(): bool {
		if (!isset($this->draftLeader)) {
			$dbResult = $this->db->read('SELECT 1 FROM draft_leaders WHERE ' . $this->SQL);
			$this->draftLeader = $dbResult->hasRecord();
		}
		return $this->draftLeader;
	}

	public function getGPWriter(): string|false {
		if (!isset($this->gpWriter)) {
			$this->gpWriter = false;
			$dbResult = $this->db->read('SELECT position FROM galactic_post_writer WHERE ' . $this->SQL);
			if ($dbResult->hasRecord()) {
				$this->gpWriter = $dbResult->record()->getString('position');
			}
		}
		return $this->gpWriter;
	}

	public function isGPEditor(): bool {
		return $this->getGPWriter() == 'editor';
	}

	public function isForceDropMessages(): bool {
		return $this->forceDropMessages;
	}

	public function setForceDropMessages(bool $bool): void {
		if ($this->forceDropMessages == $bool) {
			return;
		}
		$this->forceDropMessages = $bool;
		$this->hasChanged = true;
	}

	public function getScoutMessageGroupLimit(): int {
		return match ($this->scoutMessageGroupType) {
			ScoutMessageGroupType::Always => 0,
			ScoutMessageGroupType::Auto => MESSAGES_PER_PAGE,
			ScoutMessageGroupType::Never => PHP_INT_MAX,
		};
	}

	public function getScoutMessageGroupType(): ScoutMessageGroupType {
		return $this->scoutMessageGroupType;
	}

	public function setScoutMessageGroupType(ScoutMessageGroupType $setting): void {
		if ($this->scoutMessageGroupType === $setting) {
			return;
		}
		$this->scoutMessageGroupType = $setting;
		$this->hasChanged = true;
	}

	/**
	 * @return int Message ID
	 */
	protected static function doMessageSending(int $senderID, int $receiverID, int $gameID, int $messageTypeID, string $message, int $expires, bool $senderDelete = false, bool $unread = true): int {
		$message = trim($message);
		$db = Database::getInstance();
		// Keep track of the message_id so it can be returned
		$insertID = $db->insert('message', [
			'account_id' => $db->escapeNumber($receiverID),
			'game_id' => $db->escapeNumber($gameID),
			'message_type_id' => $db->escapeNumber($messageTypeID),
			'message_text' => $db->escapeString($message),
			'sender_id' => $db->escapeNumber($senderID),
			'send_time' => $db->escapeNumber(Epoch::time()),
			'expire_time' => $db->escapeNumber($expires),
			'sender_delete' => $db->escapeBoolean($senderDelete),
		]);

		if ($unread === true) {
			// give him the message icon
			$db->replace('player_has_unread_messages', [
				'game_id' => $db->escapeNumber($gameID),
				'account_id' => $db->escapeNumber($receiverID),
				'message_type_id' => $db->escapeNumber($messageTypeID),
			]);
		}

		switch ($messageTypeID) {
			case MSG_PLAYER:
				$receiverAccount = SmrAccount::getAccount($receiverID);
				if ($receiverAccount->isValidated() && $receiverAccount->isReceivingMessageNotifications($messageTypeID) && !$receiverAccount->isActive()) {
					$sender = Messages::getMessagePlayer($senderID, $gameID, $messageTypeID);
					if ($sender instanceof self) {
						$sender = $sender->getDisplayName();
					}
					$mail = setupMailer();
					$mail->Subject = 'Message Notification';
					$mail->setFrom('notifications@smrealms.de', 'SMR Notifications');
					$bbifiedMessage = 'From: ' . $sender . ' Date: ' . date($receiverAccount->getDateTimeFormat(), Epoch::time()) . "<br/>\r\n<br/>\r\n" . bbifyMessage($message, $gameID, true);
					$mail->msgHTML($bbifiedMessage);
					$mail->AltBody = strip_tags($bbifiedMessage);
					$mail->addAddress($receiverAccount->getEmail(), $receiverAccount->getHofName());
					$mail->send();
					$receiverAccount->decreaseMessageNotifications($messageTypeID, 1);
					$receiverAccount->update();
				}
				break;
		}

		return $insertID;
	}

	public function sendMessageToBox(int $boxTypeID, string $message): void {
		// send him the message
		SmrAccount::doMessageSendingToBox($this->getAccountID(), $boxTypeID, $message, $this->getGameID());
	}

	public function sendGlobalMessage(string $message, bool $canBeIgnored = true): void {
		if ($canBeIgnored) {
			if ($this->getAccount()->isMailBanned()) {
				throw new UserError('You are currently banned from sending messages');
			}
		}
		$this->sendMessageToBox(BOX_GLOBALS, $message);

		// send to all online player
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT account_id
					FROM active_session
					JOIN player USING (game_id, account_id)
					WHERE active_session.last_accessed >= ' . $db->escapeNumber(Epoch::time() - TIME_BEFORE_INACTIVE) . '
						AND game_id = ' . $db->escapeNumber($this->getGameID()) . '
						AND ignore_globals = \'FALSE\'
						AND account_id != ' . $db->escapeNumber($this->getAccountID()));

		foreach ($dbResult->records() as $dbRecord) {
			$this->sendMessage($dbRecord->getInt('account_id'), MSG_GLOBAL, $message, $canBeIgnored);
		}
		$this->sendMessage($this->getAccountID(), MSG_GLOBAL, $message, $canBeIgnored, false);
	}

	/**
	 * @return ($canBeIgnored is true ? int|false : int) Message ID
	 */
	public function sendMessage(int $receiverID, int $messageTypeID, string $message, bool $canBeIgnored = true, bool $unread = true, int $expires = null, bool $senderDelete = false): int|false {
		//get expire time
		if ($canBeIgnored) {
			if ($this->getAccount()->isMailBanned()) {
				throw new UserError('You are currently banned from sending messages');
			}
			// Don't send messages to players ignoring us
			$dbResult = $this->db->read('SELECT 1 FROM message_blacklist WHERE account_id=' . $this->db->escapeNumber($receiverID) . ' AND blacklisted_id=' . $this->db->escapeNumber($this->getAccountID()) . ' LIMIT 1');
			if ($dbResult->hasRecord()) {
				return false;
			}
		}

		$message = word_filter($message);

		// If expires not specified, use default based on message type
		if ($expires === null) {
			$expires = match ($messageTypeID) {
				MSG_GLOBAL => 3600, // 1h
				MSG_PLAYER => 86400 * 31, // 1 month
				MSG_PLANET => 86400 * 7, // 1 week
				MSG_SCOUT => 86400 * 3, // 3 days
				MSG_POLITICAL => 86400 * 31, // 1 month
				MSG_ALLIANCE => 86400 * 31, // 1 month
				MSG_ADMIN => 86400 * 365, // 1 year
				MSG_CASINO => 86400 * 31, // 1 month
				default => 86400 * 7, // 1 week
			};
			$expires += Epoch::time();
		}

		// Do not put scout messages in the sender's sent box
		if ($messageTypeID == MSG_SCOUT) {
			$senderDelete = true;
		}

		// send him the message and return the message_id
		return self::doMessageSending($this->getAccountID(), $receiverID, $this->getGameID(), $messageTypeID, $message, $expires, $senderDelete, $unread);
	}

	public function sendMessageFromOpAnnounce(int $receiverID, string $message, int $expires = null): void {
		// get expire time if not set
		if ($expires === null) {
			$expires = Epoch::time() + 86400 * 14;
		}
		self::doMessageSending(ACCOUNT_ID_OP_ANNOUNCE, $receiverID, $this->getGameID(), MSG_ALLIANCE, $message, $expires);
	}

	public function sendMessageFromAllianceCommand(int $receiverID, string $message): void {
		$expires = Epoch::time() + 86400 * 365;
		self::doMessageSending(ACCOUNT_ID_ALLIANCE_COMMAND, $receiverID, $this->getGameID(), MSG_PLAYER, $message, $expires);
	}

	public static function sendMessageFromPlanet(int $gameID, int $receiverID, string $message): void {
		//get expire time
		$expires = Epoch::time() + 86400 * 31;
		// send him the message
		self::doMessageSending(ACCOUNT_ID_PLANET, $receiverID, $gameID, MSG_PLANET, $message, $expires);
	}

	public static function sendMessageFromPort(int $gameID, int $receiverID, string $message): void {
		//get expire time
		$expires = Epoch::time() + 86400 * 31;
		// send him the message
		self::doMessageSending(ACCOUNT_ID_PORT, $receiverID, $gameID, MSG_PLAYER, $message, $expires);
	}

	public static function sendMessageFromFedClerk(int $gameID, int $receiverID, string $message): void {
		$expires = Epoch::time() + 86400 * 365;
		self::doMessageSending(ACCOUNT_ID_FED_CLERK, $receiverID, $gameID, MSG_PLAYER, $message, $expires);
	}

	public static function sendMessageFromAdmin(int $gameID, int $receiverID, string $message, int $expires = null): void {
		//get expire time
		if ($expires === null) {
			$expires = Epoch::time() + 86400 * 365;
		}
		// send him the message
		self::doMessageSending(ACCOUNT_ID_ADMIN, $receiverID, $gameID, MSG_ADMIN, $message, $expires);
	}

	public static function sendMessageFromAllianceAmbassador(int $gameID, int $receiverID, string $message, int $expires = null): void {
		//get expire time
		if ($expires === null) {
			$expires = Epoch::time() + 86400 * 31;
		}
		// send him the message
		self::doMessageSending(ACCOUNT_ID_ALLIANCE_AMBASSADOR, $receiverID, $gameID, MSG_ALLIANCE, $message, $expires);
	}

	public static function sendMessageFromCasino(int $gameID, int $receiverID, string $message, int $expires = null): void {
		//get expire time
		if ($expires === null) {
			$expires = Epoch::time() + 86400 * 7;
		}
		// send him the message
		self::doMessageSending(ACCOUNT_ID_CASINO, $receiverID, $gameID, MSG_CASINO, $message, $expires);
	}

	public static function sendMessageFromRace(int $raceID, int $gameID, int $receiverID, string $message, int $expires = null): void {
		//get expire time
		if ($expires === null) {
			$expires = Epoch::time() + 86400 * 5;
		}
		// send him the message
		self::doMessageSending(ACCOUNT_ID_GROUP_RACES + $raceID, $receiverID, $gameID, MSG_POLITICAL, $message, $expires);
	}

	public function setMessagesRead(int $messageTypeID): void {
		$this->db->write('DELETE FROM player_has_unread_messages
							WHERE ' . $this->SQL . ' AND message_type_id = ' . $this->db->escapeNumber($messageTypeID));
		$this->db->write('UPDATE message SET msg_read = ' . $this->db->escapeBoolean(true) . '
				WHERE message_type_id = ' . $this->db->escapeNumber($messageTypeID) . ' AND ' . $this->SQL);
	}

	public function getSafeAttackRating(): int {
		return max(0, min(8, IFloor($this->getAlignment() / 150) + 4));
	}

	public function hasFederalProtection(): bool {
		$sector = SmrSector::getSector($this->getGameID(), $this->getSectorID());
		if (!$sector->offersFederalProtection()) {
			return false;
		}

		$ship = $this->getShip();
		if ($ship->hasIllegalGoods()) {
			return false;
		}

		if ($ship->getAttackRating() <= $this->getSafeAttackRating()) {
			foreach ($sector->getFedRaceIDs() as $fedRaceID) {
				if ($this->canBeProtectedByRace($fedRaceID)) {
					return true;
				}
			}
		}

		return false;
	}

	public function canBeProtectedByRace(int $raceID): bool {
		if (!isset($this->canFed)) {
			$this->canFed = [];
			foreach (Race::getAllIDs() as $raceID2) {
				$this->canFed[$raceID2] = $this->getRelation($raceID2) >= ALIGN_FED_PROTECTION;
			}
			$dbResult = $this->db->read('SELECT race_id, allowed FROM player_can_fed
								WHERE ' . $this->SQL . ' AND expiry > ' . $this->db->escapeNumber(Epoch::time()));
			foreach ($dbResult->records() as $dbRecord) {
				$this->canFed[$dbRecord->getInt('race_id')] = $dbRecord->getBoolean('allowed');
			}
		}
		return $this->canFed[$raceID];
	}

	/**
	 * Returns a boolean identifying if the player can currently
	 * participate in battles.
	 */
	public function canFight(): bool {
		return !($this->hasNewbieTurns() ||
		         $this->isDead() ||
		         $this->isLandedOnPlanet() ||
		         $this->hasFederalProtection());
	}

	public function setDead(bool $bool): void {
		if ($this->dead == $bool) {
			return;
		}
		$this->dead = $bool;
		$this->hasChanged = true;
	}

	public function getKills(): int {
		return $this->kills;
	}

	public function increaseKills(int $kills): void {
		if ($kills < 0) {
			throw new Exception('Trying to increase negative kills.');
		}
		$this->setKills($this->kills + $kills);
	}

	public function setKills(int $kills): void {
		if ($this->kills == $kills) {
			return;
		}
		$this->kills = $kills;
		$this->hasChanged = true;
	}

	public function getDeaths(): int {
		return $this->deaths;
	}

	public function increaseDeaths(int $deaths): void {
		if ($deaths < 0) {
			throw new Exception('Trying to increase negative deaths.');
		}
		$this->setDeaths($this->getDeaths() + $deaths);
	}

	public function setDeaths(int $deaths): void {
		if ($this->deaths == $deaths) {
			return;
		}
		$this->deaths = $deaths;
		$this->hasChanged = true;
	}

	public function getAssists(): int {
		return $this->assists;
	}

	public function increaseAssists(int $assists): void {
		if ($assists < 1) {
			throw new Exception('Must increase by a positive number.');
		}
		$this->assists += $assists;
		$this->hasChanged = true;
	}

	public function hasGoodAlignment(): bool {
		return $this->alignment >= ALIGNMENT_GOOD;
	}

	public function hasEvilAlignment(): bool {
		return $this->alignment <= ALIGNMENT_EVIL;
	}

	public function hasNeutralAlignment(): bool {
		return !$this->hasGoodAlignment() && !$this->hasEvilAlignment();
	}

	public function getAlignment(): int {
		return $this->alignment;
	}

	public function increaseAlignment(int $align): void {
		if ($align < 0) {
			throw new Exception('Trying to increase negative align.');
		}
		if ($align == 0) {
			return;
		}
		$align += $this->alignment;
		$this->setAlignment($align);
	}

	public function decreaseAlignment(int $align): void {
		if ($align < 0) {
			throw new Exception('Trying to decrease negative align.');
		}
		if ($align == 0) {
			return;
		}
		$align = $this->alignment - $align;
		$this->setAlignment($align);
	}

	public function setAlignment(int $align): void {
		if ($this->alignment == $align) {
			return;
		}
		$this->alignment = $align;
		$this->hasChanged = true;
	}

	public function getCredits(): int {
		return $this->credits;
	}

	public function getBank(): int {
		return $this->bank;
	}

	/**
	 * Increases personal bank account up to the maximum allowed credits.
	 * Returns the amount that was actually added to handle overflow.
	 */
	public function increaseBank(int $credits): int {
		if ($credits == 0) {
			return 0;
		}
		if ($credits < 0) {
			throw new Exception('Trying to increase negative credits.');
		}
		$newTotal = min($this->bank + $credits, MAX_MONEY);
		$actualAdded = $newTotal - $this->bank;
		$this->setBank($newTotal);
		return $actualAdded;
	}

	public function decreaseBank(int $credits): void {
		if ($credits == 0) {
			return;
		}
		if ($credits < 0) {
			throw new Exception('Trying to decrease negative credits.');
		}
		$newTotal = $this->bank - $credits;
		$this->setBank($newTotal);
	}

	public function setBank(int $credits): void {
		if ($this->bank == $credits) {
			return;
		}
		if ($credits < 0) {
			throw new Exception('Trying to set negative credits.');
		}
		if ($credits > MAX_MONEY) {
			throw new Exception('Trying to set more than max credits.');
		}
		$this->bank = $credits;
		$this->hasChanged = true;
	}

	public function getExperience(): int {
		return $this->experience;
	}

	/**
	 * Returns the percent progress towards the next level.
	 * This value is rounded because it is used primarily in HTML img widths.
	 */
	public function getNextLevelPercentAcquired(): int {
		if ($this->getNextLevelExperience() == $this->getThisLevelExperience()) {
			return 100;
		}
		return max(0, min(100, IRound(($this->getExperience() - $this->getThisLevelExperience()) / ($this->getNextLevelExperience() - $this->getThisLevelExperience()) * 100)));
	}

	public function getNextLevelPercentRemaining(): int {
		return 100 - $this->getNextLevelPercentAcquired();
	}

	/**
	 * @return array<string, string|int>
	 */
	public function getNextLevel(): array {
		$LEVELS = Globals::getLevelRequirements();
		if (!isset($LEVELS[$this->getLevelID() + 1])) {
			return $LEVELS[$this->getLevelID()]; //Return current level experience if on last level.
		}
		return $LEVELS[$this->getLevelID() + 1];
	}

	public function getNextLevelExperience(): int {
		return $this->getNextLevel()['Requirement'];
	}

	public function getThisLevelExperience(): int {
		$LEVELS = Globals::getLevelRequirements();
		return $LEVELS[$this->getLevelID()]['Requirement'];
	}

	public function setExperience(int $experience): void {
		if ($this->experience == $experience) {
			return;
		}
		if ($experience < MIN_EXPERIENCE) {
			$experience = MIN_EXPERIENCE;
		}
		if ($experience > MAX_EXPERIENCE) {
			$experience = MAX_EXPERIENCE;
		}
		$this->experience = $experience;
		$this->hasChanged = true;

		// Since exp has changed, invalidate the player level so that it can
		// be recomputed next time it is queried (in case it has changed).
		$this->level = null;
	}

	/**
	 * Increases onboard credits up to the maximum allowed credits.
	 * Returns the amount that was actually added to handle overflow.
	 */
	public function increaseCredits(int $credits): int {
		if ($credits == 0) {
			return 0;
		}
		if ($credits < 0) {
			throw new Exception('Trying to increase negative credits.');
		}
		$newTotal = min($this->credits + $credits, MAX_MONEY);
		$actualAdded = $newTotal - $this->credits;
		$this->setCredits($newTotal);
		return $actualAdded;
	}

	public function decreaseCredits(int $credits): void {
		if ($credits == 0) {
			return;
		}
		if ($credits < 0) {
			throw new Exception('Trying to decrease negative credits.');
		}
		$newTotal = $this->credits - $credits;
		$this->setCredits($newTotal);
	}

	public function setCredits(int $credits): void {
		if ($this->credits == $credits) {
			return;
		}
		if ($credits < 0) {
			throw new Exception('Trying to set negative credits.');
		}
		if ($credits > MAX_MONEY) {
			throw new Exception('Trying to set more than max credits.');
		}
		$this->credits = $credits;
		$this->hasChanged = true;
	}

	public function increaseExperience(int $experience): void {
		if ($experience < 0) {
			throw new Exception('Trying to increase negative experience.');
		}
		if ($experience == 0) {
			return;
		}
		$newExperience = $this->experience + $experience;
		$this->setExperience($newExperience);
		$this->increaseHOF($experience, ['Experience', 'Total', 'Gain'], HOF_PUBLIC);
	}
	public function decreaseExperience(int $experience): void {
		if ($experience < 0) {
			throw new Exception('Trying to decrease negative experience.');
		}
		if ($experience == 0) {
			return;
		}
		$newExperience = $this->experience - $experience;
		$this->setExperience($newExperience);
		$this->increaseHOF($experience, ['Experience', 'Total', 'Loss'], HOF_PUBLIC);
	}

	public function isLandedOnPlanet(): bool {
		return $this->landedOnPlanet;
	}

	public function setLandedOnPlanet(bool $bool): void {
		if ($this->landedOnPlanet == $bool) {
			return;
		}
		$this->landedOnPlanet = $bool;
		$this->hasChanged = true;
	}

	/**
	 * Returns the numerical level of the player (e.g. 1-50).
	 */
	public function getLevelID(): int {
		// The level is cached for performance reasons unless `setExperience`
		// is called and the player's experience changes.
		if (!isset($this->level)) {
			$LEVELS_REQUIREMENTS = Globals::getLevelRequirements();
			foreach ($LEVELS_REQUIREMENTS as $level_id => $require) {
				if ($this->getExperience() >= $require['Requirement']) {
					continue;
				}
				$this->level = $level_id - 1;
				return $this->level;
			}
			$this->level = max(array_keys($LEVELS_REQUIREMENTS));
		}
		return $this->level;
	}

	public function getLevelName(): string {
		$level_name = Globals::getLevelRequirements()[$this->getLevelID()]['Name'];
		if ($this->isPresident()) {
			$level_name = '<img src="images/council_president.png" title="' . Race::getName($this->getRaceID()) . ' President" height="12" width="16" />&nbsp;' . $level_name;
		}
		return $level_name;
	}

	public function getMaxLevel(): int {
		return max(array_keys(Globals::getLevelRequirements()));
	}

	public function getPlayerID(): int {
		return $this->playerID;
	}

	/**
	 * Returns the player name.
	 * Use getDisplayName or getLinkedDisplayName for HTML-safe versions.
	 */
	public function getPlayerName(): string {
		return $this->playerName;
	}

	public function setPlayerName(string $name): void {
		$this->playerName = $name;
		$this->hasChanged = true;
	}

	/**
	 * Returns the decorated player name, suitable for HTML display.
	 */
	public function getDisplayName(bool $includeAlliance = false): string {
		$name = htmlentities($this->playerName) . ' (' . $this->getPlayerID() . ')';
		$return = get_colored_text($this->getAlignment(), $name);
		if ($this->isNPC()) {
			$return .= ' <span class="npcColour">[NPC]</span>';
		}
		if ($includeAlliance) {
			$return .= ' (' . $this->getAllianceDisplayName() . ')';
		}
		return $return;
	}

	public function getBBLink(): string {
			return '[player=' . $this->getPlayerID() . ']';
	}

	public function getLinkedDisplayName(bool $includeAlliance = true): string {
		$return = '<a href="' . $this->getTraderSearchHREF() . '">' . $this->getDisplayName() . '</a>';
		if ($includeAlliance) {
			$return .= ' (' . $this->getAllianceDisplayName(true) . ')';
		}
		return $return;
	}

	/**
	 * Change a player's name, with name validation.
	 *
	 * @throws Smr\Exceptions\UserError When the new name is not permitted.
	 */
	public function changePlayerName(string $name): void {
		// Check if the player already has this name (case-sensitive)
		if ($this->getPlayerName() == $name) {
			throw new UserError('Your player already has that name!');
		}

		// Make sure the name passes some basic character requirements
		DisplayNameValidator::validate($name);

		// Check if name is in use by any other player.
		try {
			$other = self::getPlayerByPlayerName($name, $this->getGameID());
			// The player_name field has case-insensitive collation, so if we
			// find our own player, then it is because the new name has a
			// different case (since we did a case-sensitive identity check
			// above), and we allow it to be changed.
			if ($this->equals($other)) {
				throw new PlayerNotFound();
			}
			throw new UserError('That name is already being used in this game!');
		} catch (PlayerNotFound) {
			// Name is not in use, continue.
		}

		$this->setPlayerName($name);
	}

	/**
	 * Use this method when the player is changing their own name.
	 * This will flag the player as having used their free name change.
	 */
	public function changePlayerNameByPlayer(string $playerName): void {
		$this->changePlayerName($playerName);
		$this->setNameChanged(true);
	}

	public function isNameChanged(): bool {
		return $this->nameChanged;
	}

	public function setNameChanged(bool $bool): void {
		$this->nameChanged = $bool;
		$this->hasChanged = true;
	}

	public function isRaceChanged(): bool {
		return $this->raceChanged;
	}

	public function setRaceChanged(bool $raceChanged): void {
		$this->raceChanged = $raceChanged;
		$this->hasChanged = true;
	}

	public function canChangeRace(): bool {
		return !$this->isRaceChanged() && (Epoch::time() - $this->getGame()->getStartTime() < TIME_FOR_RACE_CHANGE);
	}

	public static function getColouredRaceNameOrDefault(int $otherRaceID, self $player = null, bool $linked = false): string {
		$relations = 0;
		if ($player !== null) {
			$relations = $player->getRelation($otherRaceID);
		}
		return Globals::getColouredRaceName($otherRaceID, $relations, $linked);
	}

	public function getColouredRaceName(int $otherRaceID, bool $linked = false): string {
		return self::getColouredRaceNameOrDefault($otherRaceID, $this, $linked);
	}

	public function setRaceID(int $raceID): void {
		if ($this->raceID == $raceID) {
			return;
		}
		$this->raceID = $raceID;
		$this->hasChanged = true;
	}

	public function isAllianceLeader(bool $forceUpdate = false): bool {
		return $this->getAccountID() == $this->getAlliance($forceUpdate)->getLeaderID();
	}

	public function getAlliance(bool $forceUpdate = false): SmrAlliance {
		return SmrAlliance::getAlliance($this->getAllianceID(), $this->getGameID(), $forceUpdate);
	}

	public function getAllianceID(): int {
		return $this->allianceID;
	}

	public function hasAlliance(): bool {
		return $this->getAllianceID() != 0;
	}

	protected function setAllianceID(int $ID): void {
		if ($this->allianceID == $ID) {
			return;
		}
		$this->allianceID = $ID;
		if ($this->allianceID != 0) {
			$status = $this->hasNewbieStatus() ? 'NEWBIE' : 'VETERAN';
			$this->db->write('INSERT IGNORE INTO player_joined_alliance (account_id,game_id,alliance_id,status) ' .
				'VALUES (' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ',' . $this->db->escapeString($status) . ')');
		}
		$this->hasChanged = true;
	}

	public function getAllianceBBLink(): string {
		return $this->hasAlliance() ? $this->getAlliance()->getAllianceBBLink() : $this->getAllianceDisplayName();
	}

	public function getAllianceDisplayName(bool $linked = false, bool $includeAllianceID = false): string {
		if (!$this->hasAlliance()) {
			return 'No Alliance';
		}
		return $this->getAlliance()->getAllianceDisplayName($linked, $includeAllianceID);
	}

	public function getAllianceRole(int $allianceID = null): int {
		if ($allianceID === null) {
			$allianceID = $this->getAllianceID();
		}
		if (!isset($this->allianceRoles[$allianceID])) {
			$this->allianceRoles[$allianceID] = 0;
			$dbResult = $this->db->read('SELECT role_id
						FROM player_has_alliance_role
						WHERE ' . $this->SQL . '
						AND alliance_id=' . $this->db->escapeNumber($allianceID));
			if ($dbResult->hasRecord()) {
				$this->allianceRoles[$allianceID] = $dbResult->record()->getInt('role_id');
			}
		}
		return $this->allianceRoles[$allianceID];
	}

	public function leaveAlliance(self $kickedBy = null): void {
		$alliance = $this->getAlliance();
		if ($kickedBy !== null) {
			$kickedBy->sendMessage($this->getAccountID(), MSG_PLAYER, 'You were kicked out of the alliance!', false);
			$this->actionTaken('PlayerKicked', ['Alliance' => $alliance, 'Player' => $kickedBy]);
			$kickedBy->actionTaken('KickPlayer', ['Alliance' => $alliance, 'Player' => $this]);
		} elseif ($this->isAllianceLeader()) {
			$this->actionTaken('DisbandAlliance', ['Alliance' => $alliance]);
		} else {
			$this->actionTaken('LeaveAlliance', ['Alliance' => $alliance]);
			if ($alliance->getLeaderID() != 0 && $alliance->getLeaderID() != ACCOUNT_ID_NHL) {
				$this->sendMessage($alliance->getLeaderID(), MSG_PLAYER, 'I left your alliance!', false);
			}
		}

		// Don't have a delay for switching alliance after leaving NHA, or for disbanding an alliance.
		if (!$this->isAllianceLeader() && !$alliance->isNHA()) {
			$this->setAllianceJoinable(Epoch::time() + self::TIME_FOR_ALLIANCE_SWITCH);
			$alliance->getLeader()->setAllianceJoinable(Epoch::time() + self::TIME_FOR_ALLIANCE_SWITCH); //We set the joinable time for leader here, that way a single player alliance won't cause a player to wait before switching.
		}

		$this->setAllianceID(0);
		$this->db->write('DELETE FROM player_has_alliance_role WHERE ' . $this->SQL);

		// Update the alliance cache
		unset(self::$CACHE_ALLIANCE_PLAYERS[$this->gameID][$alliance->getAllianceID()][$this->accountID]);
	}

	/**
	 * Join an alliance (used for both Leader and New Member roles)
	 */
	public function joinAlliance(int $allianceID): void {
		$this->setAllianceID($allianceID);
		$alliance = $this->getAlliance();

		if (!$this->isAllianceLeader()) {
			// Do not throw an exception if the NHL account doesn't exist.
			try {
				$this->sendMessage($alliance->getLeaderID(), MSG_PLAYER, 'I joined your alliance!', false);
			} catch (AccountNotFound $e) {
				if ($alliance->getLeaderID() != ACCOUNT_ID_NHL) {
					throw $e;
				}
			}

			$roleID = ALLIANCE_ROLE_NEW_MEMBER;
		} else {
			$roleID = ALLIANCE_ROLE_LEADER;
		}
		$this->db->insert('player_has_alliance_role', [
			'game_id' => $this->db->escapeNumber($this->getGameID()),
			'account_id' => $this->db->escapeNumber($this->getAccountID()),
			'role_id' => $this->db->escapeNumber($roleID),
			'alliance_id' => $this->db->escapeNumber($this->getAllianceID()),
		]);

		$this->actionTaken('JoinAlliance', ['Alliance' => $alliance]);
	}

	public function getAllianceJoinable(): int {
		return $this->allianceJoinable;
	}

	private function setAllianceJoinable(int $time): void {
		if ($this->allianceJoinable == $time) {
			return;
		}
		$this->allianceJoinable = $time;
		$this->hasChanged = true;
	}

	/**
	 * Invites player with $accountID to this player's alliance.
	 */
	public function sendAllianceInvitation(int $accountID, string $message, int $expires): void {
		if (!$this->hasAlliance()) {
			throw new Exception('Must be in an alliance to send alliance invitations');
		}
		// Send message to invited player
		$messageID = $this->sendMessage($accountID, MSG_PLAYER, $message, false, true, $expires, true);
		SmrInvitation::send($this->getAllianceID(), $this->getGameID(), $accountID, $this->getAccountID(), $messageID, $expires);
	}

	public function isCombatDronesKamikazeOnMines(): bool {
		return $this->combatDronesKamikazeOnMines;
	}

	public function setCombatDronesKamikazeOnMines(bool $bool): void {
		if ($this->combatDronesKamikazeOnMines == $bool) {
			return;
		}
		$this->combatDronesKamikazeOnMines = $bool;
		$this->hasChanged = true;
	}

	protected function getPersonalRelationsData(): void {
		if (!isset($this->personalRelations)) {
			//get relations
			$this->personalRelations = [];
			foreach (Race::getAllIDs() as $raceID) {
				$this->personalRelations[$raceID] = 0;
			}
			$dbResult = $this->db->read('SELECT race_id,relation FROM player_has_relation WHERE ' . $this->SQL);
			foreach ($dbResult->records() as $dbRecord) {
				$this->personalRelations[$dbRecord->getInt('race_id')] = $dbRecord->getInt('relation');
			}
		}
	}

	/**
	 * @return array<int, int>
	 */
	public function getPersonalRelations(): array {
		$this->getPersonalRelationsData();
		return $this->personalRelations;
	}

	/**
	 * Get personal relations with a race
	 */
	public function getPersonalRelation(int $raceID): int {
		$rels = $this->getPersonalRelations();
		return $rels[$raceID];
	}

	/**
	 * Get total relations with all races (personal + political)
	 *
	 * @return array<int, int>
	 */
	public function getRelations(): array {
		if (!isset($this->relations)) {
			//get relations
			$raceRelations = Globals::getRaceRelations($this->getGameID(), $this->getRaceID());
			$personalRels = $this->getPersonalRelations(); // make sure they're initialised.
			$this->relations = [];
			foreach (Race::getAllIDs() as $raceID) {
				$this->relations[$raceID] = $personalRels[$raceID] + $raceRelations[$raceID];
			}
		}
		return $this->relations;
	}

	/**
	 * Get total relations with a race (personal + political)
	 */
	public function getRelation(int $raceID): int {
		$rels = $this->getRelations();
		return $rels[$raceID];
	}

	/**
	 * Increases personal relations from trading $numGoods units with the race
	 * of the port given by $raceID.
	 */
	public function increaseRelationsByTrade(int $numGoods, int $raceID): void {
		$relations = ICeil(min($numGoods, 300) / 30);
		//Cap relations to a max of 1 after 500 have been reached
		if ($this->getPersonalRelation($raceID) + $relations >= 500) {
			$relations = max(1, min($relations, 500 - $this->getPersonalRelation($raceID)));
		}
		$this->increaseRelations($relations, $raceID);
	}

	/**
	 * Decreases personal relations from trading failures, e.g. rejected
	 * bargaining and getting caught stealing.
	 */
	public function decreaseRelationsByTrade(int $numGoods, int $raceID): void {
		$relations = ICeil(min($numGoods, 300) / 30);
		$this->decreaseRelations($relations, $raceID);
	}

	/**
	 * Increase personal relations.
	 */
	public function increaseRelations(int $relations, int $raceID): void {
		if ($relations < 0) {
			throw new Exception('Trying to increase negative relations.');
		}
		if ($relations == 0) {
			return;
		}
		$relations += $this->getPersonalRelation($raceID);
		$this->setRelations($relations, $raceID);
	}

	/**
	 * Decrease personal relations.
	 */
	public function decreaseRelations(int $relations, int $raceID): void {
		if ($relations < 0) {
			throw new Exception('Trying to decrease negative relations.');
		}
		if ($relations == 0) {
			return;
		}
		$relations = $this->getPersonalRelation($raceID) - $relations;
		$this->setRelations($relations, $raceID);
	}

	/**
	 * Set personal relations.
	 */
	public function setRelations(int $relations, int $raceID): void {
		$this->getRelations();
		if ($this->personalRelations[$raceID] == $relations) {
			return;
		}
		if ($relations < MIN_RELATIONS) {
			$relations = MIN_RELATIONS;
		}
		$relationsDiff = IRound($relations - $this->personalRelations[$raceID]);
		$this->personalRelations[$raceID] = $relations;
		$this->relations[$raceID] += $relationsDiff;
		$this->db->replace('player_has_relation', [
			'account_id' => $this->db->escapeNumber($this->getAccountID()),
			'game_id' => $this->db->escapeNumber($this->getGameID()),
			'race_id' => $this->db->escapeNumber($raceID),
			'relation' => $this->db->escapeNumber($this->personalRelations[$raceID]),
		]);
	}

	/**
	 * Set any starting personal relations bonuses or penalties.
	 */
	public function giveStartingRelations(): void {
		if ($this->getRaceID() === RACE_ALSKANT) {
			// Give Alskants bonus personal relations to start.
			foreach (Race::getAllIDs() as $raceID) {
				$this->setRelations(ALSKANT_BONUS_RELATIONS, $raceID);
			}
		}
	}

	public function getLastNewsUpdate(): int {
		return $this->lastNewsUpdate;
	}

	private function setLastNewsUpdate(int $time): void {
		if ($this->lastNewsUpdate == $time) {
			return;
		}
		$this->lastNewsUpdate = $time;
		$this->hasChanged = true;
	}

	public function updateLastNewsUpdate(): void {
		$this->setLastNewsUpdate(Epoch::time());
	}

	public function getLastPort(): int {
		return $this->lastPort;
	}

	public function setLastPort(int $lastPort): void {
		if ($this->lastPort == $lastPort) {
			return;
		}
		$this->lastPort = $lastPort;
		$this->hasChanged = true;
	}

	public function getPlottedCourse(): ?Path {
		if (!isset($this->plottedCourse)) {
			// check if we have a course plotted
			$dbResult = $this->db->read('SELECT course FROM player_plotted_course WHERE ' . $this->SQL);

			if ($dbResult->hasRecord()) {
				// get the course back
				$this->plottedCourse = $dbResult->record()->getObject('course');
			} else {
				$this->plottedCourse = null;
			}
		}

		// Update the plotted course if we have moved since the last query
		if ($this->plottedCourse !== null && $this->plottedCourse->getStartSectorID() != $this->getSectorID()) {
			if ($this->plottedCourse->getEndSectorID() == $this->getSectorID()) {
				// We have reached our destination
				$this->deletePlottedCourse();
			} elseif ($this->plottedCourse->getNextOnPath() == $this->getSectorID()) {
				// We have walked into the next sector of the course
				$this->plottedCourse->followPath();
				$this->setPlottedCourse($this->plottedCourse);
			} elseif ($this->plottedCourse->isInPath($this->getSectorID())) {
				// We have skipped to some later sector in the course
				$this->plottedCourse->skipToSector($this->getSectorID());
				$this->setPlottedCourse($this->plottedCourse);
			}
		}
		return $this->plottedCourse;
	}

	public function setPlottedCourse(Path $plottedCourse): void {
		$this->plottedCourse = $plottedCourse;
		$this->db->replace('player_plotted_course', [
			'account_id' => $this->db->escapeNumber($this->getAccountID()),
			'game_id' => $this->db->escapeNumber($this->getGameID()),
			'course' => $this->db->escapeObject($this->plottedCourse),
		]);
	}

	public function hasPlottedCourse(): bool {
		return $this->getPlottedCourse() !== null;
	}

	public function isPartOfCourse(SmrSector $sector): bool {
		return $this->getPlottedCourse()?->isInPath($sector->getSectorID()) === true;
	}

	public function deletePlottedCourse(): void {
		$this->plottedCourse = null;
		$this->db->write('DELETE FROM player_plotted_course WHERE ' . $this->SQL);
	}

	/**
	 * Computes the turn cost and max misjump between current and target sector
	 *
	 * @return array<string, int>
	 */
	public function getJumpInfo(SmrSector $targetSector): array {
		$path = Plotter::findDistanceToX($targetSector, $this->getSector(), true);
		if ($path === false) {
			throw new UserError('Unable to plot from ' . $this->getSectorID() . ' to ' . $targetSector->getSectorID() . '.');
		}
		$distance = $path->getDistance();

		$turnCost = max(TURNS_JUMP_MINIMUM, IRound($distance * TURNS_PER_JUMP_DISTANCE));
		$maxMisjump = max(0, IRound(($distance - $turnCost) * MISJUMP_DISTANCE_DIFF_FACTOR / (1 + $this->getLevelID() * MISJUMP_LEVEL_FACTOR)));
		return ['turn_cost' => $turnCost, 'max_misjump' => $maxMisjump];
	}

	public function __sleep() {
		return ['accountID', 'gameID', 'sectorID', 'alignment', 'playerID', 'playerName', 'npc'];
	}

	/**
	 * @return array<int, StoredDestination>
	 */
	public function getStoredDestinations(): array {
		if (!isset($this->storedDestinations)) {
			$this->storedDestinations = [];
			$dbResult = $this->db->read('SELECT * FROM player_stored_sector WHERE ' . $this->SQL);
			foreach ($dbResult->records() as $dbRecord) {
				$sectorID = $dbRecord->getInt('sector_id');
				$this->storedDestinations[$sectorID] = new StoredDestination(
					sectorID: $sectorID,
					label: $dbRecord->getString('label'),
					offsetTop: $dbRecord->getInt('offset_top'),
					offsetLeft: $dbRecord->getInt('offset_left'),
				);
			}
		}
		return $this->storedDestinations;
	}

	public function moveDestinationButton(int $sectorID, int $offsetTop, int $offsetLeft): void {
		$this->getStoredDestinations(); // make sure property is initialized

		if ($offsetLeft < 0 || $offsetLeft > 500 || $offsetTop < 0 || $offsetTop > 300) {
			throw new UserError('The saved sector must be in the box!');
		}

		if (!isset($this->storedDestinations[$sectorID])) {
			throw new UserError('You do not have a saved sector for #' . $sectorID);
		}

		// Replace destination with updated offsets
		$this->storedDestinations[$sectorID] = new StoredDestination(
			sectorID: $sectorID,
			label: $this->storedDestinations[$sectorID]->label,
			offsetTop: $offsetTop,
			offsetLeft: $offsetLeft,
		);
		$this->db->write('
			UPDATE player_stored_sector
				SET offset_left = ' . $this->db->escapeNumber($offsetLeft) . ', offset_top=' . $this->db->escapeNumber($offsetTop) . '
			WHERE ' . $this->SQL . ' AND sector_id = ' . $this->db->escapeNumber($sectorID));
	}

	public function addDestinationButton(int $sectorID, string $label): void {
		$this->getStoredDestinations(); // make sure property is initialized

		if (!SmrSector::sectorExists($this->getGameID(), $sectorID)) {
			throw new UserError('You want to add a non-existent sector?');
		}

		// sector already stored ?
		if (isset($this->storedDestinations[$sectorID])) {
			throw new UserError('Sector already stored!');
		}

		$this->storedDestinations[$sectorID] = new StoredDestination(
			label: $label,
			sectorID: $sectorID,
			offsetTop: 1,
			offsetLeft: 1,
		);

		$this->db->insert('player_stored_sector', [
			'account_id' => $this->db->escapeNumber($this->getAccountID()),
			'game_id' => $this->db->escapeNumber($this->getGameID()),
			'sector_id' => $this->db->escapeNumber($sectorID),
			'label' => $this->db->escapeString($label),
			'offset_top' => 1,
			'offset_left' => 1,
		]);
	}

	public function deleteDestinationButton(int $sectorID): void {
		$this->getStoredDestinations(); // make sure property is initialized

		if (!isset($this->storedDestinations[$sectorID])) {
			throw new Exception('Could not find stored destination');
		}

		$this->db->write('
			DELETE FROM player_stored_sector
			WHERE ' . $this->SQL . '
			AND sector_id = ' . $this->db->escapeNumber($sectorID));
		unset($this->storedDestinations[$sectorID]);
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function getTickers(): array {
		if (!isset($this->tickers)) {
			$this->tickers = [];
			//get ticker info
			$dbResult = $this->db->read('SELECT type,time,expires,recent FROM player_has_ticker WHERE ' . $this->SQL . ' AND expires > ' . $this->db->escapeNumber(Epoch::time()));
			foreach ($dbResult->records() as $dbRecord) {
				$this->tickers[$dbRecord->getString('type')] = [
					'Type' => $dbRecord->getString('type'),
					'Time' => $dbRecord->getInt('time'),
					'Expires' => $dbRecord->getInt('expires'),
					'Recent' => $dbRecord->getString('recent'),
				];
			}
		}
		return $this->tickers;
	}

	public function hasTickers(): bool {
		return count($this->getTickers()) > 0;
	}

	/**
	 * @return array<string, mixed>|false
	 */
	public function getTicker(string $tickerType): array|false {
		$tickers = $this->getTickers();
		if (isset($tickers[$tickerType])) {
			return $tickers[$tickerType];
		}
		return false;
	}

	public function hasTicker(string $tickerType): bool {
		return $this->getTicker($tickerType) !== false;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function shootForces(SmrForce $forces): array {
		return $this->getShip()->shootForces($forces);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function shootPort(SmrPort $port): array {
		return $this->getShip()->shootPort($port);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function shootPlanet(SmrPlanet $planet): array {
		return $this->getShip()->shootPlanet($planet);
	}

	/**
	 * @param array<AbstractSmrPlayer> $targetPlayers
	 * @return array<string, mixed>
	 */
	public function shootPlayers(array $targetPlayers): array {
		return $this->getShip()->shootPlayers($targetPlayers);
	}

	public function getMilitaryPayment(): int {
		return $this->militaryPayment;
	}

	public function hasMilitaryPayment(): bool {
		return $this->getMilitaryPayment() > 0;
	}

	public function setMilitaryPayment(int $amount): void {
		if ($this->militaryPayment == $amount) {
			return;
		}
		$this->militaryPayment = $amount;
		$this->hasChanged = true;
	}

	public function increaseMilitaryPayment(int $amount): void {
		if ($amount < 0) {
			throw new Exception('Trying to increase negative military payment.');
		}
		$this->setMilitaryPayment($this->getMilitaryPayment() + $amount);
	}

	public function decreaseMilitaryPayment(int $amount): void {
		if ($amount < 0) {
			throw new Exception('Trying to decrease negative military payment.');
		}
		$this->setMilitaryPayment($this->getMilitaryPayment() - $amount);
	}

	protected function getBountiesData(): void {
		if (!isset($this->bounties)) {
			$this->bounties = [];
			$dbResult = $this->db->read('SELECT * FROM bounty WHERE ' . $this->SQL);
			foreach ($dbResult->records() as $dbRecord) {
				$this->bounties[$dbRecord->getInt('bounty_id')] = [
							'Amount' => $dbRecord->getInt('amount'),
							'SmrCredits' => $dbRecord->getInt('smr_credits'),
							'Type' => BountyType::from($dbRecord->getString('type')),
							'Claimer' => $dbRecord->getInt('claimer_id'),
							'Time' => $dbRecord->getInt('time'),
							'ID' => $dbRecord->getInt('bounty_id'),
							'New' => false];
			}
		}
	}

	/**
	 * Get bounties that can be claimed by this player.
	 *
	 * @return array<array<string, mixed>>
	 */
	public function getClaimableBounties(?BountyType $type = null): array {
		$bounties = [];
		$query = 'SELECT * FROM bounty WHERE claimer_id=' . $this->db->escapeNumber($this->getAccountID()) . ' AND game_id=' . $this->db->escapeNumber($this->getGameID());
		$query .= match ($type) {
			null => '',
			default => ' AND type=' . $this->db->escapeString($type->value),
		};
		$dbResult = $this->db->read($query);
		foreach ($dbResult->records() as $dbRecord) {
			$bounties[] = [
				'player' => self::getPlayer($dbRecord->getInt('account_id'), $this->getGameID()),
				'bounty_id' => $dbRecord->getInt('bounty_id'),
				'credits' => $dbRecord->getInt('amount'),
				'smr_credits' => $dbRecord->getInt('smr_credits'),
			];
		}
		return $bounties;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function getBounties(): array {
		$this->getBountiesData();
		return $this->bounties;
	}

	public function hasBounties(): bool {
		return count($this->getBounties()) > 0;
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function getBounty(int $bountyID): array {
		if (!$this->hasBounty($bountyID)) {
			throw new Exception('BountyID does not exist: ' . $bountyID);
		}
		return $this->bounties[$bountyID];
	}

	public function hasBounty(int $bountyID): bool {
		$bounties = $this->getBounties();
		return isset($bounties[$bountyID]);
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function createBounty(BountyType $type): array {
		$bounty = [
			'Amount' => 0,
			'SmrCredits' => 0,
			'Type' => $type,
			'Claimer' => 0,
			'Time' => Epoch::time(),
			'ID' => $this->getNextBountyID(),
			'New' => true,
		];
		$this->setBounty($bounty);
		return $bounty;
	}

	protected function getNextBountyID(): int {
		if (!$this->hasBounties()) {
			return 0;
		}
		return max(array_keys($this->getBounties())) + 1;
	}

	/**
	 * @param array<string, mixed> $bounty
	 */
	protected function setBounty(array $bounty): void {
		$this->bounties[$bounty['ID']] = $bounty;
		$this->hasBountyChanged[$bounty['ID']] = true;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getCurrentBounty(BountyType $type): array {
		$bounties = $this->getBounties();
		foreach ($bounties as $bounty) {
			if ($bounty['Claimer'] == 0 && $bounty['Type'] == $type) {
				return $bounty;
			}
		}
		return $this->createBounty($type);
	}

	public function hasCurrentBounty(BountyType $type): bool {
		$bounties = $this->getBounties();
		foreach ($bounties as $bounty) {
			if ($bounty['Claimer'] == 0 && $bounty['Type'] == $type) {
				return true;
			}
		}
		return false;
	}

	public function getCurrentBountyAmount(BountyType $type): int {
		$bounty = $this->getCurrentBounty($type);
		return $bounty['Amount'];
	}

	protected function setCurrentBountyAmount(BountyType $type, int $amount): void {
		$bounty = $this->getCurrentBounty($type);
		if ($bounty['Amount'] == $amount) {
			return;
		}
		$bounty['Amount'] = $amount;
		$this->setBounty($bounty);
	}

	public function increaseCurrentBountyAmount(BountyType $type, int $amount): void {
		if ($amount < 0) {
			throw new Exception('Trying to increase negative current bounty.');
		}
		$this->setCurrentBountyAmount($type, $this->getCurrentBountyAmount($type) + $amount);
	}

	public function decreaseCurrentBountyAmount(BountyType $type, int $amount): void {
		if ($amount < 0) {
			throw new Exception('Trying to decrease negative current bounty.');
		}
		$this->setCurrentBountyAmount($type, $this->getCurrentBountyAmount($type) - $amount);
	}

	protected function getCurrentBountySmrCredits(BountyType $type): int {
		$bounty = $this->getCurrentBounty($type);
		return $bounty['SmrCredits'];
	}

	protected function setCurrentBountySmrCredits(BountyType $type, int $credits): void {
		$bounty = $this->getCurrentBounty($type);
		if ($bounty['SmrCredits'] == $credits) {
			return;
		}
		$bounty['SmrCredits'] = $credits;
		$this->setBounty($bounty);
	}

	public function increaseCurrentBountySmrCredits(BountyType $type, int $credits): void {
		if ($credits < 0) {
			throw new Exception('Trying to increase negative current bounty.');
		}
		$this->setCurrentBountySmrCredits($type, $this->getCurrentBountySmrCredits($type) + $credits);
	}

	public function decreaseCurrentBountySmrCredits(BountyType $type, int $credits): void {
		if ($credits < 0) {
			throw new Exception('Trying to decrease negative current bounty.');
		}
		$this->setCurrentBountySmrCredits($type, $this->getCurrentBountySmrCredits($type) - $credits);
	}

	public function setBountiesClaimable(self $claimer): void {
		foreach ($this->getBounties() as $bounty) {
			if ($bounty['Claimer'] == 0) {
				$bounty['Claimer'] = $claimer->getAccountID();
				$this->setBounty($bounty);
			}
		}
	}

	protected function getHOFData(): void {
		if (!isset($this->HOF)) {
			//Get Player HOF
			$dbResult = $this->db->read('SELECT type,amount FROM player_hof WHERE ' . $this->SQL);
			$this->HOF = [];
			foreach ($dbResult->records() as $dbRecord) {
				$this->HOF[$dbRecord->getString('type')] = $dbRecord->getFloat('amount');
			}
			self::getHOFVis();
		}
	}

	/**
	 * @return array<string, string>
	 */
	public static function getHOFVis(): array {
		if (!isset(self::$HOFVis)) {
			//Get Player HOF Vis
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT type,visibility FROM hof_visibility');
			self::$HOFVis = [];
			foreach ($dbResult->records() as $dbRecord) {
				self::$HOFVis[$dbRecord->getString('type')] = $dbRecord->getString('visibility');
			}
			// Add non-database types
			self::$HOFVis[HOF_TYPE_DONATION] = HOF_PUBLIC;
			self::$HOFVis[HOF_TYPE_USER_SCORE] = HOF_PUBLIC;
		}
		return self::$HOFVis;
	}

	/**
	 * @param array<string> $typeList
	 */
	public function getHOF(array $typeList): float {
		$this->getHOFData();
		return $this->HOF[implode(':', $typeList)] ?? 0;
	}

	/**
	 * @param array<string> $typeList
	 */
	public function increaseHOF(float $amount, array $typeList, string $visibility): void {
		if ($amount < 0) {
			throw new Exception('Trying to increase negative HOF: ' . implode(':', $typeList));
		}
		if ($amount == 0) {
			return;
		}
		$this->setHOF($this->getHOF($typeList) + $amount, $typeList, $visibility);
	}

	/**
	 * @param array<string> $typeList
	 */
	public function decreaseHOF(float $amount, array $typeList, string $visibility): void {
		if ($amount < 0) {
			throw new Exception('Trying to decrease negative HOF: ' . implode(':', $typeList));
		}
		if ($amount == 0) {
			return;
		}
		$this->setHOF($this->getHOF($typeList) - $amount, $typeList, $visibility);
	}

	/**
	 * @param array<string> $typeList
	 */
	public function setHOF(float $amount, array $typeList, string $visibility): void {
		if ($this->isNPC()) {
			// Don't store HOF for NPCs.
			return;
		}
		if ($amount < 0) {
			$amount = 0;
		}
		if ($this->getHOF($typeList) == $amount) {
			return;
		}

		$hofType = implode(':', $typeList);
		if (!isset(self::$HOFVis[$hofType])) {
			self::$hasHOFVisChanged[$hofType] = self::HOF_NEW;
		} elseif (self::$HOFVis[$hofType] != $visibility) {
			self::$hasHOFVisChanged[$hofType] = self::HOF_CHANGED;
		}
		self::$HOFVis[$hofType] = $visibility;

		if (!isset($this->HOF[$hofType])) {
			$this->hasHOFChanged[$hofType] = self::HOF_NEW;
		} else {
			$this->hasHOFChanged[$hofType] = self::HOF_CHANGED;
		}
		$this->HOF[$hofType] = $amount;
	}

	public function isUnderAttack(): bool {
		return $this->underAttack;
	}

	public function setUnderAttack(bool $value): void {
		if ($this->underAttack === $value) {
			return;
		}
		$this->underAttack = $value;
		$this->hasChanged = true;
	}

	public function killPlayer(int $sectorID): void {
		$sector = SmrSector::getSector($this->getGameID(), $sectorID);
		//msg taken care of in trader_att_proc.php
		// forget plotted course
		$this->deletePlottedCourse();

		$sector->diedHere($this);

		// if we are in an alliance we increase their deaths
		if ($this->hasAlliance()) {
			$this->db->write('UPDATE alliance SET alliance_deaths = alliance_deaths + 1
							WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()) . ' AND alliance_id = ' . $this->db->escapeNumber($this->getAllianceID()));
		}

		// record death stat
		$this->increaseHOF(1, ['Dying', 'Deaths'], HOF_PUBLIC);
		//record cost of ship lost
		$this->increaseHOF($this->getShip()->getCost(), ['Dying', 'Money', 'Cost Of Ships Lost'], HOF_PUBLIC);
		// reset turns since last death
		$this->setHOF(0, ['Movement', 'Turns Used', 'Since Last Death'], HOF_ALLIANCE);

		// Reset credits to starting amount + ship insurance
		$credits = $this->getGame()->getStartingCredits();
		$credits += IRound($this->getShip()->getCost() * self::SHIP_INSURANCE_FRACTION);
		$this->setCredits($credits);

		$this->setSectorID($this->getHome());
		$this->increaseDeaths(1);
		$this->setLandedOnPlanet(false);
		$this->setDead(true);
		$this->setNewbieWarning(true);
		$this->getShip()->getPod($this->hasNewbieStatus());
		$this->setNewbieTurns(NEWBIE_TURNS_ON_DEATH);
		$this->setUnderAttack(false);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function killPlayerByPlayer(self $killer): array {
		$return = [];
		$msg = $this->getBBLink();

		if ($this->hasCustomShipName()) {
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$msg .= ' flying <span class="yellow">' . $named_ship . '</span>';
		}
		$msg .= ' was destroyed by ' . $killer->getBBLink();
		if ($killer->hasCustomShipName()) {
			$named_ship = strip_tags($killer->getCustomShipName(), '<font><span><img>');
			$msg .= ' flying <span class="yellow">' . $named_ship . '</span>';
		}
		$msg .= ' in Sector&nbsp;' . Globals::getSectorBBLink($this->getSectorID());
		$this->getSector()->increaseBattles(1);
		$this->db->insert('news', [
			'game_id' => $this->db->escapeNumber($this->getGameID()),
			'time' => $this->db->escapeNumber(Epoch::time()),
			'news_message' => $this->db->escapeString($msg),
			'killer_id' => $this->db->escapeNumber($killer->getAccountID()),
			'killer_alliance' => $this->db->escapeNumber($killer->getAllianceID()),
			'dead_id' => $this->db->escapeNumber($this->getAccountID()),
			'dead_alliance' => $this->db->escapeNumber($this->getAllianceID()),
		]);

		self::sendMessageFromFedClerk($this->getGameID(), $this->getAccountID(), 'You were <span class="red">DESTROYED</span> by ' . $killer->getBBLink() . ' in sector ' . Globals::getSectorBBLink($this->getSectorID()));
		self::sendMessageFromFedClerk($this->getGameID(), $killer->getAccountID(), 'You <span class="red">DESTROYED</span>&nbsp;' . $this->getBBLink() . ' in sector ' . Globals::getSectorBBLink($this->getSectorID()));

		// Dead player loses between 5% and 25% experience
		$expLossPercentage = 0.15 + 0.10 * ($this->getLevelID() - $killer->getLevelID()) / $this->getMaxLevel();
		$return['DeadExp'] = max(0, IFloor($this->getExperience() * $expLossPercentage));
		$expBeforeDeath = $this->getExperience();
		$this->decreaseExperience($return['DeadExp']);

		// Killer gains 50% of the lost exp
		$return['KillerExp'] = max(0, ICeil(0.5 * $return['DeadExp']));
		$killer->increaseExperience($return['KillerExp']);

		$return['KillerCredits'] = $this->getCredits();
		$killer->increaseCredits($return['KillerCredits']);

		// The killer may change alignment
		$relations = Globals::getRaceRelations($this->getGameID(), $this->getRaceID());
		$relation = $relations[$killer->getRaceID()];

		$alignChangePerRelation = 0.1;
		if ($relation >= RELATIONS_PEACE || $relation <= RELATIONS_WAR) {
			$alignChangePerRelation = 0.04;
		}

		$killerAlignChange = IRound(-$relation * $alignChangePerRelation); //Lose relations when killing a peaceful race
		if ($killerAlignChange > 0) {
			$killer->increaseAlignment($killerAlignChange);
		} else {
			$killer->decreaseAlignment(-$killerAlignChange);
		}
		// War setting gives them military pay
		if ($relation <= RELATIONS_WAR) {
			$killer->increaseMilitaryPayment(-IFloor($relation * 100 * pow($return['KillerExp'] / 2, 0.25)));
		}

		//check for federal bounty being offered for current port raiders;
		$this->db->write('DELETE FROM player_attacks_port WHERE time < ' . $this->db->escapeNumber(Epoch::time() - self::TIME_FOR_FEDERAL_BOUNTY_ON_PR));
		$query = 'SELECT 1
					FROM player_attacks_port
					JOIN port USING(game_id, sector_id)
					JOIN player USING(game_id, account_id)
					WHERE armour > 0 AND ' . $this->SQL . ' LIMIT 1';
		$dbResult = $this->db->read($query);
		if ($dbResult->hasRecord()) {
			$bounty = IFloor(DEFEND_PORT_BOUNTY_PER_LEVEL * $this->getLevelID());
			$this->increaseCurrentBountyAmount(BountyType::HQ, $bounty);
		}

		// Killer get marked as claimer of podded player's bounties even if they don't exist
		$this->setBountiesClaimable($killer);

		// If the alignment difference is greater than 200 then a bounty may be set
		$alignmentDiff = abs($this->getAlignment() - $killer->getAlignment());
		$return['BountyGained'] = [
			'Type' => 'None',
			'Amount' => 0,
		];
		if ($alignmentDiff >= 200) {
			// If the podded players alignment makes them deputy or member then set bounty
			if ($this->hasGoodAlignment()) {
				$return['BountyGained']['Type'] = BountyType::HQ;
			} elseif ($this->hasEvilAlignment()) {
				$return['BountyGained']['Type'] = BountyType::UG;
			}

			if ($return['BountyGained']['Type'] != 'None') {
				$return['BountyGained']['Amount'] = IFloor(pow($alignmentDiff, 2.56));
				$killer->increaseCurrentBountyAmount($return['BountyGained']['Type'], $return['BountyGained']['Amount']);
			}
		}

		$killingHof = ['Killing'];
		if ($this->isNPC()) {
			$killingHof[] = 'NPC';
		}
		$killer->increaseHOF($return['KillerExp'], [...$killingHof, 'Experience', 'Gained'], HOF_PUBLIC);
		$killer->increaseHOF($expBeforeDeath, [...$killingHof, 'Experience', 'Of Traders Killed'], HOF_PUBLIC);
		$killer->increaseHOF($return['DeadExp'], [...$killingHof, 'Experience', 'Lost By Traders Killed'], HOF_PUBLIC);

		$killer->increaseHOF($return['KillerCredits'], [...$killingHof, 'Money', 'Lost By Traders Killed'], HOF_PUBLIC);
		$killer->increaseHOF($return['KillerCredits'], [...$killingHof, 'Money', 'Gain'], HOF_PUBLIC);
		$killer->increaseHOF($this->getShip()->getCost(), [...$killingHof, 'Money', 'Cost Of Ships Killed'], HOF_PUBLIC);
		$killer->increaseHOF($return['BountyGained']['Amount'], [...$killingHof, 'Money', 'Bounty Gained'], HOF_PUBLIC);

		if ($killerAlignChange > 0) {
			$killer->increaseHOF($killerAlignChange, [...$killingHof, 'Alignment', 'Gain'], HOF_PUBLIC);
		} else {
			$killer->increaseHOF(-$killerAlignChange, [...$killingHof, 'Alignment', 'Loss'], HOF_PUBLIC);
		}

		if ($this->isNPC()) {
			$killer->increaseHOF(1, ['Killing', 'NPC Kills'], HOF_PUBLIC);
		} elseif ($this->getShip()->getAttackRatingWithMaxCDs() <= MAX_ATTACK_RATING_NEWBIE && $this->hasNewbieStatus() && !$killer->hasNewbieStatus()) { //Newbie kill
			$killer->increaseHOF(1, ['Killing', 'Newbie Kills'], HOF_PUBLIC);
		} else {
			$killer->increaseKills(1);
			$killer->increaseHOF(1, ['Killing', 'Kills'], HOF_PUBLIC);

			if ($killer->hasAlliance()) {
				$this->db->write('UPDATE alliance SET alliance_kills=alliance_kills+1 WHERE alliance_id=' . $this->db->escapeNumber($killer->getAllianceID()) . ' AND game_id=' . $this->db->escapeNumber($killer->getGameID()));
			}

			// alliance vs. alliance stats
			$this->incrementAllianceVsDeaths($killer->getAllianceID());
		}

		$dyingHof = ['Dying', 'Players'];
		if ($killer->isNPC()) {
			$dyingHof[] = 'NPC';
		}
		$this->increaseHOF($return['BountyGained']['Amount'], [...$dyingHof, 'Money', 'Bounty Gained By Killer'], HOF_PUBLIC);
		$this->increaseHOF($return['KillerExp'], [...$dyingHof, 'Experience', 'Gained By Killer'], HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'], [...$dyingHof, 'Experience', 'Lost'], HOF_PUBLIC);
		$this->increaseHOF($return['KillerCredits'], [...$dyingHof, 'Money Lost'], HOF_PUBLIC);
		$this->increaseHOF($this->getShip()->getCost(), [...$dyingHof, 'Money', 'Cost Of Ships Lost'], HOF_PUBLIC);
		$this->increaseHOF(1, [...$dyingHof, 'Deaths'], HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'], ['Dying', 'Experience', 'Lost'], HOF_PUBLIC);

		$this->killPlayer($this->getSectorID());
		return $return;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function killPlayerByForces(SmrForce $forces): array {
		$return = [];
		$owner = $forces->getOwner();
		// send a message to the person who died
		self::sendMessageFromFedClerk($this->getGameID(), $owner->getAccountID(), 'Your forces <span class="red">DESTROYED </span>' . $this->getBBLink() . ' in sector ' . Globals::getSectorBBLink($forces->getSectorID()));
		self::sendMessageFromFedClerk($this->getGameID(), $this->getAccountID(), 'You were <span class="red">DESTROYED</span> by ' . $owner->getBBLink() . '\'s forces in sector ' . Globals::getSectorBBLink($this->getSectorID()));

		$news_message = $this->getBBLink();
		if ($this->hasCustomShipName()) {
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$news_message .= ' flying <span class="yellow">' . $named_ship . '</span>';
		}
		$news_message .= ' was destroyed by ' . $owner->getBBLink() . '\'s forces in sector ' . Globals::getSectorBBLink($forces->getSectorID());
		// insert the news entry
		$this->db->insert('news', [
			'game_id' => $this->db->escapeNumber($this->getGameID()),
			'time' => $this->db->escapeNumber(Epoch::time()),
			'news_message' => $this->db->escapeString($news_message),
			'killer_id' => $this->db->escapeNumber($owner->getAccountID()),
			'killer_alliance' => $this->db->escapeNumber($owner->getAllianceID()),
			'dead_id' => $this->db->escapeNumber($this->getAccountID()),
			'dead_alliance' => $this->db->escapeNumber($this->getAllianceID()),
		]);

		// Player loses 15% experience
		$expLossPercentage = .15;
		$return['DeadExp'] = IFloor($this->getExperience() * $expLossPercentage);
		$this->decreaseExperience($return['DeadExp']);

		$return['LostCredits'] = $this->getCredits();

		// alliance vs. alliance stats
		$this->incrementAllianceVsDeaths(ALLIANCE_VS_FORCES);
		$owner->incrementAllianceVsKills(ALLIANCE_VS_FORCES);

		$this->increaseHOF($return['DeadExp'], ['Dying', 'Experience', 'Lost'], HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'], ['Dying', 'Forces', 'Experience Lost'], HOF_PUBLIC);
		$this->increaseHOF($return['LostCredits'], ['Dying', 'Forces', 'Money Lost'], HOF_PUBLIC);
		$this->increaseHOF($this->getShip()->getCost(), ['Dying', 'Forces', 'Cost Of Ships Lost'], HOF_PUBLIC);
		$this->increaseHOF(1, ['Dying', 'Forces', 'Deaths'], HOF_PUBLIC);

		$this->killPlayer($forces->getSectorID());
		return $return;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function killPlayerByPort(AbstractSmrPort $port): array {
		$return = [];
		// send a message to the person who died
		self::sendMessageFromFedClerk($this->getGameID(), $this->getAccountID(), 'You were <span class="red">DESTROYED</span> by the defenses of ' . $port->getDisplayName());

		$news_message = $this->getBBLink();
		if ($this->hasCustomShipName()) {
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$news_message .= ' flying <span class="yellow">' . $named_ship . '</span>';
		}
		$news_message .= ' was destroyed while invading ' . $port->getDisplayName() . '.';
		// insert the news entry
		$this->db->insert('news', [
			'game_id' => $this->db->escapeNumber($this->getGameID()),
			'time' => $this->db->escapeNumber(Epoch::time()),
			'news_message' => $this->db->escapeString($news_message),
			'killer_id' => $this->db->escapeNumber(ACCOUNT_ID_PORT),
			'dead_id' => $this->db->escapeNumber($this->getAccountID()),
			'dead_alliance' => $this->db->escapeNumber($this->getAllianceID()),
		]);

		// Player loses between 15% and 20% experience
		$expLossPercentage = .20 - .05 * ($port->getLevel() - 1) / ($port->getMaxLevel() - 1);
		$return['DeadExp'] = max(0, IFloor($this->getExperience() * $expLossPercentage));
		$this->decreaseExperience($return['DeadExp']);

		$return['LostCredits'] = $this->getCredits();

		// alliance vs. alliance stats
		$this->incrementAllianceVsDeaths(ALLIANCE_VS_PORTS);

		$this->increaseHOF($return['DeadExp'], ['Dying', 'Experience', 'Lost'], HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'], ['Dying', 'Ports', 'Experience Lost'], HOF_PUBLIC);
		$this->increaseHOF($return['LostCredits'], ['Dying', 'Ports', 'Money Lost'], HOF_PUBLIC);
		$this->increaseHOF($this->getShip()->getCost(), ['Dying', 'Ports', 'Cost Of Ships Lost'], HOF_PUBLIC);
		$this->increaseHOF(1, ['Dying', 'Ports', 'Deaths'], HOF_PUBLIC);

		$this->killPlayer($port->getSectorID());
		return $return;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function killPlayerByPlanet(SmrPlanet $planet): array {
		$return = [];
		// send a message to the person who died
		$planetOwner = $planet->getOwner();
		self::sendMessageFromFedClerk($this->getGameID(), $planetOwner->getAccountID(), 'Your planet <span class="red">DESTROYED</span>&nbsp;' . $this->getBBLink() . ' in sector ' . Globals::getSectorBBLink($planet->getSectorID()));
		self::sendMessageFromFedClerk($this->getGameID(), $this->getAccountID(), 'You were <span class="red">DESTROYED</span> by the planetary defenses of ' . $planet->getCombatName());

		$news_message = $this->getBBLink();
		if ($this->hasCustomShipName()) {
			$named_ship = strip_tags($this->getCustomShipName(), '<font><span><img>');
			$news_message .= ' flying <span class="yellow">' . $named_ship . '</span>';
		}
		$news_message .= ' was destroyed by ' . $planet->getCombatName() . '\'s planetary defenses in sector ' . Globals::getSectorBBLink($planet->getSectorID()) . '.';
		// insert the news entry
		$this->db->insert('news', [
			'game_id' => $this->db->escapeNumber($this->getGameID()),
			'time' => $this->db->escapeNumber(Epoch::time()),
			'news_message' => $this->db->escapeString($news_message),
			'killer_id' => $this->db->escapeNumber($planetOwner->getAccountID()),
			'killer_alliance' => $this->db->escapeNumber($planetOwner->getAllianceID()),
			'dead_id' => $this->db->escapeNumber($this->getAccountID()),
			'dead_alliance' => $this->db->escapeNumber($this->getAllianceID()),
		]);

		// Player loses between 15% and 20% experience
		$expLossPercentage = .20 - .05 * $planet->getLevel() / $planet->getMaxLevel();
		$return['DeadExp'] = max(0, IFloor($this->getExperience() * $expLossPercentage));
		$this->decreaseExperience($return['DeadExp']);

		$return['LostCredits'] = $this->getCredits();

		// alliance vs. alliance stats
		$this->incrementAllianceVsDeaths(ALLIANCE_VS_PLANETS);
		$planetOwner->incrementAllianceVsKills(ALLIANCE_VS_PLANETS);

		$this->increaseHOF($return['DeadExp'], ['Dying', 'Experience', 'Lost'], HOF_PUBLIC);
		$this->increaseHOF($return['DeadExp'], ['Dying', 'Planets', 'Experience Lost'], HOF_PUBLIC);
		$this->increaseHOF($return['LostCredits'], ['Dying', 'Planets', 'Money Lost'], HOF_PUBLIC);
		$this->increaseHOF($this->getShip()->getCost(), ['Dying', 'Planets', 'Cost Of Ships Lost'], HOF_PUBLIC);
		$this->increaseHOF(1, ['Dying', 'Planets', 'Deaths'], HOF_PUBLIC);

		$this->killPlayer($planet->getSectorID());
		return $return;
	}

	public function incrementAllianceVsKills(int $otherID): void {
		$values = [$this->getGameID(), $this->getAllianceID(), $otherID, 1];
		$this->db->write('INSERT INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) VALUES (' . $this->db->escapeArray($values) . ') ON DUPLICATE KEY UPDATE kills = kills + 1');
	}

	public function incrementAllianceVsDeaths(int $otherID): void {
		$values = [$this->getGameID(), $otherID, $this->getAllianceID(), 1];
		$this->db->write('INSERT INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) VALUES (' . $this->db->escapeArray($values) . ') ON DUPLICATE KEY UPDATE kills = kills + 1');
	}

	public function getTurnsLevel(): TurnsLevel {
		return match (true) {
			$this->getTurns() === 0 => TurnsLevel::None,
			$this->getTurns() <= 25 => TurnsLevel::Low,
			$this->getTurns() <= 75 => TurnsLevel::Medium,
			default => TurnsLevel::High,
		};
	}

	public function getTurns(): int {
		return $this->turns;
	}

	public function hasTurns(): bool {
		return $this->turns > 0;
	}

	public function getMaxTurns(): int {
		return $this->getGame()->getMaxTurns();
	}

	public function setTurns(int $turns): void {
		if ($this->turns == $turns) {
			return;
		}
		// Make sure turns are in range [0, MaxTurns]
		$this->turns = max(0, min($turns, $this->getMaxTurns()));
		$this->hasChanged = true;
	}

	public function takeTurns(int $take, int $takeNewbie = 0): void {
		if ($take < 0 || $takeNewbie < 0) {
			throw new Exception('Trying to take negative turns.');
		}
		$take = ICeil($take);
		// Only take up to as many newbie turns as we have remaining
		$takeNewbie = min($this->getNewbieTurns(), $takeNewbie);

		$this->setTurns($this->getTurns() - $take);
		$this->setNewbieTurns($this->getNewbieTurns() - $takeNewbie);
		$this->increaseHOF($take, ['Movement', 'Turns Used', 'Since Last Death'], HOF_ALLIANCE);
		$this->increaseHOF($take, ['Movement', 'Turns Used', 'Total'], HOF_ALLIANCE);
		$this->increaseHOF($takeNewbie, ['Movement', 'Turns Used', 'Newbie'], HOF_ALLIANCE);

		// Player has taken an action
		$this->setLastActive(Epoch::time());
		$this->updateLastCPLAction();
	}

	public function giveTurns(int $give, int $giveNewbie = 0): void {
		if ($give < 0 || $giveNewbie < 0) {
			throw new Exception('Trying to give negative turns.');
		}
		$this->setTurns($this->getTurns() + $give);
		$this->setNewbieTurns($this->getNewbieTurns() + $giveNewbie);
	}

	/**
	 * Calculate the time in seconds between the given time and when the
	 * player will be at max turns.
	 */
	public function getTimeUntilMaxTurns(int $time, bool $forceUpdate = false): int {
		$timeDiff = $time - $this->getLastTurnUpdate();
		$turnsDiff = $this->getMaxTurns() - $this->getTurns();
		$ship = $this->getShip($forceUpdate);
		$maxTurnsTime = ICeil(($turnsDiff * 3600 / $ship->getRealSpeed())) - $timeDiff;
		// If already at max turns, return 0
		return max(0, $maxTurnsTime);
	}

	/**
	 * Calculate the time in seconds until the next turn is awarded.
	 */
	public function getTimeUntilNextTurn(): int {
		$secondsSinceUpdate = Epoch::time() - $this->getLastTurnUpdate();
		$secondsPerTurn = 3600 / $this->getShip()->getRealSpeed();
		return ICeil(fmod(abs($secondsSinceUpdate - $secondsPerTurn), $secondsPerTurn));
	}

	/**
	 * Grant the player their starting turns.
	 */
	public function giveStartingTurns(): void {
		$startTurns = IFloor($this->getShip()->getRealSpeed() * $this->getGame()->getStartTurnHours());
		$this->giveTurns($startTurns);
		$this->setLastTurnUpdate($this->getGame()->getStartTime());
	}

	// Turns only update when player is active.
	// Calculate turns gained between given time and the last turn update
	public function getTurnsGained(int $time, bool $forceUpdate = false): int {
		$timeDiff = $time - $this->getLastTurnUpdate();
		$ship = $this->getShip($forceUpdate);
		$extraTurns = IFloor($timeDiff * $ship->getRealSpeed() / 3600);
		return $extraTurns;
	}

	public function updateTurns(): void {
		// is account validated?
		if (!$this->getAccount()->isValidated()) {
			return;
		}

		// how many turns would he get right now?
		$extraTurns = $this->getTurnsGained(Epoch::time());

		// do we have at least one turn to give?
		if ($extraTurns > 0) {
			// recalc the time to avoid rounding errors
			$newLastTurnUpdate = $this->getLastTurnUpdate() + ICeil($extraTurns * 3600 / $this->getShip()->getRealSpeed());
			$this->setLastTurnUpdate($newLastTurnUpdate);
			$this->giveTurns($extraTurns);
		}
	}

	public function getLastTurnUpdate(): int {
		return $this->lastTurnUpdate;
	}

	public function setLastTurnUpdate(int $time): void {
		if ($this->lastTurnUpdate == $time) {
			return;
		}
		$this->lastTurnUpdate = $time;
		$this->hasChanged = true;
	}

	public function getLastActive(): int {
		return $this->lastActive;
	}

	public function setLastActive(int $lastActive): void {
		if ($this->lastActive == $lastActive) {
			return;
		}
		$this->lastActive = $lastActive;
		$this->hasChanged = true;
	}

	public function getLastCPLAction(): int {
		return $this->lastCPLAction;
	}

	public function setLastCPLAction(int $time): void {
		if ($this->lastCPLAction == $time) {
			return;
		}
		$this->lastCPLAction = $time;
		$this->hasChanged = true;
	}

	public function updateLastCPLAction(): void {
		$this->setLastCPLAction(Epoch::time());
	}

	public function setNewbieWarning(bool $bool): void {
		if ($this->newbieWarning == $bool) {
			return;
		}
		$this->newbieWarning = $bool;
		$this->hasChanged = true;
	}

	public function getNewbieWarning(): bool {
		return $this->newbieWarning;
	}

	public function isDisplayMissions(): bool {
		return $this->displayMissions;
	}

	public function setDisplayMissions(bool $bool): void {
		if ($this->displayMissions == $bool) {
			return;
		}
		$this->displayMissions = $bool;
		$this->hasChanged = true;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function getMissions(): array {
		if (!isset($this->missions)) {
			$dbResult = $this->db->read('SELECT * FROM player_has_mission WHERE ' . $this->SQL);
			$this->missions = [];
			foreach ($dbResult->records() as $dbRecord) {
				$missionID = $dbRecord->getInt('mission_id');
				$this->missions[$missionID] = [
					'On Step' => $dbRecord->getInt('on_step'),
					'Progress' => $dbRecord->getInt('progress'),
					'Unread' => $dbRecord->getBoolean('unread'),
					'Expires' => $dbRecord->getInt('step_fails'),
					'Sector' => $dbRecord->getInt('mission_sector'),
					'Starting Sector' => $dbRecord->getInt('starting_sector'),
				];
				$this->rebuildMission($missionID);
			}
		}
		return $this->missions;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function getActiveMissions(): array {
		$missions = $this->getMissions();
		foreach ($missions as $missionID => $mission) {
			if ($mission['On Step'] >= count(MISSIONS[$missionID]['Steps'])) {
				unset($missions[$missionID]);
			}
		}
		return $missions;
	}

	/**
	 * @return array<string, mixed>|false
	 */
	protected function getMission(int $missionID): array|false {
		$missions = $this->getMissions();
		if (isset($missions[$missionID])) {
			return $missions[$missionID];
		}
		return false;
	}

	protected function hasMission(int $missionID): bool {
		return $this->getMission($missionID) !== false;
	}

	protected function updateMission(int $missionID): bool {
		$this->getMissions();
		if (isset($this->missions[$missionID])) {
			$mission = $this->missions[$missionID];
			$this->db->write('
				UPDATE player_has_mission
				SET on_step = ' . $this->db->escapeNumber($mission['On Step']) . ',
					progress = ' . $this->db->escapeNumber($mission['Progress']) . ',
					unread = ' . $this->db->escapeBoolean($mission['Unread']) . ',
					starting_sector = ' . $this->db->escapeNumber($mission['Starting Sector']) . ',
					mission_sector = ' . $this->db->escapeNumber($mission['Sector']) . ',
					step_fails = ' . $this->db->escapeNumber($mission['Expires']) . '
				WHERE ' . $this->SQL . ' AND mission_id = ' . $this->db->escapeNumber($missionID));
			return true;
		}
		return false;
	}

	private function setupMissionStep(int $missionID): void {
		$stepID = $this->missions[$missionID]['On Step'];
		if ($stepID >= count(MISSIONS[$missionID]['Steps'])) {
			// Nothing to do if this mission is already completed
			return;
		}
		$step = MISSIONS[$missionID]['Steps'][$stepID];
		if (isset($step['PickSector'])) {
			$realX = Plotter::getX($step['PickSector']['Type'], $step['PickSector']['X'], $this->getGameID());
			$path = Plotter::findDistanceToX($realX, $this->getSector(), true, null, $this);
			if ($path === false) {
				// Abandon the mission if it cannot be completed due to a
				// sector that does not exist or cannot be reached.
				// (Probably shouldn't bestow this mission in the first place)
				$this->deleteMission($missionID);
				throw new UserError('Cannot find a path to the destination!');
			}
			$this->missions[$missionID]['Sector'] = $path->getEndSectorID();
		}
	}

	/**
	 * Declining a mission will permanently hide it from the player
	 * by adding it in its completed state.
	 */
	public function declineMission(int $missionID): void {
		$finishedStep = count(MISSIONS[$missionID]['Steps']);
		$this->addMission($missionID, $finishedStep);
	}

	public function addMission(int $missionID, int $step = 0): void {
		if ($this->hasMission($missionID)) {
			throw new Exception('Mission ID already added: ' . $missionID);
		}

		$mission = [
			'On Step' => $step,
			'Progress' => 0,
			'Unread' => true,
			'Expires' => (Epoch::time() + 86400),
			'Sector' => 0,
			'Starting Sector' => $this->getSectorID(),
		];

		$this->missions[$missionID] =& $mission;
		$this->setupMissionStep($missionID);
		$this->rebuildMission($missionID);

		$this->db->replace('player_has_mission', [
			'game_id' => $this->db->escapeNumber($this->gameID),
			'account_id' => $this->db->escapeNumber($this->accountID),
			'mission_id' => $this->db->escapeNumber($missionID),
			'on_step' => $this->db->escapeNumber($mission['On Step']),
			'progress' => $this->db->escapeNumber($mission['Progress']),
			'unread' => $this->db->escapeBoolean($mission['Unread']),
			'starting_sector' => $this->db->escapeNumber($mission['Starting Sector']),
			'mission_sector' => $this->db->escapeNumber($mission['Sector']),
			'step_fails' => $this->db->escapeNumber($mission['Expires']),
		]);
	}

	private function rebuildMission(int $missionID): void {
		$mission = $this->missions[$missionID];
		$this->missions[$missionID]['Name'] = MISSIONS[$missionID]['Name'];

		if ($mission['On Step'] >= count(MISSIONS[$missionID]['Steps'])) {
			// If we have completed this mission just use false to indicate no current task.
			$currentStep = false;
		} else {
			$data = ['player' => $this, 'mission' => $mission];
			$currentStep = MISSIONS[$missionID]['Steps'][$mission['On Step']];
			array_walk_recursive($currentStep, 'replaceMissionTemplate', $data);
		}
		$this->missions[$missionID]['Task'] = $currentStep;
	}

	public function deleteMission(int $missionID): void {
		$this->getMissions();
		if (isset($this->missions[$missionID])) {
			unset($this->missions[$missionID]);
			$this->db->write('DELETE FROM player_has_mission WHERE ' . $this->SQL . ' AND mission_id = ' . $this->db->escapeNumber($missionID));
			return;
		}
		throw new Exception('Mission with ID not found: ' . $missionID);
	}

	/**
	 * @return array<int>
	 */
	public function markMissionsRead(): array {
		$this->getMissions();
		$unreadMissions = [];
		foreach ($this->missions as $missionID => &$mission) {
			if ($mission['Unread']) {
				$unreadMissions[] = $missionID;
				$mission['Unread'] = false;
				$this->updateMission($missionID);
			}
		}
		return $unreadMissions;
	}

	public function claimMissionReward(int $missionID): string {
		if (!$this->hasMission($missionID)) {
			throw new Exception('Unknown mission: ' . $missionID);
		}
		$mission =& $this->missions[$missionID];
		if ($mission['Task'] === false || $mission['Task']['Step'] != 'Claim') {
			throw new Exception('Cannot claim mission: ' . $missionID . ', for step: ' . $mission['On Step']);
		}
		$mission['On Step']++;
		$mission['Unread'] = true;
		foreach ($mission['Task']['Rewards'] as $rewardItem => $amount) {
			switch ($rewardItem) {
				case 'Credits':
					$this->increaseCredits($amount);
					break;
				case 'Experience':
					$this->increaseExperience($amount);
					break;
			}
		}
		$rewardText = $mission['Task']['Rewards']['Text'];
		if ($mission['On Step'] < count(MISSIONS[$missionID]['Steps'])) {
			// If we haven't finished the mission yet then
			$this->setupMissionStep($missionID);
		}
		$this->rebuildMission($missionID);
		$this->updateMission($missionID);
		return $rewardText;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function getAvailableMissions(): array {
		$availableMissions = [];
		foreach (MISSIONS as $missionID => $mission) {
			if ($this->hasMission($missionID)) {
				continue;
			}
			$realX = Plotter::getX($mission['HasX']['Type'], $mission['HasX']['X'], $this->getGameID());
			if ($this->getSector()->hasX($realX)) {
				$availableMissions[$missionID] = $mission;
			}
		}
		return $availableMissions;
	}

	/**
	 * Log a player action in the current sector to the admin log console.
	 */
	public function log(int $log_type_id, string $msg): void {
		$this->getAccount()->log($log_type_id, $msg, $this->getSectorID());
	}

	/**
	 * @param array<string, mixed> $values
	 */
	public function actionTaken(string $actionID, array $values): void {
		if (!in_array($actionID, MISSION_ACTIONS)) {
			throw new Exception('Unknown action: ' . $actionID);
		}
		// TODO: Reenable this once tested.     if($this->getAccount()->isLoggingEnabled())
		switch ($actionID) {
			case 'WalkSector':
				$this->log(LOG_TYPE_MOVEMENT, 'Walks to sector: ' . $values['Sector']->getSectorID());
				break;
			case 'JoinAlliance':
				$this->log(LOG_TYPE_ALLIANCE, 'joined alliance: ' . $values['Alliance']->getAllianceName());
				break;
			case 'LeaveAlliance':
				$this->log(LOG_TYPE_ALLIANCE, 'left alliance: ' . $values['Alliance']->getAllianceName());
				break;
			case 'DisbandAlliance':
				$this->log(LOG_TYPE_ALLIANCE, 'disbanded alliance ' . $values['Alliance']->getAllianceName());
				break;
			case 'KickPlayer':
				$this->log(LOG_TYPE_ALLIANCE, 'kicked ' . $values['Player']->getAccount()->getLogin() . ' (' . $values['Player']->getPlayerName() . ') from alliance ' . $values['Alliance']->getAllianceName());
				break;
			case 'PlayerKicked':
				$this->log(LOG_TYPE_ALLIANCE, 'was kicked from alliance ' . $values['Alliance']->getAllianceName() . ' by ' . $values['Player']->getAccount()->getLogin() . ' (' . $values['Player']->getPlayerName() . ')');
				break;
		}

		$this->getMissions();
		foreach ($this->missions as $missionID => &$mission) {
			if ($mission['Task'] !== false && $mission['Task']['Step'] == $actionID) {
				$requirements = $mission['Task']['Detail'];
				if (checkMissionRequirements($values, $requirements) === true) {
					$mission['On Step']++;
					$mission['Unread'] = true;
					$this->setupMissionStep($missionID);
					$this->rebuildMission($missionID);
					$this->updateMission($missionID);
				}
			}
		}
	}

	/**
	 * @param array<SmrPlayer> $otherPlayerArray
	 */
	public function canSeeAny(array $otherPlayerArray): bool {
		foreach ($otherPlayerArray as $otherPlayer) {
			if ($this->canSee($otherPlayer)) {
				return true;
			}
		}
		return false;
	}

	public function canSee(self $otherPlayer): bool {
		if (!$otherPlayer->getShip()->isCloaked()) {
			return true;
		}
		if ($this->sameAlliance($otherPlayer)) {
			return true;
		}
		if ($this->getExperience() >= $otherPlayer->getExperience()) {
			return true;
		}
		return false;
	}

	public function equals(self $otherPlayer = null): bool {
		return $otherPlayer !== null && $this->getAccountID() == $otherPlayer->getAccountID() && $this->getGameID() == $otherPlayer->getGameID();
	}

	public function sameAlliance(self $otherPlayer = null): bool {
		return $this->equals($otherPlayer) || ($otherPlayer !== null && $this->getGameID() == $otherPlayer->getGameID() && $this->hasAlliance() && $this->getAllianceID() == $otherPlayer->getAllianceID());
	}

	public function sharedForceAlliance(self $otherPlayer = null): bool {
		return $this->sameAlliance($otherPlayer);
	}

	public function forceNAPAlliance(self $otherPlayer = null): bool {
		return $this->sameAlliance($otherPlayer);
	}

	public function planetNAPAlliance(self $otherPlayer = null): bool {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderNAPAlliance(self $otherPlayer = null): bool {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderMAPAlliance(self $otherPlayer = null): bool {
		return $this->traderAttackTraderAlliance($otherPlayer) && $this->traderDefendTraderAlliance($otherPlayer);
	}

	public function traderAttackTraderAlliance(self $otherPlayer = null): bool {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderDefendTraderAlliance(self $otherPlayer = null): bool {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderAttackForceAlliance(self $otherPlayer = null): bool {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderAttackPortAlliance(self $otherPlayer = null): bool {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderAttackPlanetAlliance(self $otherPlayer = null): bool {
		return $this->sameAlliance($otherPlayer);
	}

	public function meetsAlignmentRestriction(int $restriction): bool {
		if ($restriction < 0) {
			return $this->getAlignment() <= $restriction;
		}
		if ($restriction > 0) {
			return $this->getAlignment() >= $restriction;
		}
		return true;
	}

	/**
	 * Get an array of goods that are visible to the player
	 *
	 * @return array<int, array<string, string|int>>
	 */
	public function getVisibleGoods(): array {
		$goods = Globals::getGoods();
		$visibleGoods = [];
		foreach ($goods as $key => $good) {
			if ($this->meetsAlignmentRestriction($good['AlignRestriction'])) {
				$visibleGoods[$key] = $good;
			}
		}
		return $visibleGoods;
	}

	/**
	 * Returns an array of all unvisited sectors.
	 *
	 * @return array<int>
	 */
	public function getUnvisitedSectors(): array {
		if (!isset($this->unvisitedSectors)) {
			$this->unvisitedSectors = [];
			// Note that this table actually has entries for the *unvisited* sectors!
			$dbResult = $this->db->read('SELECT sector_id FROM player_visited_sector WHERE ' . $this->SQL);
			foreach ($dbResult->records() as $dbRecord) {
				$this->unvisitedSectors[] = $dbRecord->getInt('sector_id');
			}
		}
		return $this->unvisitedSectors;
	}

	/**
	 * Check if player has visited the input sector.
	 * Note that this populates the list of *all* unvisited sectors!
	 */
	public function hasVisitedSector(int $sectorID): bool {
		return !in_array($sectorID, $this->getUnvisitedSectors());
	}

	public function getLeaveNewbieProtectionHREF(): string {
		return (new NewbieLeaveProcessor())->href();
	}

	public function getExamineTraderHREF(): string {
		$container = new ExamineTrader($this->getAccountID());
		return $container->href();
	}

	public function getAttackTraderHREF(): string {
		return Globals::getAttackTraderHREF($this->getAccountID());
	}

	public function getPlanetKickHREF(): string {
		$container = new KickProcessor($this->getAccountID());
		return $container->href();
	}

	public function getTraderSearchHREF(): string {
		$container = new SearchForTraderResult($this->getPlayerID());
		return $container->href();
	}

	public function getAllianceRosterHREF(): string {
		return Globals::getAllianceRosterHREF($this->getAllianceID());
	}

	public function getToggleWeaponHidingHREF(bool $ajax = false): string {
		$container = new WeaponDisplayToggleProcessor();
		$container->allowAjax = $ajax;
		return $container->href();
	}

	public function isDisplayWeapons(): bool {
		return $this->displayWeapons;
	}

	/**
	 * Should weapons be displayed in the right panel?
	 * This updates the player database directly because it is used with AJAX,
	 * which does not acquire a sector lock.
	 */
	public function setDisplayWeapons(bool $bool): void {
		if ($this->displayWeapons == $bool) {
			return;
		}
		$this->displayWeapons = $bool;
		$this->db->write('UPDATE player SET display_weapons=' . $this->db->escapeBoolean($this->displayWeapons) . ' WHERE ' . $this->SQL);
	}

	public function update(): void {
		$this->save();
	}

	public function save(): void {
		if ($this->hasChanged === true) {
			$this->db->write('UPDATE player SET player_name=' . $this->db->escapeString($this->playerName) .
				', player_id=' . $this->db->escapeNumber($this->playerID) .
				', sector_id=' . $this->db->escapeNumber($this->sectorID) .
				', last_sector_id=' . $this->db->escapeNumber($this->lastSectorID) .
				', turns=' . $this->db->escapeNumber($this->turns) .
				', last_turn_update=' . $this->db->escapeNumber($this->lastTurnUpdate) .
				', newbie_turns=' . $this->db->escapeNumber($this->newbieTurns) .
				', last_news_update=' . $this->db->escapeNumber($this->lastNewsUpdate) .
				', attack_warning=' . $this->db->escapeString($this->attackColour) .
				', dead=' . $this->db->escapeBoolean($this->dead) .
				', newbie_status=' . $this->db->escapeBoolean($this->newbieStatus) .
				', land_on_planet=' . $this->db->escapeBoolean($this->landedOnPlanet) .
				', last_active=' . $this->db->escapeNumber($this->lastActive) .
				', last_cpl_action=' . $this->db->escapeNumber($this->lastCPLAction) .
				', race_id=' . $this->db->escapeNumber($this->raceID) .
				', credits=' . $this->db->escapeNumber($this->credits) .
				', experience=' . $this->db->escapeNumber($this->experience) .
				', alignment=' . $this->db->escapeNumber($this->alignment) .
				', military_payment=' . $this->db->escapeNumber($this->militaryPayment) .
				', alliance_id=' . $this->db->escapeNumber($this->allianceID) .
				', alliance_join=' . $this->db->escapeNumber($this->allianceJoinable) .
				', ship_type_id=' . $this->db->escapeNumber($this->shipID) .
				', kills=' . $this->db->escapeNumber($this->kills) .
				', deaths=' . $this->db->escapeNumber($this->deaths) .
				', assists=' . $this->db->escapeNumber($this->assists) .
				', last_port=' . $this->db->escapeNumber($this->lastPort) .
				', bank=' . $this->db->escapeNumber($this->bank) .
				', zoom=' . $this->db->escapeNumber($this->zoom) .
				', display_missions=' . $this->db->escapeBoolean($this->displayMissions) .
				', force_drop_messages=' . $this->db->escapeBoolean($this->forceDropMessages) .
				', group_scout_messages=' . $this->db->escapeString($this->scoutMessageGroupType->value) .
				', ignore_globals=' . $this->db->escapeBoolean($this->ignoreGlobals) .
				', newbie_warning = ' . $this->db->escapeBoolean($this->newbieWarning) .
				', name_changed = ' . $this->db->escapeBoolean($this->nameChanged) .
				', race_changed = ' . $this->db->escapeBoolean($this->raceChanged) .
				', combat_drones_kamikaze_on_mines = ' . $this->db->escapeBoolean($this->combatDronesKamikazeOnMines) .
				', under_attack = ' . $this->db->escapeBoolean($this->underAttack) .
				' WHERE ' . $this->SQL);
			$this->hasChanged = false;
		}
		foreach ($this->hasBountyChanged as $key => &$bountyChanged) {
			if ($bountyChanged === true) {
				$bountyChanged = false;
				$bounty = $this->getBounty($key);
				if ($bounty['New'] === true) {
					if ($bounty['Amount'] > 0 || $bounty['SmrCredits'] > 0) {
						$this->db->insert('bounty', [
							'account_id' => $this->db->escapeNumber($this->getAccountID()),
							'game_id' => $this->db->escapeNumber($this->getGameID()),
							'type' => $this->db->escapeString($bounty['Type']->value),
							'amount' => $this->db->escapeNumber($bounty['Amount']),
							'smr_credits' => $this->db->escapeNumber($bounty['SmrCredits']),
							'claimer_id' => $this->db->escapeNumber($bounty['Claimer']),
							'time' => $this->db->escapeNumber($bounty['Time']),
						]);
					}
				} else {
					if ($bounty['Amount'] > 0 || $bounty['SmrCredits'] > 0) {
						$this->db->write('UPDATE bounty
							SET amount=' . $this->db->escapeNumber($bounty['Amount']) . ',
							smr_credits=' . $this->db->escapeNumber($bounty['SmrCredits']) . ',
							type=' . $this->db->escapeString($bounty['Type']->value) . ',
							claimer_id=' . $this->db->escapeNumber($bounty['Claimer']) . ',
							time=' . $this->db->escapeNumber($bounty['Time']) . '
							WHERE bounty_id=' . $this->db->escapeNumber($bounty['ID']) . ' AND ' . $this->SQL);
					} else {
						$this->db->write('DELETE FROM bounty WHERE bounty_id=' . $this->db->escapeNumber($bounty['ID']) . ' AND ' . $this->SQL);
					}
				}
			}
		}
		$this->saveHOF();
	}

	public function saveHOF(): void {
		foreach (self::$hasHOFVisChanged as $hofType => $changeType) {
			if ($changeType == self::HOF_NEW) {
				$this->db->insert('hof_visibility', [
					'type' => $this->db->escapeString($hofType),
					'visibility' => $this->db->escapeString(self::$HOFVis[$hofType]),
				]);
			} else {
				$this->db->write('UPDATE hof_visibility SET visibility = ' . $this->db->escapeString(self::$HOFVis[$hofType]) . ' WHERE type = ' . $this->db->escapeString($hofType));
			}
			unset(self::$hasHOFVisChanged[$hofType]);
		}

		foreach ($this->hasHOFChanged as $hofType => $changeType) {
			$amount = $this->HOF[$hofType];
			if ($changeType === self::HOF_NEW) {
				if ($amount > 0) {
					$this->db->insert('player_hof', [
						'account_id' => $this->db->escapeNumber($this->getAccountID()),
						'game_id' => $this->db->escapeNumber($this->getGameID()),
						'type' => $this->db->escapeString($hofType),
						'amount' => $this->db->escapeNumber($amount),
					]);
				}
			} elseif ($changeType === self::HOF_CHANGED) {
				$this->db->write('UPDATE player_hof
					SET amount=' . $this->db->escapeNumber($amount) . '
					WHERE ' . $this->SQL . ' AND type = ' . $this->db->escapeString($hofType));
			}
			unset($this->hasHOFChanged[$hofType]);
		}
	}

}
