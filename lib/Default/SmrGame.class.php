<?php declare(strict_types=1);

// Exception thrown when a game cannot be found in the database
class GameNotFoundException extends Exception {}

class SmrGame {
	protected static $CACHE_GAMES = array();

	protected MySqlDatabase $db;

	protected int $gameID;
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

	const GAME_TYPE_DEFAULT = 0;
	const GAME_TYPE_HUNTER_WARS = 3;
	const GAME_TYPE_SEMI_WARS = 4;
	const GAME_TYPE_DRAFT = 5;
	const GAME_TYPE_FFA = 6;
	const GAME_TYPE_NEWBIE = 7;
	const GAME_TYPES = [
		self::GAME_TYPE_DEFAULT => 'Default',
		self::GAME_TYPE_HUNTER_WARS => 'Hunter Wars',
		self::GAME_TYPE_SEMI_WARS => 'Semi Wars',
		self::GAME_TYPE_DRAFT => 'Draft',
		self::GAME_TYPE_FFA => 'FFA',
		self::GAME_TYPE_NEWBIE => 'Newbie',
	];

	public static function getGame(int $gameID, bool $forceUpdate = false) : SmrGame {
		if ($forceUpdate || !isset(self::$CACHE_GAMES[$gameID])) {
			$g = new SmrGame($gameID);
			self::$CACHE_GAMES[$gameID] = $g;
		}
		return self::$CACHE_GAMES[$gameID];
	}

	public static function saveGames() : void {
		foreach (self::$CACHE_GAMES as $game) {
			$game->save();
		}
	}

	public static function createGame(int $gameID) : SmrGame {
		if (!isset(self::$CACHE_GAMES[$gameID])) {
			$g = new SmrGame($gameID, true);
			self::$CACHE_GAMES[$gameID] = $g;
		}
		return self::$CACHE_GAMES[$gameID];
	}

	protected function __construct(int $gameID, bool $create = false) {
		$this->db = MySqlDatabase::getInstance();

		$this->db->query('SELECT * FROM game WHERE game_id = ' . $this->db->escapeNumber($gameID) . ' LIMIT 1');
		if ($this->db->nextRecord()) {
			$this->gameID = $this->db->getInt('game_id');
			$this->name = $this->db->getField('game_name');
			$this->description = $this->db->getField('game_description');
			$this->joinTime = $this->db->getInt('join_time');
			$this->startTime = $this->db->getInt('start_time');
			$this->endTime = $this->db->getInt('end_time');
			$this->maxPlayers = $this->db->getInt('max_players');
			$this->maxTurns = $this->db->getInt('max_turns');
			$this->startTurnHours = $this->db->getInt('start_turns');
			$this->gameTypeID = $this->db->getInt('game_type');
			$this->creditsNeeded = $this->db->getInt('credits_needed');
			$this->gameSpeed = $this->db->getFloat('game_speed');
			$this->enabled = $this->db->getBoolean('enabled');
			$this->ignoreStats = $this->db->getBoolean('ignore_stats');
			$this->allianceMaxPlayers = $this->db->getInt('alliance_max_players');
			$this->allianceMaxVets = $this->db->getInt('alliance_max_vets');
			$this->startingCredits = $this->db->getInt('starting_credits');
		} elseif ($create === true) {
			$this->gameID = $gameID;
			$this->isNew = true;
			return;
		} else {
			throw new GameNotFoundException('No such game: ' . $gameID);
		}
	}

