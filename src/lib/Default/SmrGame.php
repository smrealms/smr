<?php declare(strict_types=1);

class SmrGame {

	protected static array $CACHE_GAMES = [];

	protected Smr\Database $db;

	protected string $name;
	protected string $description;
	protected int $joinTime;
	protected int $startTime;
	protected int $endTime;
	protected int $maxPlayers;
	protected int $maxTurns;
	protected int $startTurnHours;
	protected int $gameTypeID;
	protected int $creditsNeeded;
	protected float $gameSpeed;
	protected bool $enabled;
	protected bool $ignoreStats;
	protected int $allianceMaxPlayers;
	protected int $allianceMaxVets;
	protected int $startingCredits;

	protected int $totalPlayers;
	protected array $playableRaceIDs;

	protected bool $hasChanged = false;
	protected bool $isNew = false;

	public const GAME_TYPE_DEFAULT = 0;
	public const GAME_TYPE_HUNTER_WARS = 3;
	public const GAME_TYPE_SEMI_WARS = 4;
	public const GAME_TYPE_DRAFT = 5;
	public const GAME_TYPE_FFA = 6;
	public const GAME_TYPE_NEWBIE = 7;
	public const GAME_TYPES = [
		self::GAME_TYPE_DEFAULT => 'Default',
		self::GAME_TYPE_HUNTER_WARS => 'Hunter Wars',
		self::GAME_TYPE_SEMI_WARS => 'Semi Wars',
		self::GAME_TYPE_DRAFT => 'Draft',
		self::GAME_TYPE_FFA => 'FFA',
		self::GAME_TYPE_NEWBIE => 'Newbie',
	];

	/**
	 * Attempts to construct the game to determine if it exists.
	 */
	public static function gameExists(int $gameID): bool {
		try {
			self::getGame($gameID);
			return true;
		} catch (Smr\Exceptions\GameNotFound) {
			return false;
		}
	}

	public static function getGame(int $gameID, bool $forceUpdate = false): self {
		if ($forceUpdate || !isset(self::$CACHE_GAMES[$gameID])) {
			self::$CACHE_GAMES[$gameID] = new self($gameID);
		}
		return self::$CACHE_GAMES[$gameID];
	}

	public static function clearCache(): void {
		self::$CACHE_GAMES = [];
	}

	public static function saveGames(): void {
		foreach (self::$CACHE_GAMES as $game) {
			$game->save();
		}
	}

	public static function createGame(int $gameID): self {
		if (!isset(self::$CACHE_GAMES[$gameID])) {
			self::$CACHE_GAMES[$gameID] = new self($gameID, true);
		}
		return self::$CACHE_GAMES[$gameID];
	}

