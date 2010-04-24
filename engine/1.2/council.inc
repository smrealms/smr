<?php

// determine if we are on this council
function onCouncil($race_id) {

	global $player, $db;

	$db->query("SELECT * FROM player " .
			   "WHERE game_id = $player->game_id AND " .
					 "race_id = $race_id " .
			   "ORDER by experience DESC " .
			   "LIMIT 20");
	while ($db->next_record())
		if ($db->f("account_id") == $player->account_id)
			return 1;

	return 0;

}

function getPresident($race_id) {

	global $player, $db;

	$db->query("SELECT * FROM player_is_president " .
		   "WHERE game_id = $player->game_id AND " .
				 "race_id = $race_id");
	if ($db->next_record())
		$president = new SMR_PLAYER($db->f("account_id"), $player->game_id);

	$pres_xp = $president->experience;
	if (empty($pres_xp))
		$pres_xp = 0;

	// check if there is another player with a higher xp and deputy
	$db->query("SELECT * FROM player " .
			   "WHERE game_id = $player->game_id AND " .
					 "race_id = $race_id AND " .
					 "experience > $pres_xp AND " .
					 "alignment >= 150 " .
			   "ORDER by experience DESC " .
			   "LIMIT 1");

	if ($db->next_record()) {

		$president = new SMR_PLAYER($db->f("account_id"), $player->game_id);

		$db->query("REPLACE INTO player_is_president " .
				   "(account_id, game_id, race_id) " .
				   "VALUES($president->account_id, $player->game_id, $race_id)");

	}

	// de elect evil presidents
	if ($president->account_id > 0 && $president->alignment < 150) {

		$db->query("DELETE FROM player_is_president " .
			   "WHERE game_id = $player->game_id AND " .
					 "race_id = $race_id");

		unset($president);

	}

	return $president;

}

function modifyRelations($race_id_1) {

	global $player, $db;

	$endtime = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

	$db2 = new SmrMySqlDatabase();

	$db->query("SELECT * FROM player_votes_relation " .
			   "WHERE time < $endtime AND " .
					 "game_id = $player->game_id AND " .
					 "race_id_1 = $race_id_1");
	while ($db->next_record()) {

		$account_id	= $db->f("account_id");
		$race_id_2	= $db->f("race_id_2");
		$action		= $db->f("action");

		if ($action == "INC")
			$relation_modifier = 10;
		else
			$relation_modifier = -10;

		$db2->query("SELECT * FROM race_has_relation " .
					"WHERE race_id_1 = $race_id_1 AND " .
						  "race_id_2 = $race_id_2 AND " .
						  "game_id = $player->game_id");
		if ($db2->next_record())
			$relation = $db2->f("relation") + $relation_modifier;

		if ($relation < -500)
			$relation = -500;

		if ($relation > 500)
			$relation = 500;

		$db2->query("UPDATE race_has_relation " .
					"SET relation = $relation " .
					"WHERE race_id_1 = $race_id_1 AND " .
						  "race_id_2 = $race_id_2 AND " .
						  "game_id = $player->game_id");

		$db2->query("DELETE FROM player_votes_relation " .
					"WHERE account_id = $account_id AND " .
						  "game_id = $player->game_id");

	}

}