	public function save() : void {
		if ($this->isNew) {
			$this->db->query('INSERT INTO game (game_id,game_name,game_description,join_time,start_time,end_time,max_players,max_turns,start_turns,game_type,credits_needed,game_speed,enabled,ignore_stats,alliance_max_players,alliance_max_vets,starting_credits)
									VALUES
									(' . $this->db->escapeNumber($this->getGameID()) .
										',' . $this->db->escapeString($this->getName()) .
										',' . $this->db->escapeString($this->getDescription()) .
										',' . $this->db->escapeNumber($this->getJoinTime()) .
										',' . $this->db->escapeNumber($this->getStartTime()) .
										',' . $this->db->escapeNumber($this->getEndTime()) .
										',' . $this->db->escapeNumber($this->getMaxPlayers()) .
										',' . $this->db->escapeNumber($this->getMaxTurns()) .
										',' . $this->db->escapeNumber($this->getStartTurnHours()) .
										',' . $this->db->escapeNumber($this->gameTypeID) .
										',' . $this->db->escapeNumber($this->getCreditsNeeded()) .
										',' . $this->db->escapeNumber($this->getGameSpeed()) .
										',' . $this->db->escapeBoolean($this->isEnabled()) .
										',' . $this->db->escapeBoolean($this->isIgnoreStats()) .
										',' . $this->db->escapeNumber($this->getAllianceMaxPlayers()) .
										',' . $this->db->escapeNumber($this->getAllianceMaxVets()) .
										',' . $this->db->escapeNumber($this->getStartingCredits()) . ')');
		} elseif ($this->hasChanged) {
			$this->db->query('UPDATE game SET game_name = ' . $this->db->escapeString($this->getName()) .
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

	public function getGameID() : int {
		return $this->gameID;
	}

	public function getName() : string {
		return $this->name;
	}

	public function setName(string $name) : void {
		if (!$this->isNew && $this->name === $name) {
			return;
		}
		$this->name = $name;
		$this->hasChanged = true;
	}

	public function getDescription() : string {
		return $this->description;
	}

	public function setDescription(string $description) : void {
		if (!$this->isNew && $this->description === $description) {
			return;
		}
		$this->description = $description;
		$this->hasChanged = true;
	}

	public function hasStarted() : bool {
		return TIME >= $this->getStartTime();
	}

	/**
	 * Returns the epoch time when the game starts,
	 * i.e. when players can move, turns are gained, etc.
	 */
	public function getStartTime() : int {
		return $this->startTime;
	}

	public function setStartTime(int $startTime) : void {
		if (!$this->isNew && $this->startTime === $startTime) {
			return;
		}
		$this->startTime = $startTime;
		$this->hasChanged = true;
	}

	/**
	 * Returns the epoch time when players can begin to join the game.
	 */
	public function getJoinTime() : int {
		return $this->joinTime;
	}

	public function setJoinTime(int $joinTime) : void {
		if (!$this->isNew && $this->joinTime === $joinTime) {
			return;
		}
		$this->joinTime = $joinTime;
		$this->hasChanged = true;
	}

	public function hasEnded() : bool {
		return $this->getEndTime() < TIME;
	}

	/**
	 * Returns the epoch time when the game ends.
	 */
	public function getEndTime() : int {
		return $this->endTime;
	}

	public function setEndTime(int $endTime) : void {
		if (!$this->isNew && $this->endTime === $endTime) {
			return;
		}
		$this->endTime = $endTime;
		$this->hasChanged = true;
	}

	public function getMaxPlayers() : int {
		return $this->maxPlayers;
	}

	public function setMaxPlayers(int $maxPlayers) : void {
		if (!$this->isNew && $this->maxPlayers === $maxPlayers) {
			return;
		}
		$this->maxPlayers = $maxPlayers;
		$this->hasChanged = true;
	}

	public function getMaxTurns() : int {
		return $this->maxTurns;
	}

	public function setMaxTurns(int $int) : void {
		if (!$this->isNew && $this->maxTurns === $int) {
			return;
		}
		$this->maxTurns = $int;
		$this->hasChanged = true;
	}

	public function getStartTurnHours() : int {
		return $this->startTurnHours;
	}

	public function setStartTurnHours(int $int) : void {
		if (!$this->isNew && $this->startTurnHours === $int) {
			return;
		}
		$this->startTurnHours = $int;
		$this->hasChanged = true;
	}

	public function getGameType() : string {
		return self::GAME_TYPES[$this->gameTypeID];
	}

	public function setGameTypeID(int $gameTypeID) : void {
		if (!$this->isNew && $this->gameTypeID === $gameTypeID) {
			return;
		}
		$this->gameTypeID = $gameTypeID;
		$this->hasChanged = true;
	}

	public function getCreditsNeeded() : int {
		return $this->creditsNeeded;
	}

	public function setCreditsNeeded(int $creditsNeeded) : void {
		if (!$this->isNew && $this->creditsNeeded === $creditsNeeded) {
			return;
		}
		$this->creditsNeeded = $creditsNeeded;
		$this->hasChanged = true;
	}

	public function getGameSpeed() : float {
		return $this->gameSpeed;
	}

	public function setGameSpeed(float $gameSpeed) : void {
		if (!$this->isNew && $this->gameSpeed === $gameSpeed) {
			return;
		}
		$this->gameSpeed = $gameSpeed;
		$this->hasChanged = true;
	}

	public function isEnabled() : bool {
		return $this->enabled;
	}

	public function setEnabled(bool $bool) : void {
		if (!$this->isNew && $this->enabled === $bool) {
			return;
		}
		$this->enabled = $bool;
		$this->hasChanged = true;
	}

	public function isIgnoreStats() : bool {
		return $this->ignoreStats;
	}

	public function setIgnoreStats(bool $bool) : void {
		if (!$this->isNew && $this->ignoreStats === $bool) {
			return;
		}
		$this->ignoreStats = $bool;
		$this->hasChanged = true;
	}

	public function getAllianceMaxPlayers() : int {
		return $this->allianceMaxPlayers;
	}

	public function setAllianceMaxPlayers(int $int) : void {
		if (!$this->isNew && $this->allianceMaxPlayers === $int) {
			return;
		}
		$this->allianceMaxPlayers = $int;
		$this->hasChanged = true;
	}

	public function getAllianceMaxVets() : int {
		return $this->allianceMaxVets;
	}

	public function setAllianceMaxVets(int $int) : void {
		if (!$this->isNew && $this->allianceMaxVets === $int) {
			return;
		}
		$this->allianceMaxVets = $int;
		$this->hasChanged = true;
	}

	public function getStartingCredits() : int {
		return $this->startingCredits;
	}

	public function setStartingCredits(int $int) : void {
		if (!$this->isNew && $this->startingCredits === $int) {
			return;
		}
		$this->startingCredits = $int;
		$this->hasChanged = true;
	}

	public function getTotalPlayers() : int {
		if (!isset($this->totalPlayers)) {
			$this->db->query('SELECT count(*) FROM player WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()));
			$this->db->nextRecord();
			$this->totalPlayers = $this->db->getInt('count(*)');
		}
		return $this->totalPlayers;
	}

	public function getNumberOfGalaxies() : int {
		return count(SmrGalaxy::getGameGalaxies($this->getGameID()));
	}

	public function equals(SmrGame $otherGame) : bool {
		return $otherGame->getGameID() == $this->getGameID();
	}

	// Convenience function for printing the game name with id
	public function getDisplayName() : string {
		return $this->getName() . " (" . $this->getGameID() . ")";
	}

	/**
	 * Set the starting political relations between races.
	 */
	public function setStartingRelations(int $relations) : void {
		if ($relations < MIN_GLOBAL_RELATIONS || $relations > MAX_GLOBAL_RELATIONS) {
			throw new Exception('Invalid relations: ' . $relations);
		}
		foreach (Globals::getRaces() as $race1) {
			foreach (Globals::getRaces() as $race2) {
				if ($race1['Race ID'] == $race2['Race ID']) {
					// Max relations for a race with itself
					$amount = MAX_GLOBAL_RELATIONS;
				} elseif ($race1['Race ID'] == RACE_NEUTRAL || $race2['Race ID'] == RACE_NEUTRAL) {
					$amount = 0; //0 relations with neutral
				} else {
					$amount = $relations;
				}
				$this->db->query('REPLACE INTO race_has_relation (game_id, race_id_1, race_id_2, relation)
				                  VALUES (' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeNumber($race1['Race ID']) . ',' . $this->db->escapeNumber($race2['Race ID']) . ',' . $this->db->escapeNumber($amount) . ')');
			}
		}
	}

	/**
	 * Get the list of playable Race IDs based on which Racial HQ's
	 * are locations in this game.
	 */
	public function getPlayableRaceIDs() : array {
		if (!isset($this->playableRaceIDs)) {
			// Get a unique set of HQ's available in game
			$this->db->query('SELECT DISTINCT location_type_id
				FROM location
				WHERE location_type_id > ' . $this->db->escapeNumber(UNDERGROUND) . '
					AND location_type_id < ' . $this->db->escapeNumber(FED) . '
					AND game_id = ' . $this->db->escapeNumber($this->getGameID()) . '
				ORDER BY location_type_id');
			$this->playableRaceIDs = array();
			while ($this->db->nextRecord()) {
				$this->playableRaceIDs[] = $this->db->getInt('location_type_id') - 101;
			}
		}
		return $this->playableRaceIDs;
	}

	/**
	 * Returns the time (in seconds) until restricted ships are unlocked.
	 */
	public function timeUntilShipUnlock() : int {
		return $this->getStartTime() + TIME_FOR_RAIDER_UNLOCK - TIME;
	}

}
