<?php

// Exception thrown when a game cannot be found in the database
class GameNotFoundException extends Exception {}

class SmrGame {
	protected static $CACHE_GAMES = array();

	protected $db;

	protected $gameID;
	protected $name;
	protected $description;
	protected $joinTime;
	protected $startTime;
	protected $endTime;
	protected $maxPlayers;
	protected $maxTurns;
	protected $startTurnHours;
	protected $gameTypeID;
	protected $creditsNeeded;
	protected $gameSpeed;
	protected $enabled;
	protected $ignoreStats;
	protected $allianceMaxPlayers;
	protected $allianceMaxVets;
	protected $startingCredits;

	protected $totalPlayers;

	protected $hasChanged = false;
	protected $isNew = false;

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

	public static function &getGame($gameID,$forceUpdate = false) {
		if($forceUpdate || !isset(self::$CACHE_GAMES[$gameID])) {
			$g = new SmrGame($gameID);
			self::$CACHE_GAMES[$gameID] = $g;
		}
		return self::$CACHE_GAMES[$gameID];
	}

	public static function saveGames() {
		foreach(self::$CACHE_GAMES as $game) {
			$game->save();
		}
	}

	public static function &createGame($gameID) {
		if(!isset(self::$CACHE_GAMES[$gameID])) {
			$g = new SmrGame($gameID,true);
			self::$CACHE_GAMES[$gameID] = $g;
		}
		return self::$CACHE_GAMES[$gameID];
	}

	protected function __construct($gameID, $create=false) {
		$this->db = new SmrMySqlDatabase();

		$this->db->query('SELECT * FROM game WHERE game_id = ' . $this->db->escapeNumber($gameID) . ' LIMIT 1');
		if($this->db->nextRecord()) {
			$this->gameID				= $this->db->getInt('game_id');
			$this->name					= $this->db->getField('game_name');
			$this->description			= $this->db->getField('game_description');
			$this->joinTime = $this->db->getInt('join_time');
			$this->startTime = $this->db->getInt('start_time');
			$this->endTime = $this->db->getInt('end_time');
			$this->maxPlayers			= $this->db->getInt('max_players');
			$this->maxTurns				= $this->db->getInt('max_turns');
			$this->startTurnHours		= $this->db->getInt('start_turns');
			$this->gameTypeID			= $this->db->getInt('game_type');
			$this->creditsNeeded		= $this->db->getInt('credits_needed');
			$this->gameSpeed			= $this->db->getReal('game_speed');
			$this->enabled				= $this->db->getBoolean('enabled');
			$this->ignoreStats			= $this->db->getBoolean('ignore_stats');
			$this->allianceMaxPlayers	= $this->db->getInt('alliance_max_players');
			$this->allianceMaxVets		= $this->db->getInt('alliance_max_vets');
			$this->startingCredits		= $this->db->getInt('starting_credits');
		}
		else if($create===true) {
			$this->gameID		= (int)$gameID;
			$this->isNew		= true;
			return;
		}
		else {
			throw new GameNotFoundException('No such game: ' . $gameID);
		}
	}

