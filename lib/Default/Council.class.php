<?php declare(strict_types=1);

class Council {
	protected static $COUNCILS = array();
	protected static $PRESIDENTS = array();
	protected static $db = null;

	private function __construct() {
	}

	protected static function initialiseDatabase() {
		if (self::$db == null) {
			self::$db = new SmrMySqlDatabase();
		}
	}

	/**
	 * Returns an array of Account ID's of the Council for this race.
	 */
	public static function getRaceCouncil($gameID, $raceID) {
		if (!isset(self::$COUNCILS[$gameID][$raceID])) {
			self::initialiseDatabase();
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
	public static function getPresidentID($gameID, $raceID) {
		if (!isset(self::$PRESIDENTS[$gameID][$raceID])) {
			self::initialiseDatabase();
			self::getRaceCouncil($gameID, $raceID); // determines the president
		}
		return self::$PRESIDENTS[$gameID][$raceID];
	}

	public static function isOnCouncil($gameID, $raceID, $accountID) {
		return in_array($accountID, self::getRaceCouncil($gameID, $raceID));
	}
}
