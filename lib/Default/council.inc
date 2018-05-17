<?php
require_once(get_file_loc('SmrPlayer.class.inc'));
class Council {
	protected static $COUNCILS = array();
	protected static $PRESIDENTS = array();
	protected static $db = null;

	private function __construct() {
	}

	protected static function initialiseDatabase() {
		if(self::$db==null)
			self::$db = new SmrMySqlDatabase();
	}

	/**
	 * Returns an array of Account ID's of the Council for this race.
	 */
	public static function &getRaceCouncil($gameID,$raceID) {
		if (!isset(self::$COUNCILS[$gameID][$raceID])) {
			self::initialiseDatabase();
			self::$COUNCILS[$gameID][$raceID] = array();

			$i=1;
			self::$db->query('SELECT account_id
								FROM player
								WHERE game_id = ' . self::$db->escapeNumber($gameID) . '
									AND race_id = ' . self::$db->escapeNumber($raceID) . '
									AND npc = \'FALSE\'
								ORDER by experience DESC
								LIMIT ' . MAX_COUNCIL_MEMBERS);
			while(self::$db->nextRecord()) {
				self::$COUNCILS[$gameID][$raceID][$i] = self::$db->getField('account_id');
				$i++;
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
			self::$db->query('SELECT account_id FROM player
			                  WHERE game_id = ' . self::$db->escapeNumber($gameID) . '
			                    AND race_id = ' . self::$db->escapeNumber($raceID) . '
			                    AND alignment >= ' . self::$db->escapeNumber(ALIGNMENT_PRESIDENT) . '
			                    AND npc = \'FALSE\'
			                  ORDER BY experience DESC
			                  LIMIT 1');
			self::$PRESIDENTS[$gameID][$raceID] = self::$db->nextRecord() ? self::$db->getInt('account_id') : false;
		}
		return self::$PRESIDENTS[$gameID][$raceID];
	}

	public static function isOnCouncil($gameID,$raceID,$accountID) {
		return in_array($accountID,self::getRaceCouncil($gameID,$raceID));
	}
}

function modifyRelations($race_id_1) {
	global $player, $db;

	$endtime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

	$db2 = new SmrMySqlDatabase();

	$db->query('SELECT * FROM player_votes_relation
				WHERE time < '.$db->escapeNumber($endtime).'
					AND game_id = '.$db->escapeNumber($player->getGameID()).'
					AND race_id_1 = '.$db->escapeNumber($race_id_1));
	while ($db->nextRecord()) {
		$account_id	= $db->getInt('account_id');
		$race_id_2	= $db->getInt('race_id_2');
		$action		= $db->getField('action');

		if ($action == 'INC')
			$relation_modifier = 10;
		else
			$relation_modifier = -10;

		$db2->query('SELECT * FROM race_has_relation ' .
					'WHERE race_id_1 = ' . $db2->escapeNumber($race_id_1) . '
						AND race_id_2 = ' . $db2->escapeNumber($race_id_2) . '
						AND game_id = ' . $db2->escapeNumber($player->getGameID()));
		if ($db2->nextRecord())
			$relation = $db2->getField('relation') + $relation_modifier;

		if ($relation < MIN_GLOBAL_RELATIONS) {
			$relation = MIN_GLOBAL_RELATIONS;
		} elseif ($relation > MAX_GLOBAL_RELATIONS) {
			$relation = MAX_GLOBAL_RELATIONS;
		}

		$db2->query('UPDATE race_has_relation
					SET relation = ' . $db2->escapeNumber($relation) . '
					WHERE game_id = '.$db2->escapeNumber($player->getGameID()).'
						AND (
								race_id_1 = '.$db2->escapeNumber($race_id_1).'
								AND race_id_2 = '.$db2->escapeNumber($race_id_2).'
							OR
								race_id_1 = '.$db2->escapeNumber($race_id_2).'
								AND race_id_2 = '.$db2->escapeNumber($race_id_1).'
						)');

		$db2->query('DELETE FROM player_votes_relation
					WHERE account_id = ' . $db2->escapeNumber($account_id) . '
						AND game_id = ' . $db2->escapeNumber($player->getGameID()));
	}

}

function checkPacts($race_id_1) {
	global $player, $db;

	$db2 = new SmrMySqlDatabase();

	$db->query('SELECT * FROM race_has_voting
				WHERE end_time < ' . $db->escapeNumber(TIME) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND race_id_1 = ' . $db->escapeNumber($race_id_1));
	while ($db->nextRecord()) {
		$race_id_2 = $db->getInt('race_id_2');
		$type = $db->getField('type');

		// get 'yes' votes
		$db2->query('SELECT * FROM player_votes_pact
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND race_id_1 = ' . $db->escapeNumber($race_id_1) . '
						AND race_id_2 = ' . $db->escapeNumber($race_id_2) . '
						AND vote = \'YES\'');
		$yes_votes = $db2->getNumRows();

		// get 'no' votes
		$db2->query('SELECT * FROM player_votes_pact
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND race_id_1 = ' . $db->escapeNumber($race_id_1) . '
						AND race_id_2 = ' . $db->escapeNumber($race_id_2) . '
						AND vote = \'NO\'');
		$no_votes = $db2->getNumRows();


		// more yes than no?
		if ($yes_votes > $no_votes) {
			if ($type == 'WAR') {
				$currentlyParkedAccountIDs = array();
				$raceFedSectors = array(
										$race_id_1 => SmrSector::getLocationSectors($player->getGameID(), LOCATION_GROUP_RACIAL_BEACONS + $race_id_1),
										$race_id_2 => SmrSector::getLocationSectors($player->getGameID(), LOCATION_GROUP_RACIAL_BEACONS + $race_id_2)
									);
				foreach($raceFedSectors as $raceID => $fedSectors) {
					$currentlyParkedAccountIDs[$raceID] = array();  //initialize
					$otherRaceID = $raceID == $race_id_1 ? $race_id_2 : $race_id_1;
					foreach($fedSectors as $fedSector) {
						$sectorPlayers = $fedSector->getPlayers();
						foreach($sectorPlayers as $sectorPlayer) {
							if($sectorPlayer->getRaceID() == $otherRaceID && $sectorPlayer->canBeProtectedByRace($raceID)) {
								$currentlyParkedAccountIDs[$raceID][] = $sectorPlayer->getAccountID();
							}
						}
					}
				}

				if( count($currentlyParkedAccountIDs[$race_id_1]) + count($currentlyParkedAccountIDs[$race_id_2]) > 0 ) {
					$expireTime = TIME + TIME_FOR_WAR_VOTE_FED_SAFETY;
					$query = 'REPLACE INTO player_can_fed (account_id, game_id, race_id, expiry, allowed) VALUES ';
					foreach($currentlyParkedAccountIDs as $raceID => $accountIDs) {
						if($raceID == $race_id_1)
							$message = 'We have declared war upon your race';
						else
							$message = 'Your race has declared war upon us';
						$message .= ', you have '.format_time(TIME_FOR_WAR_VOTE_FED_SAFETY).' to vacate our federal space, after that time you will no longer be protected (unless you have strong personal relations).';
						foreach($accountIDs as $accountID) {
							$query .= '('.$db2->escapeNumber($accountID).','.$db2->escapeNumber($player->getGameID()).','.$db2->escapeNumber($raceID).','.$db2->escapeNumber($expireTime).','.$db2->escapeBoolean(true).'),';
							SmrPlayer::sendMessageFromRace($raceID, $player->getGameID(), $accountID, $message, $expireTime);
						}
					}
					$db2->query(substr($query,0,-1));
				}

				$db2->query('UPDATE race_has_relation
							SET relation = LEAST(relation,-400)
							WHERE game_id = '.$db2->escapeNumber($player->getGameID()).'
								AND (
										race_id_1 = '.$db2->escapeNumber($race_id_1).'
										AND race_id_2 = '.$db2->escapeNumber($race_id_2).'
									OR
										race_id_1 = '.$db2->escapeNumber($race_id_2).'
										AND race_id_2 = '.$db2->escapeNumber($race_id_1).'
								)');

				// get news message
				$news = 'The [race='.$race_id_1.'] have declared <span class="red">WAR</span> on the [race='.$race_id_2.']';
				$db2->query('INSERT INTO news (game_id, time, news_message) VALUES ' .
							'('.$db2->escapeNumber($player->getGameID()).', '.$db2->escapeNumber(TIME).', ' . $db2->escape_string($news, false) . ')');
			}
			elseif ($type == 'PEACE') {
				// get 'yes' votes
				$db2->query('SELECT * FROM player_votes_pact
							WHERE game_id = '.$db2->escapeNumber($player->getGameID()).'
								AND race_id_1 = '.$db2->escapeNumber($race_id_2).'
								AND race_id_2 = '.$db2->escapeNumber($race_id_1).'
								AND vote = \'YES\'');
				$rev_yes_votes = $db2->getNumRows();

				// get 'no' votes
				$db2->query('SELECT * FROM player_votes_pact
							WHERE game_id = '.$db2->escapeNumber($player->getGameID()).'
								AND race_id_1 = '.$db2->escapeNumber($race_id_2).'
								AND race_id_2 = '.$db2->escapeNumber($race_id_1).'
								AND vote = \'NO\'');
				$rev_no_votes = $db2->getNumRows();

				// more yes than no?
				if ($rev_yes_votes > $rev_no_votes) {
					$db2->query('UPDATE race_has_relation
								SET relation = GREATEST(relation,'.$db2->escapeNumber(RELATIONS_PEACE).')
								WHERE game_id = '.$db2->escapeNumber($player->getGameID()).'
									AND (
											race_id_1 = '.$db2->escapeNumber($race_id_1).'
											AND race_id_2 = '.$db2->escapeNumber($race_id_2).'
										OR
											race_id_1 = '.$db2->escapeNumber($race_id_2).'
											AND race_id_2 = '.$db2->escapeNumber($race_id_1).'
									)');

					//get news message
					$news = 'The [race='.$race_id_1.'] have signed a <span class="dgreen">PEACE</span> treaty with the [race='.$race_id_2.']';
					$db2->query('INSERT INTO news (game_id, time, news_message) VALUES
								('.$db2->escapeNumber($player->getGameID()).', '.$db2->escapeNumber(TIME).', ' . $db2->escape_string($news, false) . ')');
				}
			}
		}

		// delete vote and user votes
		$db2->query('DELETE FROM race_has_voting
					WHERE game_id = '.$db2->escapeNumber($player->getGameID()).'
						AND race_id_1 = '.$db2->escapeNumber($race_id_1).'
						AND race_id_2 = '.$db2->escapeNumber($race_id_2));
		$db2->query('DELETE FROM player_votes_pact
					WHERE game_id = '.$db2->escapeNumber($player->getGameID()).'
						AND race_id_1 = '.$db2->escapeNumber($race_id_1).'
						AND race_id_2 = '.$db2->escapeNumber($race_id_2));
		// delete vote and user votes
		$db2->query('DELETE FROM race_has_voting
					WHERE game_id = '.$db2->escapeNumber($player->getGameID()).'
						AND race_id_1 = '.$db2->escapeNumber($race_id_2).'
						AND race_id_2 = '.$db2->escapeNumber($race_id_1));
		$db2->query('DELETE FROM player_votes_pact
					WHERE game_id = '.$db2->escapeNumber($player->getGameID()).'
						AND race_id_1 = '.$db2->escapeNumber($race_id_2).'
						AND race_id_2 = '.$db2->escapeNumber($race_id_1));
	}
}