	public function save() {
		if($this->hasChanged) {
			if($this->isNew==false) {
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
			else {
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
			}
		}
	}

	public function getGameID() {
		return $this->gameID;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		if($this->name==$name) {
			return;
		}
		$this->name=$name;
		$this->hasChanged=true;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		if($this->description==$description) {
			return;
		}
		$this->description=$description;
		$this->hasChanged=true;
	}

	public function hasStarted() {
		return TIME >= $this->getStartTime();
	}

	/**
	 * Returns the epoch time when the game starts,
	 * i.e. when players can move, turns are gained, etc.
	 */
	public function getStartTime() {
		return $this->startTime;
	}

	public function setStartTime($startTime) {
		if ($this->startTime == $startTime) {
			return;
		}
		$this->startTime = $startTime;
		$this->hasChanged = true;
	}

	/**
	 * Returns the epoch time when players can begin to join the game.
	 */
	public function getJoinTime() {
		return $this->joinTime;
	}

	public function setJoinTime($joinTime) {
		if ($this->joinTime == $joinTime) {
			return;
		}
		$this->joinTime = $joinTime;
		$this->hasChanged = true;
	}

	public function hasEnded() {
		return $this->getEndTime() < TIME;
	}

	/**
	 * Returns the epoch time when the game ends.
	 */
	public function getEndTime() {
		return $this->endTime;
	}

	public function setEndTime($endTime) {
		if ($this->endTime == $endTime) {
			return;
		}
		$this->endTime = $endTime;
		$this->hasChanged = true;
	}

	public function getMaxPlayers() {
		return $this->maxPlayers;
	}

	public function setMaxPlayers($maxPlayers) {
		if($this->maxPlayers==$maxPlayers) {
			return;
		}
		$this->maxPlayers=$maxPlayers;
		$this->hasChanged=true;
	}

	public function getMaxTurns() {
		return $this->maxTurns;
	}

	public function setMaxTurns($int) {
		if($this->maxTurns==$int) {
			return;
		}
		$this->maxTurns=$int;
		$this->hasChanged=true;
	}

	public function getStartTurnHours() {
		return $this->startTurnHours;
	}

	public function setStartTurnHours($int) {
		if($this->startTurnHours==$int) {
			return;
		}
		$this->startTurnHours=$int;
		$this->hasChanged=true;
	}

	public function getGameType() {
		return self::GAME_TYPES[$this->gameTypeID];
	}

	public function setGameTypeID($gameTypeID) {
		if ($this->gameTypeID == $gameTypeID) {
			return;
		}
		$this->gameTypeID = $gameTypeID;
		$this->hasChanged=true;
	}

	public function getCreditsNeeded() {
		return $this->creditsNeeded;
	}

	public function setCreditsNeeded($creditsNeeded) {
		if($this->creditsNeeded==$creditsNeeded) {
			return;
		}
		$this->creditsNeeded=$creditsNeeded;
		$this->hasChanged=true;
	}

	public function getGameSpeed() {
		return $this->gameSpeed;
	}

	public function setGameSpeed($gameSpeed) {
		if($this->gameSpeed==$gameSpeed) {
			return;
		}
		$this->gameSpeed=$gameSpeed;
		$this->hasChanged=true;
	}

	public function isEnabled() {
		return $this->enabled;
	}

	public function setEnabled($bool) {
		if($this->enabled === $bool) {
			return;
		}
		$this->enabled=$bool;
		$this->hasChanged=true;
	}

	public function isIgnoreStats() {
		return $this->ignoreStats;
	}

	public function setIgnoreStats($bool) {
		if($this->ignoreStats === $bool) {
			return;
		}
		$this->ignoreStats=$bool;
		$this->hasChanged=true;
	}

	public function getAllianceMaxPlayers() {
		return $this->allianceMaxPlayers;
	}

	public function setAllianceMaxPlayers($int) {
		if($this->allianceMaxPlayers==$int) {
			return;
		}
		$this->allianceMaxPlayers=$int;
		$this->hasChanged=true;
	}

	public function getAllianceMaxVets() {
		return $this->allianceMaxVets;
	}

	public function setAllianceMaxVets($int) {
		if($this->allianceMaxVets==$int) {
			return;
		}
		$this->allianceMaxVets=$int;
		$this->hasChanged=true;
	}

	public function getStartingCredits() {
		return $this->startingCredits;
	}

	public function setStartingCredits($int) {
		if($this->startingCredits==$int) {
			return;
		}
		$this->startingCredits=$int;
		$this->hasChanged=true;
	}

	public function getTotalPlayers() {
		if(!isset($this->totalPlayers)) {
			$this->db->query('SELECT count(*) FROM player WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()));
			$this->db->nextRecord();
			$this->totalPlayers = $this->db->getInt('count(*)');
		}
		return $this->totalPlayers;
	}

	public function getNumberOfGalaxies() {
		return count(SmrGalaxy::getGameGalaxies($this->getGameID()));
	}

	public function equals(SmrGame $otherGame) {
		return $otherGame->getGameID()==$this->getGameID();
	}

	// Convenience function for printing the game name with id
	public function getDisplayName() {
		return $this->getName() . " (" . $this->getGameID() . ")";
	}
}
