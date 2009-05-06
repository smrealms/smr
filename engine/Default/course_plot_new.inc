<?php
throw new Exception('Used');
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());

/**
 * Returns the galaxy id of a certain sector or 0 if it doesn't exist.
 */
function get_galaxy_id($sector_id, $game_id) {

	$temp_db = new SmrMySqlDatabase();

	$temp_db->query('SELECT galaxy_id FROM sector
					 WHERE sector_id = '.$this->escapeString($sector_id).' AND
						   game_id = '.$this->escapeString($game_id).'
					 LIMIT 1');

	if ($temp_db->nextRecord())
		return $temp_db->getField('galaxy_id');
	else
		return false;

}

function get_plot(&$route_in, $game_id) {

	// echo 'PLOT COURSE IS CURRENTLY UNDERGOING TESTING, HIT THE BACK BUTTON AND USE CURRENT SECTOR/LOCAL MAP INSTEAD<br />';
	// do a check if start and target are the same
	if ($route_in['START'] == $route_in['END']) {

		// build a trivial route from it
		// target sector is always a part of the route
		$route_in['RAW'] = $route_in['END'];

		// of course it has a zero length
		$route_in['LEN'] = 0;

		// return with success
		return true;

	}

	$db = new SmrMySqlDatabase();
	$db2 = new SmrMySqlDatabase();

	// First things first, get every sector in the galaxy

	$db->query('SELECT sector_id, link_up, link_down, link_left, link_right
			FROM sector
			WHERE game_id = '.$game_id.'
			ORDER BY sector_id');

	// Ok, now build a map array to work inside
	$galaxy_map = array();

	// Array is like this. $galaxy_map[sector_id] = link_up, link_down, link_left, link_right, visitor
	// Where visitor is 0:unvisited, 1: start group, 2: target group, 3:avoid, 4:unexplored
	while ($db->nextRecord()) {
		
		$sect = $db->getField('sector_id');
		//get warp
		$db2->query('SELECT * FROM warp WHERE (sector_id_1 = '.$sect.' OR sector_id_2 = '.$sect.') AND game_id = '.$game_id);
		if ($db2->nextRecord())
			if ($sect == $db2->getField('sector_id_1')) $warp = $db2->getField('sector_id_2');
			else $warp = $db2->getField('sector_id_1');
		else
			$warp = 0;
		$galaxy_map[$db->getField('sector_id')] = array(	$db->getField('link_up'),
								$db->getField('link_down'),
								$db->getField('link_left'),
								$db->getField('link_right'),
								$warp,
								0);
								
	}

	// Maps can get quite large, release the memory
	$db->free();

	// Grab all the sectors the player doesn't know about from the table (DORMANT CODE)
	/*
	$db->query('SELECT sector_id 
			FROM player_visited_sector 
			WHERE account_id = '.$player->getAccountID().' ' .
			AND game_id = ' .	$game_id);

	while ($db->nextRecord()) {
		$galaxy_map[$db->getField('sector_id')][5] = 4;
	}

	// Depending on how adventurous they are this can be quite big, release the memory
	$db->free();
	*/
	// Now build an array of avoided sectors (DORMANT CODE)
	// $avoided_sectors = explode(':', $player->avoid_sectors);
	// for($i=0;$i<sizeof($avoided_sectors);$i++){
	//	$galaxy_map[$avoided_sectors[$i]][4] = 3;
	// }

	// Ok, now build an array of wavefront points which I call runners
	// We start out with two, one at the start sector the other at the target sector
	// Array is like this: $runners[runner_group][$sector_id] = string of route it has taken
	// runner_group - 0:Runner began at start, 1:Runner began at end
	// The algorithm now has 2 wavefronts, not 1
	$runners = array(array());
	$runners[0][$route_in['START']] = $route_in['START'];
	$runners[1][$route_in['END']] = $route_in['END'];

	// Now set the start and target sectors as having been visited
	$galaxy_map[$route_in['START']][5] = 1;
	$galaxy_map[$route_in['END']][5] = 2;

	// This tracks how many iterations have passed and therefore how long the route is
	$len = array(array());
	$amount = 0;
	$used = array();
	// Ok, now run until such times as the target sector is found
	while (true) {

		// Contains information of new runners
		$new_runners = array(array());

		// Remember: k = runner group (start/target), i = runner id(sector)

		for ($k = 0; $k<2; $k++) {
			// Here to prevent recalculating on every iteration
			// note to Az - func below is how many sectors stored
			$total_runners = sizeof($runners[$k]);

			// Keys are non-sequential, a foreach loop would make a copy of the array which would be BAD
			// Therefore the simplest thing to do here is just pull out the keys from runners into a separate array
			// note to Az - func below is what the sectors are
			$runners_keys = array_keys($runners[$k]);

			// Ok, let the runner run
			for ($i = 0; $i < $total_runners; $i++) {

				// Keep track of the sector this particular runner is in
				$runner_sector = $runners_keys[$i];
				// Now create the new runners if possible
				for($j = 0; $j < 5; $j++) {

					// The sector being moved to
					$sector_id = $galaxy_map[$runner_sector][$j];
					if ($sector_id > 0) {
						// This sector hasn't been visited before, so build a new runner and set it visited
						if ($k == 0) {
							// Runners that began at the start generate the route forwards
							$new_runners[0][$sector_id] = $runners[0][$runner_sector] . ':' . $sector_id;
							if (empty($len[0][$sector_id]) || $len[0][$sector_id] > $len[0][$runner_sector] + 1)
								$len[0][$sector_id] = $len[0][$runner_sector] + 1;
							//is it a warp?
							if ($j == 4) $len[0][$sector_id] = $len[0][$runner_sector] + 5;
						}
						else {
							// Runners that began at the target generate the route backwards
							$new_runners[1][$sector_id] = $sector_id . ':' . $runners[1][$runner_sector];
							//don't add if len is already less...
							if (empty($len[1][$sector_id]) || $len[1][$sector_id] > $len[1][$runner_sector] + 1)
								$len[1][$sector_id] = $len[1][$runner_sector] + 1;
							//is it a warp?
							if ($j == 4) $len[1][$sector_id] = $len[1][$runner_sector] + 5;
						}
						// Set sector as having been visited by the appropriate visitor type
						//only if it hasn't been b4
						if ($galaxy_map[$sector_id][5] == 0)
							$galaxy_map[$sector_id][5] = $k + 1;
					} 
					if ($sector_id > 0 && $galaxy_map[$sector_id][5] == 2 - $k ) {
						
						// This means we've just hit a sector already visited by the opposing set of runners
						// ie the two wavefronts have met so we have the shortest route available
						// The complimentary half of the route exists in one of two places
						// runner group 1 will find it in $runners
						// runner group 2 will find it in $new_runners since runner group 1 has already run
						//this is the value we add to the distance incase it ends at a warp without going through
						if ($j == 4) $add_value = 5;
						else $add_value = 1;
						if ($k == 0) {
								
								$so_far[] = $len[0][$runner_sector] + $len[1][$sector_id] + $add_value;
								//$PHP_OUTPUT.=($len[0][$runner_sector] . ' + ' . $len[1][$sector_id] + $add_value . '<br />');
								//maybe its not in a real runner already
								if ($runners[1][$sector_id] == '') $runners[1][$sector_id] = $new_runners[1][$sector_id];
								if ($runners[0][$runner_sector] == '') $runners[0][$runner_sector] = $new_runners[0][$sector_id];
								$so_far_route[] = $runners[0][$runner_sector] . ':' . $runners[1][$sector_id];
								
						} else {
								
								$so_far[] = $len[0][$sector_id] + $len[1][$runner_sector] + $add_value;
								//$PHP_OUTPUT.=($len[0][$sector_id] . ' + ' . $len[1][$runner_sector] + $add_value . '<br />');
								//maybe its in a real runner already
								if ($new_runners[0][$sector_id] == '') $new_runners[0][$sector_id] = $runners[0][$sector_id];
								if ($new_runners[1][$runner_sector] == '') $new_runners[1][$runner_sector] = $runners[1][$runner_sector];
								$so_far_route[] = $new_runners[0][$sector_id] . ':' . $runners[1][$runner_sector];
								
						}
						//since this is a long plot we need to make sure we get a couple choices
						//so that we make sure we get the shortest
						if ($amount < 20) {
							
							$amount++;
							continue;
							
						} else {
							
							//find the shortest
							asort($so_far);
							//debg stuff
							/*
							ksort($runners[0]);
							ksort($runners[1]);
							ksort($new_runners[0]);
							ksort($new_runners[1]);
							ksort($so_far_route);
							$PHP_OUTPUT.=('<pre>');
							echo_r($so_far);
							echo_r($so_far_route);
							echo_r($runners[0]);
							echo_r($runners[1]);
							echo_r($new_runners[0]);
							echo_r($new_runners[1]);
							$PHP_OUTPUT.=('</pre>');
							*/
							foreach ($so_far as $key => $dist) {
								
								$route_in['LEN'] = $dist;
								$route_in['RAW'] = $so_far_route[$key];
								break;
								
							}
							
						}
							
						// Set the appropriate length

						break 4;
					}
				}
			}
			if(sizeof($new_runners[$k]) == 0) {
				// Lack of new runners means this is a failed plot, kill the routine
				unset ($runners, $new_runners, $galaxy_map, $runners_keys);
				return false;
			}
			unset($runners_keys);
		}
		// Ok, route hasn't been found, so setup again, this looks odd but is totally deliberate
		unset ($runners);
		$runners = $new_runners;
		unset ($new_runners);
	}
	unset ($galaxy_map);
	return true; // Success
}

?>