	protected function __construct(
		protected readonly int $gameID,
		bool $create = false
	) {
		$this->db = Smr\Database::getInstance();

		$dbResult = $this->db->read('SELECT * FROM game WHERE game_id = ' . $this->db->escapeNumber($gameID) . ' LIMIT 1');
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$this->name = $dbRecord->getField('game_name');
			$this->description = $dbRecord->getField('game_description');
			$this->joinTime = $dbRecord->getInt('join_time');
			$this->startTime = $dbRecord->getInt('start_time');
			$this->endTime = $dbRecord->getInt('end_time');
			$this->maxPlayers = $dbRecord->getInt('max_players');
			$this->maxTurns = $dbRecord->getInt('max_turns');
			$this->startTurnHours = $dbRecord->getInt('start_turns');
			$this->gameTypeID = $dbRecord->getInt('game_type');
			$this->creditsNeeded = $dbRecord->getInt('credits_needed');
			$this->gameSpeed = $dbRecord->getFloat('game_speed');
			$this->enabled = $dbRecord->getBoolean('enabled');
			$this->ignoreStats = $dbRecord->getBoolean('ignore_stats');
			$this->allianceMaxPlayers = $dbRecord->getInt('alliance_max_players');
			$this->allianceMaxVets = $dbRecord->getInt('alliance_max_vets');
			$this->startingCredits = $dbRecord->getInt('starting_credits');
		} elseif ($create === true) {
			$this->isNew = true;
		} else {
			throw new Smr\Exceptions\GameNotFound('No such game: ' . $gameID);
		}
	}

	public function save(): void {
		if ($this->isNew) {
			$this->db->insert('game', [
				'game_id' => $this->db->escapeNumber($this->getGameID()),
				'game_name' => $this->db->escapeString($this->getName()),
				'game_description' => $this->db->escapeString($this->getDescription()),
				'join_time' => $this->db->escapeNumber($this->getJoinTime()),
				'start_time' => $this->db->escapeNumber($this->getStartTime()),
				'end_time' => $this->db->escapeNumber($this->getEndTime()),
				'max_players' => $this->db->escapeNumber($this->getMaxPlayers()),
				'max_turns' => $this->db->escapeNumber($this->getMaxTurns()),
				'start_turns' => $this->db->escapeNumber($this->getStartTurnHours()),
				'game_type' => $this->db->escapeNumber($this->gameTypeID),
				'credits_needed' => $this->db->escapeNumber($this->getCreditsNeeded()),
				'game_speed' => $this->db->escapeNumber($this->getGameSpeed()),
				'enabled' => $this->db->escapeBoolean($this->isEnabled()),
				'ignore_stats' => $this->db->escapeBoolean($this->isIgnoreStats()),
				'alliance_max_players' => $this->db->escapeNumber($this->getAllianceMaxPlayers()),
				'alliance_max_vets' => $this->db->escapeNumber($this->getAllianceMaxVets()),
				'starting_credits' => $this->db->escapeNumber($this->getStartingCredits()),
			]);
		} elseif ($this->hasChanged) {
			$this->db->write('UPDATE game SET game_name = ' . $this->db->escapeString($this->getName()) .
										', game_description = ' . $this->db->escapeString($this->getDescription()) .
										', join_time = ' . $this->db->escapeNumber($this->getJoinTime()) .
										', start_time = ' . $this->db->escapeNumber($this->getStartTime()) .
										', end_time = ' . $this->db->escapeNumber($this->getEndTime()) .
										', max_players = ' . $this->db->escapeNumber($this->getMaxPlayers()) .
										', max_turns = ' . $this->db->escapeNumber($this->getMaxTurns()) .
										', start_turns = ' . $this->db->escapeNumber($this->getStartTurnHours()) .
										', game_type = ' . $this->db->escapeNumber($this->gameTypeID) .
										', credits_needed = ' . $this->db->escapeNumber($this->getCreditsNeeded()) .
										', game_speed = ' . $this->db->escapeNumber($this->getGameSpeed()) .
										', enabled = ' . $this->db->escapeBoolean($this->isEnabled()) .
										', ignore_stats = ' . $this->db->escapeBoolean($this->isIgnoreStats()) .
										', alliance_max_players = ' . $this->db->escapeNumber($this->getAllianceMaxPlayers()) .
										', alliance_max_vets = ' . $this->db->escapeNumber($this->getAllianceMaxVets()) .
										', starting_credits = ' . $this->db->escapeNumber($this->getStartingCredits()) .
									' WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()) . ' LIMIT 1');
		}
		$this->isNew = false;
		$this->hasChanged = false;
	}

	public function getGameID(): int {
		return $this->gameID;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name): void {
		if (!$this->isNew && $this->name === $name) {
			return;
		}
		$this->name = $name;
		$this->hasChanged = true;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function setDescription(string $description): void {
		if (!$this->isNew && $this->description === $description) {
			return;
		}
		$this->description = $description;
		$this->hasChanged = true;
	}

	public function hasStarted(): bool {
		return Smr\Epoch::time() >= $this->getStartTime();
	}

	/**
	 * Returns the epoch time when the game starts,
	 * i.e. when players can move, turns are gained, etc.
	 */
	public function getStartTime(): int {
		return $this->startTime;
	}

	public function setStartTime(int $startTime): void {
		if (!$this->isNew && $this->startTime === $startTime) {
			return;
		}
		$this->startTime = $startTime;
		$this->hasChanged = true;
	}

	/**
	 * Returns the epoch time when players can begin to join the game.
	 */
	public function getJoinTime(): int {
		return $this->joinTime;
	}

	public function setJoinTime(int $joinTime): void {
		if (!$this->isNew && $this->joinTime === $joinTime) {
			return;
		}
		$this->joinTime = $joinTime;
		$this->hasChanged = true;
	}

	public function hasEnded(): bool {
		return $this->getEndTime() < Smr\Epoch::time();
	}

	/**
	 * Returns the epoch time when the game ends.
	 */
	public function getEndTime(): int {
		return $this->endTime;
	}

	public function setEndTime(int $endTime): void {
		if (!$this->isNew && $this->endTime === $endTime) {
			return;
		}
		$this->endTime = $endTime;
		$this->hasChanged = true;
	}

	public function getMaxPlayers(): int {
		return $this->maxPlayers;
	}

	public function setMaxPlayers(int $maxPlayers): void {
		if (!$this->isNew && $this->maxPlayers === $maxPlayers) {
			return;
		}
		$this->maxPlayers = $maxPlayers;
		$this->hasChanged = true;
	}

	public function getMaxTurns(): int {
		return $this->maxTurns;
	}

	public function setMaxTurns(int $int): void {
		if (!$this->isNew && $this->maxTurns === $int) {
			return;
		}
		$this->maxTurns = $int;
		$this->hasChanged = true;
	}

	public function getStartTurnHours(): int {
		return $this->startTurnHours;
	}

	public function setStartTurnHours(int $int): void {
		if (!$this->isNew && $this->startTurnHours === $int) {
			return;
		}
		$this->startTurnHours = $int;
		$this->hasChanged = true;
	}

	public function getGameType(): string {
		return self::GAME_TYPES[$this->gameTypeID];
	}

	public function setGameTypeID(int $gameTypeID): void {
		if (!$this->isNew && $this->gameTypeID === $gameTypeID) {
			return;
		}
		$this->gameTypeID = $gameTypeID;
		$this->hasChanged = true;
	}

	public function getCreditsNeeded(): int {
		return $this->creditsNeeded;
	}

	public function setCreditsNeeded(int $creditsNeeded): void {
		if (!$this->isNew && $this->creditsNeeded === $creditsNeeded) {
			return;
		}
		$this->creditsNeeded = $creditsNeeded;
		$this->hasChanged = true;
	}

	public function getGameSpeed(): float {
		return $this->gameSpeed;
	}

	public function setGameSpeed(float $gameSpeed): void {
		if (!$this->isNew && $this->gameSpeed === $gameSpeed) {
			return;
		}
		$this->gameSpeed = $gameSpeed;
		$this->hasChanged = true;
	}

	public function isEnabled(): bool {
		return $this->enabled;
	}

	public function setEnabled(bool $bool): void {
		if (!$this->isNew && $this->enabled === $bool) {
			return;
		}
		$this->enabled = $bool;
		$this->hasChanged = true;
	}

	public function isIgnoreStats(): bool {
		return $this->ignoreStats;
	}

	public function setIgnoreStats(bool $bool): void {
		if (!$this->isNew && $this->ignoreStats === $bool) {
			return;
		}
		$this->ignoreStats = $bool;
		$this->hasChanged = true;
	}

	public function getAllianceMaxPlayers(): int {
		return $this->allianceMaxPlayers;
	}

	public function setAllianceMaxPlayers(int $int): void {
		if (!$this->isNew && $this->allianceMaxPlayers === $int) {
			return;
		}
		$this->allianceMaxPlayers = $int;
		$this->hasChanged = true;
	}

	public function getAllianceMaxVets(): int {
		return $this->allianceMaxVets;
	}

	public function setAllianceMaxVets(int $int): void {
		if (!$this->isNew && $this->allianceMaxVets === $int) {
			return;
		}
		$this->allianceMaxVets = $int;
		$this->hasChanged = true;
	}

	public function getStartingCredits(): int {
		return $this->startingCredits;
	}

	public function setStartingCredits(int $int): void {
		if (!$this->isNew && $this->startingCredits === $int) {
			return;
		}
		$this->startingCredits = $int;
		$this->hasChanged = true;
	}

	public function getTotalPlayers(): int {
		if (!isset($this->totalPlayers)) {
			$dbResult = $this->db->read('SELECT count(*) FROM player WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()));
			$this->totalPlayers = $dbResult->record()->getInt('count(*)');
		}
		return $this->totalPlayers;
	}

	/**
	 * @return array<int, \SmrGalaxy>
	 */
	public function getGalaxies(): array {
		return SmrGalaxy::getGameGalaxies($this->gameID);
	}

	public function getNumberOfGalaxies(): int {
		return count($this->getGalaxies());
	}

	public function equals(self $otherGame): bool {
		return $otherGame->getGameID() == $this->getGameID();
	}

	// Convenience function for printing the game name with id
	public function getDisplayName(): string {
		return $this->getName() . ' (' . $this->getGameID() . ')';
	}

	/**
	 * Set the starting political relations between races.
	 */
	public function setStartingRelations(int $relations): void {
		if ($relations < MIN_GLOBAL_RELATIONS || $relations > MAX_GLOBAL_RELATIONS) {
			throw new Exception('Invalid relations: ' . $relations);
		}
		foreach (Smr\Race::getAllIDs() as $raceID1) {
			foreach (Smr\Race::getAllIDs() as $raceID2) {
				if ($raceID1 == $raceID2) {
					// Max relations for a race with itself
					$amount = MAX_GLOBAL_RELATIONS;
				} elseif ($raceID1 == RACE_NEUTRAL || $raceID2 == RACE_NEUTRAL) {
					$amount = 0; //0 relations with neutral
				} else {
					$amount = $relations;
				}
				$this->db->replace('race_has_relation', [
					'game_id' => $this->db->escapeNumber($this->getGameID()),
					'race_id_1' => $this->db->escapeNumber($raceID1),
					'race_id_2' => $this->db->escapeNumber($raceID2),
					'relation' => $this->db->escapeNumber($amount),
				]);
			}
		}
	}

	/**
	 * Get the list of playable Race IDs based on which Racial HQ's
	 * are locations in this game.
	 */
	public function getPlayableRaceIDs(): array {
		if (!isset($this->playableRaceIDs)) {
			// Get a unique set of HQ's available in game
			$dbResult = $this->db->read('SELECT DISTINCT location_type_id
				FROM location
				WHERE location_type_id > ' . $this->db->escapeNumber(UNDERGROUND) . '
					AND location_type_id < ' . $this->db->escapeNumber(FED) . '
					AND game_id = ' . $this->db->escapeNumber($this->getGameID()) . '
				ORDER BY location_type_id');
			$this->playableRaceIDs = [];
			foreach ($dbResult->records() as $dbRecord) {
				$this->playableRaceIDs[] = $dbRecord->getInt('location_type_id') - GOVERNMENT;
			}
		}
		return $this->playableRaceIDs;
	}

	/**
	 * Returns the time (in seconds) until restricted ships are unlocked.
	 */
	public function timeUntilShipUnlock(): int {
		return $this->getStartTime() + TIME_FOR_RAIDER_UNLOCK - Smr\Epoch::time();
	}

}
