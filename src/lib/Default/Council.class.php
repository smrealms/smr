<?php declare(strict_types=1);

class Council {
	protected static array $COUNCILS = [];
	protected static array $PRESIDENTS = [];
	protected static Smr\Database $db;

	/**
	 * Returns an array of Account ID's of the Council for this race.
	 */
	public static function getRaceCouncil(int $gameID, int $raceID) : array {
		if (!isset(self::$COUNCILS[$gameID][$raceID])) {
			self::$db = Smr\Database::getInstance();
			self::$COUNCILS[$gameID][$raceID] = array();
			self::$PRESIDENTS[$gameID][$raceID] = false;

			// Require council members to have > 0 exp to ensure that players
			// cannot perform council activities before the game starts.
			$i = 1;
			self::$db->query('SELECT account_id, alignment
								FROM player
								WHERE game_id = ' . self::$db->escapeNumber($gameID) . '
									AND race_id = ' . self::$db->escapeNumber($raceID) . '
									AND npc = \'FALSE\'
									AND experience > 0
								ORDER by experience DESC
								LIMIT ' . MAX_COUNCIL_MEMBERS);
			while (self::$db->nextRecord()) {
				// Add this player to the council
				self::$COUNCILS[$gameID][$raceID][$i++] = self::$db->getInt('account_id');

				// Determine if this player is also the president
				if (self::$PRESIDENTS[$gameID][$raceID] === false) {
					if (self::$db->getInt('alignment') >= ALIGNMENT_PRESIDENT) {
						self::$PRESIDENTS[$gameID][$raceID] = self::$db->getInt('account_id');
					}
				}
			}
		}
		return self::$COUNCILS[$gameID][$raceID];
	}

	/**
	 * Returns the Account ID of the President for this race (or false if no President).
	 */
	public static function getPresidentID(int $gameID, int $raceID) : int|false {
		self::getRaceCouncil($gameID, $raceID); // determines the president
		return self::$PRESIDENTS[$gameID][$raceID];
	}

	public static function isOnCouncil(int $gameID, int $raceID, int $accountID) : bool {
		return in_array($accountID, self::getRaceCouncil($gameID, $raceID));
	}
}
