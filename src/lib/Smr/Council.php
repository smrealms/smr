<?php declare(strict_types=1);

namespace Smr;

class Council {

	/** @var array<int, array<int, array<int, int>>> */
	protected static array $COUNCILS = [];
	/** @var array<int, array<int, int|false>> */
	protected static array $PRESIDENTS = [];

	/**
	 * Returns an array of Account ID's of the Council for this race.
	 *
	 * @return array<int, int>
	 */
	public static function getRaceCouncil(int $gameID, int $raceID): array {
		if (!isset(self::$COUNCILS[$gameID][$raceID])) {
			self::$COUNCILS[$gameID][$raceID] = [];
			self::$PRESIDENTS[$gameID][$raceID] = false;

			// Require council members to have > 0 exp to ensure that players
			// cannot perform council activities before the game starts.
			$i = 1;
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT account_id, alignment
								FROM player
								WHERE game_id = :game_id
									AND race_id = :race_id
									AND npc = \'FALSE\'
									AND experience > 0
								ORDER by experience DESC
								LIMIT :limit', [
				'game_id' => $db->escapeNumber($gameID),
				'race_id' => $db->escapeNumber($raceID),
				'limit' => MAX_COUNCIL_MEMBERS,
			]);
			foreach ($dbResult->records() as $dbRecord) {
				// Add this player to the council
				self::$COUNCILS[$gameID][$raceID][$i++] = $dbRecord->getInt('account_id');

				// Determine if this player is also the president
				if (self::$PRESIDENTS[$gameID][$raceID] === false) {
					if ($dbRecord->getInt('alignment') >= ALIGNMENT_PRESIDENT) {
						self::$PRESIDENTS[$gameID][$raceID] = $dbRecord->getInt('account_id');
					}
				}
			}
		}
		return self::$COUNCILS[$gameID][$raceID];
	}

	/**
	 * Returns the Account ID of the President for this race (or false if no President).
	 */
	public static function getPresidentID(int $gameID, int $raceID): int|false {
		self::getRaceCouncil($gameID, $raceID); // determines the president
		return self::$PRESIDENTS[$gameID][$raceID];
	}

	public static function isOnCouncil(int $gameID, int $raceID, int $accountID): bool {
		return in_array($accountID, self::getRaceCouncil($gameID, $raceID), true);
	}

}
