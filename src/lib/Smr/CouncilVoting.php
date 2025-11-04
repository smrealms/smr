<?php declare(strict_types=1);

namespace Smr;

use Exception;

/**
 * Collection of functions to help process council voting.
 */
class CouncilVoting {

	public static function modifyRelations(int $race_id_1, int $gameID): void {

		// Process any votes that ended prior to the start of today
		$endtime = strtotime(date('Y-m-d'));
		if ($endtime === false) {
			throw new Exception('Failed to convert date to time');
		}

		$db = Database::getInstance();

		$dbResult = $db->read('SELECT * FROM player_votes_relation
				WHERE time < :end_time
					AND game_id = :game_id
					AND race_id_1 = :race_id_1', [
			'end_time' => $db->escapeNumber($endtime),
			'game_id' => $db->escapeNumber($gameID),
			'race_id_1' => $db->escapeNumber($race_id_1),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$account_id = $dbRecord->getInt('account_id');
			$race_id_2 = $dbRecord->getInt('race_id_2');
			$action = $dbRecord->getString('action');

			if ($action === 'INC') {
				$relation_modifier = RELATIONS_VOTE_CHANGE;
			} else {
				$relation_modifier = -RELATIONS_VOTE_CHANGE;
			}

			$dbResult2 = $db->read('SELECT * FROM race_has_relation
					WHERE race_id_1 = :race_id_1
						AND race_id_2 = :race_id_2
						AND game_id = :game_id', [
				'race_id_1' => $db->escapeNumber($race_id_1),
				'race_id_2' => $db->escapeNumber($race_id_2),
				'game_id' => $db->escapeNumber($gameID),
			]);
			$relation = $dbResult2->record()->getInt('relation') + $relation_modifier;

			if ($relation < MIN_GLOBAL_RELATIONS) {
				$relation = MIN_GLOBAL_RELATIONS;
			} elseif ($relation > MAX_GLOBAL_RELATIONS) {
				$relation = MAX_GLOBAL_RELATIONS;
			}

			$db->write('UPDATE race_has_relation
					SET relation = :relation
					WHERE game_id = :game_id
						AND (
								race_id_1 = :race_id_1
								AND race_id_2 = :race_id_2
							OR
								race_id_1 = :race_id_2
								AND race_id_2 = :race_id_1
						)', [
				'relation' => $db->escapeNumber($relation),
				'game_id' => $db->escapeNumber($gameID),
				'race_id_1' => $db->escapeNumber($race_id_1),
				'race_id_2' => $db->escapeNumber($race_id_2),
			]);

