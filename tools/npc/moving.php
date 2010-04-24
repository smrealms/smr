<?php

function follow_course($account_id, $game_id, $course) {

	// example for the course: 50-49-48-33-18-17-16-1
	$sectors = explode('-', $course);

	// get first sector from the list
	$target_sector = array_shift($sectors);
	log_message($account_id, 'I follow a course. Next sector: '.$target_sector);

	// and move there
	if (move_to_sector($account_id, $game_id, $target_sector)) {

		// now glue it together again and return as new plot course
		return implode('-', $sectors);

	} else {

		// move to sector failed so we return the old course
		return $course;

	}

}

function move_to_sector($account_id, $game_id, $target_sector) {

	$turns			= get_player($account_id, $game_id, 'turns');
	$last_sector	= get_current_sector($account_id, $game_id);

	if ($target_sector == get_warp($account_id, $game_id, $last_sector))
	    $need_turns = 5;
	else
	    $need_turns = 1;

	if ($need_turns > $turns)
		return false;

	// send scout message
	leaving_sector($account_id, $game_id, $last_sector);

	// set green exit
	set_player($account_id, $game_id, 'last_sector_id', $last_sector);

	// move the user around
	set_player($account_id, $game_id, 'sector_id', $target_sector);
	takeTurns($account_id, $game_id, $need_turns);

	if (!sector_visited($account_id, $game_id, $target_sector)) {

		set_stats($account_id, 'sectors_explored', 1);

		// make current sector visible to him
		sector_set_visited($account_id, $game_id, $target_sector);

	}

	// send scout msg
	entering_sector($account_id, $game_id, $target_sector);

	return true;

}

function leaving_sector($account_id, $game_id, $sector_id) {

	// new db object
	$db = new SmrMySqlDatabase();

	// we need that for the rank
	$account = new SMR_ACCOUNT();
	$account->get_by_id($account_id);

	// get our rank
	$rank_id = $account->get_rank();

	// get our alliance_id
	$alliance_id = get_player($account_id, $game_id, 'alliance_id');

	$galaxy_id = get_sector($account_id, $game_id, 'galaxy_id');

	// iterate over all scout drones in sector
	$db->query('SELECT * FROM sector_has_forces
				WHERE sector_id = '.$sector_id.' AND
					  game_id = '.$game_id.' AND
					  owner_id != $account_id AND
					  scout_drones > 0
			   ');
	while ($db->nextRecord()) {

		// we can skip that whole thing if we are not in an alliance
		// in that case everyone is our enemy
		if ($alliance_id > 0) {

			// skip scouts ffrom friends
			if (get_player($db->getField('owner_id'), $game_id, 'alliance_id') == $alliance_id)
				continue;

		}

		// we may skip player if this is a protected gal.
		if ($galaxy_id < 9) {

			$curr_account = new SMR_ACCOUNT();
			$curr_account->get_by_id($db->getField('owner_id'));

			// if one is vet and the other is newbie we skip it
			if (different_level($rank_id, $curr_account->get_rank(), $account->veteran, $curr_account->veteran))
				continue;

		}

		// send scout messages to user
		$message = 'Your forces have spotted ' . get_colored_name($account_id, $game_id) . ' leaving sector #'.$sector_id.'';
		send_message($account_id, $game_id, $db->getField('owner_id'), SCOUTMSG, $message);

	}

}

function entering_sector($account_id, $game_id, $sector_id) {

	// new db object
	$db = new SmrMySqlDatabase();

	// we need that for the rank
	$account = new SMR_ACCOUNT();
	$account->get_by_id($account_id);

	// get our rank
	$rank_id = $account->get_rank();

	// get our alliance_id
	$alliance_id = get_player($account_id, $game_id, 'alliance_id');

	$galaxy_id = get_sector($account_id, $game_id, 'galaxy_id');

	// iterate over all scout drones in sector
	$db->query('SELECT * FROM sector_has_forces
				WHERE sector_id = '.$sector_id.' AND
					  game_id = '.$game_id.' AND
					  owner_id != $account_id AND
					  scout_drones > 0
			   ');
	while ($db->nextRecord()) {

		// we can skip that whole thing if we are not in an alliance
		// in that case everyone is our enemy
		if ($alliance_id > 0) {

			// skip scouts ffrom friends
			if (get_player($db->getField('owner_id'), $game_id, 'alliance_id') == $alliance_id)
				continue;

		}

		// we may skip player if this is a protected gal.
		if ($galaxy_id < 9) {

			$curr_account = new SMR_ACCOUNT();
			$curr_account->get_by_id($db->getField('owner_id'));

			// if one is vet and the other is newbie we skip it
			if (different_level($rank_id, $curr_account->get_rank(), $account->veteran, $curr_account->veteran))
				continue;

		}

		// send scout messages to user
		$message = 'Your forces have spotted ' . get_colored_name($account_id, $game_id) . ' entering sector #'.$sector_id;
		send_message($account_id, $game_id, $db->getField('owner_id'), SCOUTMSG, $message);

	}

}

?>