<?php declare(strict_types=1);

namespace Smr;

use SmrPlayer;
use SmrSector;

/**
 * Collection of functions to help process council voting.
 */
class CouncilVoting {

	public static function modifyRelations(int $race_id_1, int $gameID) : void {

		// Process any votes that ended prior to the start of today
		$endtime = strtotime(date('Y-m-d'));

		$db = Database::getInstance();

		$dbResult = $db->read('SELECT * FROM player_votes_relation
				WHERE time < '.$db->escapeNumber($endtime) . '
					AND game_id = '.$db->escapeNumber($gameID) . '
					AND race_id_1 = '.$db->escapeNumber($race_id_1));
		foreach ($dbResult->records() as $dbRecord) {
			$account_id = $dbRecord->getInt('account_id');
			$race_id_2 = $dbRecord->getInt('race_id_2');
			$action = $dbRecord->getField('action');

			if ($action == 'INC') {
				$relation_modifier = RELATIONS_VOTE_CHANGE;
			} else {
				$relation_modifier = -RELATIONS_VOTE_CHANGE;
			}

			$dbResult2 = $db->read('SELECT * FROM race_has_relation ' .
					'WHERE race_id_1 = ' . $db->escapeNumber($race_id_1) . '
						AND race_id_2 = ' . $db->escapeNumber($race_id_2) . '
						AND game_id = ' . $db->escapeNumber($gameID));
			$relation = $dbResult2->record()->getInt('relation') + $relation_modifier;

			if ($relation < MIN_GLOBAL_RELATIONS) {
				$relation = MIN_GLOBAL_RELATIONS;
			} elseif ($relation > MAX_GLOBAL_RELATIONS) {
				$relation = MAX_GLOBAL_RELATIONS;
			}

			$db->write('UPDATE race_has_relation
					SET relation = ' . $db->escapeNumber($relation) . '
					WHERE game_id = '.$db->escapeNumber($gameID) . '
						AND (
								race_id_1 = '.$db->escapeNumber($race_id_1) . '
								AND race_id_2 = '.$db->escapeNumber($race_id_2) . '
							OR
								race_id_1 = '.$db->escapeNumber($race_id_2) . '
								AND race_id_2 = '.$db->escapeNumber($race_id_1) . '
						)');

			$db->write('DELETE FROM player_votes_relation
					WHERE account_id = ' . $db->escapeNumber($account_id) . '
						AND game_id = ' . $db->escapeNumber($gameID));
		}
	}

	public static function checkPacts(int $race_id_1, int $gameID) : void {

		$db = Database::getInstance();

		$dbResult = $db->read('SELECT * FROM race_has_voting
				WHERE end_time < ' . $db->escapeNumber(Epoch::time()) . '
					AND game_id = ' . $db->escapeNumber($gameID) . '
					AND race_id_1 = ' . $db->escapeNumber($race_id_1));
		foreach ($dbResult->records() as $dbRecord) {
			$race_id_2 = $dbRecord->getInt('race_id_2');
			$type = $dbRecord->getField('type');

			// get 'yes' votes
			$dbResult2 = $db->read('SELECT 1 FROM player_votes_pact
					WHERE game_id = ' . $db->escapeNumber($gameID) . '
						AND race_id_1 = ' . $db->escapeNumber($race_id_1) . '
						AND race_id_2 = ' . $db->escapeNumber($race_id_2) . '
						AND vote = \'YES\'');
			$yes_votes = $dbResult2->getNumRecords();

			// get 'no' votes
			$dbResult2 = $db->read('SELECT 1 FROM player_votes_pact
					WHERE game_id = ' . $db->escapeNumber($gameID) . '
						AND race_id_1 = ' . $db->escapeNumber($race_id_1) . '
						AND race_id_2 = ' . $db->escapeNumber($race_id_2) . '
						AND vote = \'NO\'');
			$no_votes = $dbResult2->getNumRecords();


			// more yes than no?
			if ($yes_votes > $no_votes) {
				if ($type == 'WAR') {
					$currentlyParkedAccountIDs = [];
					$raceFedSectors = [
						$race_id_1 => SmrSector::getLocationSectors($gameID, LOCATION_GROUP_RACIAL_BEACONS + $race_id_1),
						$race_id_2 => SmrSector::getLocationSectors($gameID, LOCATION_GROUP_RACIAL_BEACONS + $race_id_2)
					];
					foreach ($raceFedSectors as $raceID => $fedSectors) {
						$currentlyParkedAccountIDs[$raceID] = []; //initialize
						$otherRaceID = $raceID == $race_id_1 ? $race_id_2 : $race_id_1;
						foreach ($fedSectors as $fedSector) {
							$sectorPlayers = $fedSector->getPlayers();
							foreach ($sectorPlayers as $sectorPlayer) {
								if ($sectorPlayer->getRaceID() == $otherRaceID && $sectorPlayer->canBeProtectedByRace($raceID)) {
									$currentlyParkedAccountIDs[$raceID][] = $sectorPlayer->getAccountID();
								}
							}
						}
					}

					if (count($currentlyParkedAccountIDs[$race_id_1]) + count($currentlyParkedAccountIDs[$race_id_2]) > 0) {
						$expireTime = Epoch::time() + TIME_FOR_WAR_VOTE_FED_SAFETY;
						$query = 'REPLACE INTO player_can_fed (account_id, game_id, race_id, expiry, allowed) VALUES ';
						foreach ($currentlyParkedAccountIDs as $raceID => $accountIDs) {
							if ($raceID == $race_id_1) {
								$message = 'We have declared war upon your race';
							} else {
								$message = 'Your race has declared war upon us';
							}
							$message .= ', you have ' . format_time(TIME_FOR_WAR_VOTE_FED_SAFETY) . ' to vacate our federal space, after that time you will no longer be protected (unless you have strong personal relations).';
							foreach ($accountIDs as $accountID) {
								$query .= '(' . $db->escapeNumber($accountID) . ',' . $db->escapeNumber($gameID) . ',' . $db->escapeNumber($raceID) . ',' . $db->escapeNumber($expireTime) . ',' . $db->escapeBoolean(true) . '),';
								SmrPlayer::sendMessageFromRace($raceID, $gameID, $accountID, $message, $expireTime);
							}
						}
						$db->write(substr($query, 0, -1));
					}

					$db->write('UPDATE race_has_relation
							SET relation = LEAST(relation,' . $db->escapeNumber(RELATIONS_VOTE_WAR) . ')
							WHERE game_id = ' . $db->escapeNumber($gameID) . '
								AND (
										race_id_1 = ' . $db->escapeNumber($race_id_1) . '
										AND race_id_2 = ' . $db->escapeNumber($race_id_2) . '
									OR
										race_id_1 = ' . $db->escapeNumber($race_id_2) . '
										AND race_id_2 = ' . $db->escapeNumber($race_id_1) . '
								)');

					// get news message
					$news = 'The [race=' . $race_id_1 . '] have declared <span class="red">WAR</span> on the [race=' . $race_id_2 . ']';
					$db->insert('news', [
						'game_id' => $db->escapeNumber($gameID),
						'time' => $db->escapeNumber(Epoch::time()),
						'news_message' => $db->escapeString($news),
					]);
				} elseif ($type == 'PEACE') {
					// get 'yes' votes
					$dbResult2 = $db->read('SELECT 1 FROM player_votes_pact
							WHERE game_id = ' . $db->escapeNumber($gameID) . '
								AND race_id_1 = ' . $db->escapeNumber($race_id_2) . '
								AND race_id_2 = ' . $db->escapeNumber($race_id_1) . '
								AND vote = \'YES\'');
					$rev_yes_votes = $dbResult2->getNumRecords();

					// get 'no' votes
					$dbResult2 = $db->read('SELECT 1 FROM player_votes_pact
							WHERE game_id = ' . $db->escapeNumber($gameID) . '
								AND race_id_1 = ' . $db->escapeNumber($race_id_2) . '
								AND race_id_2 = ' . $db->escapeNumber($race_id_1) . '
								AND vote = \'NO\'');
					$rev_no_votes = $dbResult2->getNumRecords();

					// more yes than no?
					if ($rev_yes_votes > $rev_no_votes) {
						$db->write('UPDATE race_has_relation
								SET relation = GREATEST(relation,' . $db->escapeNumber(RELATIONS_VOTE_PEACE) . ')
								WHERE game_id = ' . $db->escapeNumber($gameID) . '
									AND (
											race_id_1 = ' . $db->escapeNumber($race_id_1) . '
											AND race_id_2 = ' . $db->escapeNumber($race_id_2) . '
										OR
											race_id_1 = ' . $db->escapeNumber($race_id_2) . '
											AND race_id_2 = ' . $db->escapeNumber($race_id_1) . '
									)');

						//get news message
						$news = 'The [race=' . $race_id_1 . '] have signed a <span class="dgreen">PEACE</span> treaty with the [race=' . $race_id_2 . ']';
						$db->insert('news', [
							'game_id' => $db->escapeNumber($gameID),
							'time' => $db->escapeNumber(Epoch::time()),
							'news_message' => $db->escapeString($news),
						]);
					}
				}
			}

			// delete vote and user votes
			$db->write('DELETE FROM race_has_voting
					WHERE game_id = ' . $db->escapeNumber($gameID) . '
						AND race_id_1 = ' . $db->escapeNumber($race_id_1) . '
						AND race_id_2 = ' . $db->escapeNumber($race_id_2));
			$db->write('DELETE FROM player_votes_pact
					WHERE game_id = ' . $db->escapeNumber($gameID) . '
						AND race_id_1 = ' . $db->escapeNumber($race_id_1) . '
						AND race_id_2 = ' . $db->escapeNumber($race_id_2));
			// delete vote and user votes
			$db->write('DELETE FROM race_has_voting
					WHERE game_id = ' . $db->escapeNumber($gameID) . '
						AND race_id_1 = ' . $db->escapeNumber($race_id_2) . '
						AND race_id_2 = ' . $db->escapeNumber($race_id_1));
			$db->write('DELETE FROM player_votes_pact
					WHERE game_id = ' . $db->escapeNumber($gameID) . '
						AND race_id_1 = ' . $db->escapeNumber($race_id_2) . '
						AND race_id_2 = ' . $db->escapeNumber($race_id_1));
		}
	}

}
