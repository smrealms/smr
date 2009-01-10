<?php

/*
$player	=& SmrPlayer::getPlayer($account_id, $game_id);
$ship	= new SMR_SHIP($account_id, $game_id);
$sector = new SMR_SECTOR($player->getSectorID(), $game_id, $account_id);
*/

function scout($account_id, $game_id) {

	// get current sector
	$sector_id = get_current_sector($account_id, $game_id);
	log_message($account_id, 'I\'m currently in sector #'.$sector_id);

	// first we need to verify whats the distance to the next fed space
	$distance_to_fed = get_distance_to_fed($sector_id, $game_id, $account_id);
	log_message($account_id, 'This is '.$distance_to_fed.' sectors away from FED');

	$turns = get_player($account_id, $game_id, 'turns');
	$newbie_turns = get_player($account_id, $game_id, 'newbie_turns');

	// do we have to return to fed yet?
	if ($newbie_turns - $distance_to_fed < 1 && $turns - $distance_to_fed < 10) {

		log_message($account_id, 'Low on turns! I have to return to Fed Space');
		set_short_term_goal('follow_course', plot_course());

	} else
		log_message($account_id, 'Everything OK, continue mission.');


}

function get_current_sector($account_id, $game_id) {

	return get_sector($account_id, $game_id, 'sector_id');

}

function get_warp($account_id, $game_id, $sector_id) {

	// database object
	$db = new SmrMySqlDatabase();

	$db->query('SELECT *
				FROM warp
				WHERE game_id = '.$game_id.' AND
					  (sector_id_1 = $sector_id OR sector_id_2 = $sector_id)
			   ');
	if ($db->next_record())
		$warp = ($db->f('sector_id_1') == $sector_id) ? $db->f('sector_id_2') : $db->f('sector_id_1');
	else
		$warp = 0;

	return $warp;

}

function get_distance_to_fed($sector_id, $game_id, $account_id) {

	// initialize distance to fed
	$distance_to_fed = 9999;

	// database object
	$db = new SmrMySqlDatabase();

	// fill the galaxy_map with a fake sector 0
	$galaxy_map[0] = array(0, 0, 0, 0, 0);

	// add current sector to map
	$galaxy_map[$sector_id] = load_sector($game_id, $sector_id, 0);

	// and add it to the runner array
	// this array contains all sector id's
	// we still going to process
	$runners = array();
	array_push($runners, $sector_id);

	while (count($runners) > 0) {

		// get the next sector we process form runner array
		$curr_sector = array_pop($runners);

		// is the distance for this sector
		// already greater than the max distance from hq that is allowed?
		if ($galaxy_map[$curr_sector][4] >= $distance_to_fed)
			continue;

		if (sector_is_fed($curr_sector, $game_id))
			$distance_to_fed = $galaxy_map[$curr_sector][4];

		// for every 4 possible exit sectors we loop over galaxy_map
		for ($i = 0; $i < 4; $i++) {

			// get target link for easier code
			$link_to = $galaxy_map[$curr_sector][$i];

			// did we previosly visitted that sector
			// or does it link to sector 0?
			if (isset($galaxy_map[$link_to]))
				continue;

			// wherever this leads
			// we ignore it, if it's a black area, because we don't know whats there
			if (!sector_visited($account_id, $game_id, $link_to))
				continue;

			// get the distance from start sector to the current sector
			$distance = $galaxy_map[$curr_sector][4];

			// load this sector	(distance will be old distance + 1)
			$galaxy_map[$link_to] = load_sector($game_id, $link_to, $distance + 1);

			// add to runner array
			array_push($runners, $link_to);

		}

	}

	return $distance_to_fed;

}

function load_sector($game_id, $sector_id, $distance) {

	// database object
	$db = new SmrMySqlDatabase();

	$db->query('SELECT link_up, link_down, link_left, link_right
				FROM sector
				WHERE game_id = '.$game_id.' AND
					  sector_id = '.$sector_id);
	if (!$db->next_record())
		return false;

	// the last entry is the distance to the start sector
	return array($db->f('link_up'), $db->f('link_down'), $db->f('link_left'), $db->f('link_right'), $distance);

}

function sector_is_fed($sector_id, $game_id) {

	// database object
	$db = new SmrMySqlDatabase();

	$db->query('SELECT *
				FROM location NATURAL JOIN location_is_fed
				WHERE sector_id = '.$sector_id.' AND
					  game_id = '.$game_id.'
			   ');

	return $db->nf();

}

function sector_visited($account_id, $game_id, $sector_id) {

	// database object
	$db = new SmrMySqlDatabase();

	$db->query('SELECT *
				FROM player_visited_sector
				WHERE sector_id = '.$sector_id.' AND
					  game_id = '.$game_id.' AND
					  account_id = '.$account_id.'
			   ');

	return !$db->nf();

}

function sector_has_port($game_id, $sector_id) {

	// database object
	$db = new SmrMySqlDatabase();

	$db->query('SELECT *
				FROM port
				WHERE sector_id = '.$sector_id.' AND
					  game_id = '.$game_id.'
			   ');

	return $db->nf();

}

function sector_set_visited($account_id, $game_id, $sector_id) {

	// database object
	$db = new SmrMySqlDatabase();

	if (sector_has_port($game_id, $sector_id)) {

		$port_info = array();

		$db->query('SELECT *
					FROM port_has_goods
					WHERE game_id = '.$game_id.' AND
						  sector_id = '.$sector_id.'
					ORDER BY good_id
				   ');
		while ($db->next_record())
			$port_info[$db->f('good_id')] = $db->f('transaction');

		$curr_time = time();
		$port_info = $db->escape_string(serialize($port_info));

		//give them the port info
		$db->query('REPLACE INTO player_visited_port
					(account_id, game_id, sector_id, visited, port_info)
					VALUES ($account_id, $game_id, $sector_id, $curr_time, '.$db->escapeString($port_info).')
				   ');

	}

	//now delete the entry from visited
	$db->query('DELETE
				FROM player_visited_sector
				WHERE game_id = '.$game_id.' AND
					  sector_id = '.$sector_id.' AND
					  account_id = '.$account_id.'
			   ');

}

?>