function checkPacts($race_id_1) {

	global $player, $db;

	$endtime = time();
	$db2 = new SmrMySqlDatabase();

	$db->query("SELECT * FROM race_has_voting " .
			   "WHERE end_time < $endtime AND " .
					 "game_id = $player->game_id AND " .
					 "race_id_1 = $race_id_1");
	while ($db->next_record()) {

		$race_id_2	= $db->f("race_id_2");
		$type		= $db->f("type");

		// get 'yes' votes
		$db2->query("SELECT * FROM player_votes_pact " .
					"WHERE game_id = $player->game_id AND " .
						  "race_id_1 = $race_id_1 AND " .
						  "race_id_2 = $race_id_2 AND " .
						  "vote = 'YES'");
		$yes_votes = $db2->nf();

		// get 'no' votes
		$db2->query("SELECT * FROM player_votes_pact " .
					"WHERE game_id = $player->game_id AND " .
						  "race_id_1 = $race_id_1 AND " .
						  "race_id_2 = $race_id_2 AND " .
						  "vote = 'NO'");
		$no_votes = $db2->nf();

		if ($type == "WAR") {

			// more yes than no?
			if ($yes_votes > $no_votes) {

				$db2->query("UPDATE race_has_relation " .
							"SET relation = -400 " .
							"WHERE game_id = $player->game_id AND " .
								  "(race_id_1 = $race_id_1 AND race_id_2 = $race_id_2 OR " .
								  "race_id_1 = $race_id_2 AND race_id_2 = $race_id_1)");

				$time = time();
				//get races
				$db2->query("SELECT * FROM race WHERE race_id = $race_id_1");
				if ($db2->next_record())
					$our_race = $db2->f("race_name");

				$db2->query("SELECT * FROM race WHERE race_id = $race_id_2");
				if ($db2->next_record())
					$their_race = $db2->f("race_name");

				// get news message
				$news = "The $our_race have declared <span style=\"color:red;\">WAR</span> on the $their_race";
				$db2->query("INSERT INTO news (game_id, time, news_message) VALUES " .
							"($player->game_id, $time, " . format_string($news, false) . ")");

			}

		} elseif ($type == "PEACE" && $yes_votes > $no_votes) {

			// get 'yes' votes
			$db2->query("SELECT * FROM player_votes_pact " .
						"WHERE game_id = $player->game_id AND " .
							  "race_id_1 = $race_id_2 AND " .
							  "race_id_2 = $race_id_1 AND " .
							  "vote = 'YES'");
			$rev_yes_votes = $db2->nf();

			// get 'no' votes
			$db2->query("SELECT * FROM player_votes_pact " .
						"WHERE game_id = $player->game_id AND " .
							  "race_id_1 = $race_id_2 AND " .
							  "race_id_2 = $race_id_1 AND " .
							  "vote = 'NO'");
			$rev_no_votes = $db2->nf();

			// more yes than no?
			if ($rev_yes_votes > $rev_no_votes) {

				$db2->query("UPDATE race_has_relation " .
							"SET relation = 300 " .
							"WHERE game_id = $player->game_id AND " .
								  "(race_id_1 = $race_id_1 AND race_id_2 = $race_id_2 OR " .
								  "race_id_1 = $race_id_2 AND race_id_2 = $race_id_1)");

				$time = time();

				//get races
				$db2->query("SELECT * FROM race WHERE race_id = $race_id_1");
				if ($db2->next_record())
					$our_race = $db2->f("race_name");

				$db2->query("SELECT * FROM race WHERE race_id = $race_id_2");
				if ($db2->next_record())
					$their_race = $db2->f("race_name");

				//get news message
				$news = "The $our_race have signed a <span style=\"color:green;\">PEACE</span> treaty with the $their_race";
				$db2->query("INSERT INTO news (game_id, time, news_message) VALUES " .
							"($player->game_id, $time, " . format_string($news, false) . ")");

			}

		}

		// delete vote and user votes
		$db2->query("DELETE FROM race_has_voting " .
					"WHERE game_id = $player->game_id AND " .
						  "race_id_1 = $race_id_1 AND " .
						  "race_id_2 = $race_id_2");
		$db2->query("DELETE FROM player_votes_pact " .
					"WHERE game_id = $player->game_id AND " .
						  "race_id_1 = $race_id_1 AND " .
						  "race_id_2 = $race_id_2");
		// delete vote and user votes
		$db2->query("DELETE FROM race_has_voting " .
					"WHERE game_id = $player->game_id AND " .
						  "race_id_1 = $race_id_2 AND " .
						  "race_id_2 = $race_id_1");
		$db2->query("DELETE FROM player_votes_pact " .
					"WHERE game_id = $player->game_id AND " .
						  "race_id_1 = $race_id_2 AND " .
						  "race_id_2 = $race_id_1");

	}

}

?>