			$db->delete('player_votes_relation', [
				'account_id' => $account_id,
				'game_id' => $gameID,
			]);
		}
	}

	/**
	 * @return array{YES: int, NO: int} Number of yes and no votes
	 */
	public static function countVotes(int $race_id_1, int $race_id_2, int $gameID): array {
		$db = Database::getInstance();
		$results = ['YES' => 0, 'NO' => 0];
		foreach (array_keys($results) as $vote) {
			$dbResult = $db->read('SELECT count(*) FROM player_votes_pact
					WHERE game_id = :game_id
						AND race_id_1 = :race_id_1
						AND race_id_2 = :race_id_2
						AND vote = :vote', [
				'game_id' => $db->escapeNumber($gameID),
				'race_id_1' => $db->escapeNumber($race_id_1),
				'race_id_2' => $db->escapeNumber($race_id_2),
				'vote' => $db->escapeString($vote),
			]);
			$results[$vote] = $dbResult->record()->getInt('count(*)');
		}
		return $results;
	}

	public static function deleteVote(int $race_id_1, int $race_id_2, int $gameID, string $type): void {
		$db = Database::getInstance();

		// Delete vote for current race
		$sqlParams = [
			'game_id' => $db->escapeNumber($gameID),
			'race_id_1' => $db->escapeNumber($race_id_1),
			'race_id_2' => $db->escapeNumber($race_id_2),
		];
		$db->delete('race_has_voting', $sqlParams);
		$db->delete('player_votes_pact', $sqlParams);

		// Only delete vote for other race if it's a peace vote
		if ($type === 'PEACE') {
			$sqlParams2 = [
				'game_id' => $db->escapeNumber($gameID),
				'race_id_1' => $db->escapeNumber($race_id_2),
				'race_id_2' => $db->escapeNumber($race_id_1),
			];
			$db->delete('race_has_voting', $sqlParams2);
			$db->delete('player_votes_pact', $sqlParams2);
		}
	}

	public static function checkPacts(int $race_id_1, int $gameID): void {

		$db = Database::getInstance();

		$dbResult = $db->read('SELECT * FROM race_has_voting
				WHERE end_time < :now
					AND game_id = :game_id
					AND race_id_1 = :race_id_1', [
			'now' => $db->escapeNumber(Epoch::time()),
			'game_id' => $db->escapeNumber($gameID),
			'race_id_1' => $db->escapeNumber($race_id_1),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$race_id_2 = $dbRecord->getInt('race_id_2');
			$type = $dbRecord->getString('type');

			$votes = self::countVotes(
				race_id_1: $race_id_1,
				race_id_2: $race_id_2,
				gameID: $gameID,
			);

			// more yes than no?
			if ($votes['YES'] > $votes['NO']) {
				if ($type === 'WAR') {
					$currentlyParkedAccountIDs = [];
					$raceFedSectors = [
						$race_id_1 => Sector::getLocationSectors($gameID, LOCATION_GROUP_RACIAL_BEACONS + $race_id_1),
						$race_id_2 => Sector::getLocationSectors($gameID, LOCATION_GROUP_RACIAL_BEACONS + $race_id_2),
					];
					foreach ($raceFedSectors as $raceID => $fedSectors) {
						$currentlyParkedAccountIDs[$raceID] = []; //initialize
						$otherRaceID = $raceID === $race_id_1 ? $race_id_2 : $race_id_1;
						foreach ($fedSectors as $fedSector) {
							$sectorPlayers = $fedSector->getPlayers();
							foreach ($sectorPlayers as $sectorPlayer) {
								if ($sectorPlayer->getRaceID() === $otherRaceID && $sectorPlayer->canBeProtectedByRace($raceID)) {
									$currentlyParkedAccountIDs[$raceID][] = $sectorPlayer->getAccountID();
								}
							}
						}
					}

					if (count($currentlyParkedAccountIDs[$race_id_1]) + count($currentlyParkedAccountIDs[$race_id_2]) > 0) {
						$expireTime = Epoch::time() + TIME_FOR_WAR_VOTE_FED_SAFETY;
						$query = 'REPLACE INTO player_can_fed (account_id, game_id, race_id, expiry, allowed) VALUES ';
						foreach ($currentlyParkedAccountIDs as $raceID => $accountIDs) {
							if ($raceID === $race_id_1) {
								$message = 'We have declared war upon your race';
							} else {
								$message = 'Your race has declared war upon us';
							}
							$message .= ', you have ' . format_time(TIME_FOR_WAR_VOTE_FED_SAFETY) . ' to vacate our federal space, after that time you will no longer be protected (unless you have strong personal relations).';
							foreach ($accountIDs as $accountID) {
								$query .= '(' . $db->escapeNumber($accountID) . ',' . $db->escapeNumber($gameID) . ',' . $db->escapeNumber($raceID) . ',' . $db->escapeNumber($expireTime) . ',' . $db->escapeBoolean(true) . '),';
								Player::sendMessageFromRace($raceID, $gameID, $accountID, $message, $expireTime);
							}
						}
						$db->write(substr($query, 0, -1));
					}

					$db->write('UPDATE race_has_relation
							SET relation = LEAST(relation, :relations_war)
							WHERE game_id = :game_id
								AND (
										race_id_1 = :race_id_1
										AND race_id_2 = :race_id_2
									OR
										race_id_1 = :race_id_2
										AND race_id_2 = :race_id_1
								)', [
						'relations_war' => $db->escapeNumber(RELATIONS_VOTE_WAR),
						'game_id' => $db->escapeNumber($gameID),
						'race_id_1' => $db->escapeNumber($race_id_1),
						'race_id_2' => $db->escapeNumber($race_id_2),
					]);

					// get news message
					$news = 'The [race=' . $race_id_1 . '] have declared <span class="red">WAR</span> on the [race=' . $race_id_2 . ']';
					$db->insert('news', [
						'game_id' => $gameID,
						'time' => Epoch::time(),
						'news_message' => $news,
					]);
				} elseif ($type === 'PEACE') {
					$rev_votes = self::countVotes(
						race_id_1: $race_id_2,
						race_id_2: $race_id_1,
						gameID: $gameID,
					);

					// more yes than no?
					if ($rev_votes['YES'] > $rev_votes['NO']) {
						$db->write('UPDATE race_has_relation
								SET relation = GREATEST(relation, :relations_peace)
								WHERE game_id = :game_id
									AND (
											race_id_1 = :race_id_1
											AND race_id_2 = :race_id_2
										OR
											race_id_1 = :race_id_2
											AND race_id_2 = :race_id_1
									)', [
							'relations_peace' => $db->escapeNumber(RELATIONS_VOTE_PEACE),
							'game_id' => $db->escapeNumber($gameID),
							'race_id_1' => $db->escapeNumber($race_id_1),
							'race_id_2' => $db->escapeNumber($race_id_2),
						]);

						//get news message
						$news = 'The [race=' . $race_id_1 . '] have signed a <span class="dgreen">PEACE</span> treaty with the [race=' . $race_id_2 . ']';
						$db->insert('news', [
							'game_id' => $gameID,
							'time' => Epoch::time(),
							'news_message' => $news,
						]);
					}
				}
			}

			// delete vote and user votes
			self::deleteVote(
				race_id_1: $race_id_1,
				race_id_2: $race_id_2,
				gameID: $gameID,
				type: $type,
			);
		}
	}

}
