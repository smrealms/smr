<?php

function create_location($game_id, $galaxy_id, $location_type_id, $sector_id = 0) {

	// database object
	$db = new SmrMySqlDatabase();

	// if we didn't get a sector we choose one
	if ($sector_id == 0) {
		// get one sector where we put it
		$db->query('SELECT sector_id
					FROM sector
					WHERE game_id = ' . $db->escapeNumber($game_id) . '
						AND galaxy_id = ' . $db->escapeNumber($galaxy_id) . '
						AND sector_id NOT IN(
							SELECT sector_id
							FROM location
							WHERE game_id = sector.game_id
							AND location_type_id = ' . $db->escapeNumber($location_type_id) . '
						)
					ORDER BY rand()
					LIMIT 1');
		if (!$db->nextRecord())
			return false;
		$sector_id = $db->getInt('sector_id');
	}

	// now putting the hq in
	$db->query('INSERT INTO location
				(game_id, sector_id, location_type_id)
				VALUES (' . $db->escapeNumber($game_id) . ', ' . $db->escapeNumber($sector_id) . ', ' . $db->escapeNumber($location_type_id) . ')');

	return $sector_id;

}

function get_sector($game_id, $sector_id, $distance) {

	// database object
	$db = new SmrMySqlDatabase();

	$db->query('SELECT link_up, link_down, link_left, link_right
				FROM sector
				WHERE game_id = '.$game_id.' AND
					  sector_id = '.$sector_id);
	if (!$db->nextRecord())
		return false;

	// the last entry is the distance to the start sector
	return array($db->getField('link_up'), $db->getField('link_down'), $db->getField('link_left'), $db->getField('link_right'), $distance);

}

function create_fed($game_id, $galaxy_id, $sector_id = 0)
{
	// database object
	$db = new SmrMySqlDatabase();

	// if we didn't get a sector we choose one
	if ($sector_id == 0) {

		// get one sector where we put it
		$db->query('SELECT sector_id FROM sector
					WHERE game_id = '.$game_id.' AND
						  galaxy_id = '.$galaxy_id.'
					ORDER BY rand()
					LIMIT 1');

		if (!$db->nextRecord())
			return false;

		$sector_id = $db->getField('sector_id');

	}

	// we build a map now (load only the sectors we going to visit)
	// and set fed space for it

	// fill the galaxy_map with a fake sector 0
	$galaxy_map[0] = array(0, 0, 0, 0, 0);

	// add current sector to map
	$galaxy_map[$sector_id] = get_sector($game_id, $sector_id, 0);

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
		if ($galaxy_map[$curr_sector][4] > 2)
			continue;

		// put fed sector in for it
		create_location($game_id, $galaxy_id, FED, $curr_sector);

		// for every 4 possible exit sectors we loop over galaxy_map
		for ($i = 0; $i < 4; $i++) {

			// get target link for easier code
			$link_to = $galaxy_map[$curr_sector][$i];

			// did we previosly visitted that sector
			// or does it link to sector 0?
			if (isset($galaxy_map[$link_to]))
				continue;

			// get the distance from hq to the current sector
			$distance = $galaxy_map[$curr_sector][4];

			// load this sector	(distance will be old distance + 1)
			$galaxy_map[$link_to] = get_sector($game_id, $link_to, $distance + 1);

			// add to runner array
			array_push($runners, $link_to);

		}

	}

}

?>