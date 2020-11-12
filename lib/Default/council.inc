<?php declare(strict_types=1);

function modifyRelations($race_id_1) {
	global $player, $db;

	// Process any votes that ended prior to the start of today
	$endtime = strtotime(date('Y-m-d'));

	MySqlDatabase::getInstance(true);

	$db->query('SELECT * FROM player_votes_relation
				WHERE time < '.$db->escapeNumber($endtime) . '
					AND game_id = '.$db->escapeNumber($player->getGameID()) . '
					AND race_id_1 = '.$db->escapeNumber($race_id_1));
	while ($db->nextRecord()) {
		$account_id = $db->getInt('account_id');
		$race_id_2 = $db->getInt('race_id_2');
		$action = $db->getField('action');

		if ($action == 'INC') {
			$relation_modifier = RELATIONS_VOTE_CHANGE;
		} else {
			$relation_modifier = -RELATIONS_VOTE_CHANGE;
		}

		$db2->query('SELECT * FROM race_has_relation ' .
					'WHERE race_id_1 = ' . $db2->escapeNumber($race_id_1) . '
						AND race_id_2 = ' . $db2->escapeNumber($race_id_2) . '
						AND game_id = ' . $db2->escapeNumber($player->getGameID()));
		if ($db2->nextRecord()) {
			$relation = $db2->getInt('relation') + $relation_modifier;
		}

		if ($relation < MIN_GLOBAL_RELATIONS) {
			$relation = MIN_GLOBAL_RELATIONS;
		} elseif ($relation > MAX_GLOBAL_RELATIONS) {
			$relation = MAX_GLOBAL_RELATIONS;
		}

		$db2->query('UPDATE race_has_relation
					SET relation = ' . $db2->escapeNumber($relation) . '
					WHERE game_id = '.$db2->escapeNumber($player->getGameID()) . '
						AND (
								race_id_1 = '.$db2->escapeNumber($race_id_1) . '
								AND race_id_2 = '.$db2->escapeNumber($race_id_2) . '
							OR
								race_id_1 = '.$db2->escapeNumber($race_id_2) . '
								AND race_id_2 = '.$db2->escapeNumber($race_id_1) . '
						)');

		$db2->query('DELETE FROM player_votes_relation
					WHERE account_id = ' . $db2->escapeNumber($account_id) . '
						AND game_id = ' . $db2->escapeNumber($player->getGameID()));
	}

}

function checkPacts($race_id_1) {
	global $player, $db;

	MySqlDatabase::getInstance(true);

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
				foreach ($raceFedSectors as $raceID => $fedSectors) {
					$currentlyParkedAccountIDs[$raceID] = array(); //initialize
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
					$expireTime = TIME + TIME_FOR_WAR_VOTE_FED_SAFETY;
					$query = 'REPLACE INTO player_can_fed (account_id, game_id, race_id, expiry, allowed) VALUES ';
					foreach ($currentlyParkedAccountIDs as $raceID => $accountIDs) {
						if ($raceID == $race_id_1) {
							$message = 'We have declared war upon your race';
						} else {
							$message = 'Your race has declared war upon us';
						}
						$message .= ', you have ' . format_time(TIME_FOR_WAR_VOTE_FED_SAFETY) . ' to vacate our federal space, after that time you will no longer be protected (unless you have strong personal relations).';
						foreach ($accountIDs as $accountID) {
							$query .= '(' . $db2->escapeNumber($accountID) . ',' . $db2->escapeNumber($player->getGameID()) . ',' . $db2->escapeNumber($raceID) . ',' . $db2->escapeNumber($expireTime) . ',' . $db2->escapeBoolean(true) . '),';
							SmrPlayer::sendMessageFromRace($raceID, $player->getGameID(), $accountID, $message, $expireTime);
						}
					}
					$db2->query(substr($query, 0, -1));
				}

				$db2->query('UPDATE race_has_relation
							SET relation = LEAST(relation,' . $db2->escapeNumber(RELATIONS_VOTE_WAR) . ')
							WHERE game_id = '.$db2->escapeNumber($player->getGameID()) . '
								AND (
										race_id_1 = '.$db2->escapeNumber($race_id_1) . '
										AND race_id_2 = '.$db2->escapeNumber($race_id_2) . '
									OR
										race_id_1 = '.$db2->escapeNumber($race_id_2) . '
										AND race_id_2 = '.$db2->escapeNumber($race_id_1) . '
								)');

				// get news message
				$news = 'The [race=' . $race_id_1 . '] have declared <span class="red">WAR</span> on the [race=' . $race_id_2 . ']';
				$db2->query('INSERT INTO news (game_id, time, news_message) VALUES ' .
							'(' . $db2->escapeNumber($player->getGameID()) . ', ' . $db2->escapeNumber(TIME) . ', ' . $db2->escapeString($news) . ')');
			} elseif ($type == 'PEACE') {
				// get 'yes' votes
				$db2->query('SELECT * FROM player_votes_pact
							WHERE game_id = '.$db2->escapeNumber($player->getGameID()) . '
								AND race_id_1 = '.$db2->escapeNumber($race_id_2) . '
								AND race_id_2 = '.$db2->escapeNumber($race_id_1) . '
								AND vote = \'YES\'');
				$rev_yes_votes = $db2->getNumRows();

				// get 'no' votes
				$db2->query('SELECT * FROM player_votes_pact
							WHERE game_id = '.$db2->escapeNumber($player->getGameID()) . '
								AND race_id_1 = '.$db2->escapeNumber($race_id_2) . '
								AND race_id_2 = '.$db2->escapeNumber($race_id_1) . '
								AND vote = \'NO\'');
				$rev_no_votes = $db2->getNumRows();

				// more yes than no?
				if ($rev_yes_votes > $rev_no_votes) {
					$db2->query('UPDATE race_has_relation
								SET relation = GREATEST(relation,'.$db2->escapeNumber(RELATIONS_VOTE_PEACE) . ')
								WHERE game_id = '.$db2->escapeNumber($player->getGameID()) . '
									AND (
											race_id_1 = '.$db2->escapeNumber($race_id_1) . '
											AND race_id_2 = '.$db2->escapeNumber($race_id_2) . '
										OR
											race_id_1 = '.$db2->escapeNumber($race_id_2) . '
											AND race_id_2 = '.$db2->escapeNumber($race_id_1) . '
									)');

					//get news message
					$news = 'The [race=' . $race_id_1 . '] have signed a <span class="dgreen">PEACE</span> treaty with the [race=' . $race_id_2 . ']';
					$db2->query('INSERT INTO news (game_id, time, news_message) VALUES
								('.$db2->escapeNumber($player->getGameID()) . ', ' . $db2->escapeNumber(TIME) . ', ' . $db2->escapeString($news) . ')');
				}
			}
		}

		// delete vote and user votes
		$db2->query('DELETE FROM race_has_voting
					WHERE game_id = '.$db2->escapeNumber($player->getGameID()) . '
						AND race_id_1 = '.$db2->escapeNumber($race_id_1) . '
						AND race_id_2 = '.$db2->escapeNumber($race_id_2));
		$db2->query('DELETE FROM player_votes_pact
					WHERE game_id = '.$db2->escapeNumber($player->getGameID()) . '
						AND race_id_1 = '.$db2->escapeNumber($race_id_1) . '
						AND race_id_2 = '.$db2->escapeNumber($race_id_2));
		// delete vote and user votes
		$db2->query('DELETE FROM race_has_voting
					WHERE game_id = '.$db2->escapeNumber($player->getGameID()) . '
						AND race_id_1 = '.$db2->escapeNumber($race_id_2) . '
						AND race_id_2 = '.$db2->escapeNumber($race_id_1));
		$db2->query('DELETE FROM player_votes_pact
					WHERE game_id = '.$db2->escapeNumber($player->getGameID()) . '
						AND race_id_1 = '.$db2->escapeNumber($race_id_2) . '
						AND race_id_2 = '.$db2->escapeNumber($race_id_1));
	}
}
