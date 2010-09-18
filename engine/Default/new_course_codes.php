<?php
throw new Exception('Used');
/////////////////////////////////////////////////
//
// NOTE NOTE NOTE NOTE NOTE NOTE NOTE NOTE NOTE
//
/////////////////////////////////////////////////

//note this is not a real script it is just to remind me how to do things I will
//forget for plot course.

/////////////////////////////////////////////////
//
// This is the code to get uni array
//
/////////////////////////////////////////////////
$sector = array();
$db2 = new SmrMySqlDatabase();
$db->query('SELECT * FROM sector WHERE game_id = '.$game_id);
while ($db->nextRecord()) {

	$sec_id = $db->getField('sector_id');
	$sector_links = array();
	$sector_links[1] = $db->getField('link_up');
	$sector_links[2] = $db->getField('link_right');
	$sector_links[3] = $db->getField('link_down');
	$sector_links[4] = $db->getField('link_left');
	$db2->query('SELECT * FROM warp WHERE game_id = '.$game_id.' AND (sector_id_1 = '.$sec_id.' OR sector_id_2 = '.$sec_id.')');
	if ($db->nextRecord()) {
			
		if ($db2->getField('sector_id_1') == $sec_id)
			$sector_links[5] = $db2->getField('sector_id_2');
		else
			$sector_links[5] = $db2->getField('sector_id_1');
	} else
		$sector_links[5] = 0;
	$sector[$sec_id] = $sector_links;
	
}

$db->query('INSERT INTO universe_array (game_id, array) VALUES ('.$game_id.', '.$sector.')');

/////////////////////////////////////////////////
//
// Now here is the update alliance array code
// Run this (or player update) at game_play_proc
//
/////////////////////////////////////////////////
$db2 = new SmrMySqlDatabase();
$db3 = new SmrMySqlDatabase();
$explored = array();
$db->query('SELECT * FROM sector WHERE game_id = '.$player->getGameID().' ORDER BY sector_id');
while ($db->nextRecord()) {
	
	$sec_id = $db->getField('sector_id');
	$db2->query('SELECT * FROM player NATURAL JOIN player_visited_sector ' .
						'WHERE game_id = '.$player->getGameID().' AND ' .
						'alliance_id = '.$player->getAllianceID().' AND ' .
						'sector_id = '.$sec_id);
						
	$db3->query('SELECT * FROM alliance WHERE game_id = '.$player->getGameID().' AND ' .
													  'alliance_id = '.$player->getAllianceID());
	//find how many players are in this alliance
	$count = $db3->getNumRows();
	//if we have less entries than members it means we have explored it
	if ($db2->getNumRows() < $count) {
			
			//someone has explored this sector!!
			$explored[] = $sec_id;
			
	}
	
}
$db->query('REPLACE INTO alliance_maps (game_id, alliance_id, maps) VALUES ' .
				'('.$player->getGameID().', '.$player->getAllianceID().', '.$explored.')');

///////////////////////////////////////////////////
//
// Now here is the update player array code
// Run this (or alliance update) at game_play_proc
//
///////////////////////////////////////////////////

$db2 = new SmrMySqlDatabase();
$db3 = new SmrMySqlDatabase();
$explored = array();
$db->query('SELECT * FROM sector WHERE game_id = '.$player->getGameID().' ORDER BY sector_id');
while ($db->nextRecord()) {
	
	$sec_id = $db->getField('sector_id');
	$db2->query('SELECT * FROM player NATURAL JOIN player_visited_sector ' .
						'WHERE game_id = '.$player->getGameID().' AND ' .
						'sector_id = '.$sec_id);
						
	if (!$db2->nextRecord()) {
			
			//we have explored this sector!!
			$explored[] = $sec_id;
			
	}
	
}
$db->query('REPLACE INTO player_has_maps (game_id, alliance_id, maps) VALUES ' .
				'('.$player->getGameID().', '.$player->getAllianceID().', '.$explored.')');

////////////////////////////////////
//
// we are joining an alliance
// first run alliance update code
//
////////////////////////////////////

$db->query('DELETE FROM player_has_maps WHERE game_id = '.$player->getGameID().' AND account_id = '.$player->getAccountID());

////////////////////////////////////
//
// we are leaving an alliance
//
////////////////////////////////////

//before you take them out of the alliance
$db->query('SELECT * FROM alliance_has_maps WHERE game_id = '.$player->getGameID().' AND ' .
				'alliance_id = '.$player->getAllianceID());
$old_maps = $db->getField('maps');
$db->query('REPLACE INTO player_has_maps (game_id, account_id, maps) VALUES ' .
				'('.$player->getGameID().', '.$player->getAccountID().', '.$old_maps.')');

//now run the rest of the leaving script


/////////////////////////////////////
//
// This is the plot course code here
//
/////////////////////////////////////

if (empty($to) || !is_numeric($to) || $to == $from) {

    $container = array();
    $container['url'] = 'skeleton.php';
    $container['body'] = 'course_plot.php';
    forward($container);

}

$db->query('SELECT * FROM sector WHERE sector_id = '.$to.' AND game_id = '.SmrSession::$game_id);
if ($db->getNumRows() == 0)
	create_error('That sector doesn\'t exist. Try a new one.');

$db->query('SELECT * FROM sector WHERE sector_id = '.$from.' AND game_id = '.SmrSession::$game_id);
if ($db->getNumRows() == 0)
	create_error('That sector doesn\'t exist. Try a new one.');
	
//get our arrays
$db->query('SELECT * FROM universe_array WHERE game_id = '.$player->getGameID());
$uni_array = $db->getField('array');
if ($player->hasAlliance())
	$db->query('SELECT * FROM alliance_maps WHERE game_id = '.$player->getGameID().' AND alliance_id = '.$player->getAllianceID());
else
	$db->query('SELECT * FROM player_has_maps WHERE game_id = '.$player->getGameID().' AND ' .
					'account_id = '.$player->getAccountID());
					
$user_array = $db->getField('maps');

// initialize the queue. all sectors we have to visit are in here
$sector_queue = array();

// keeps the distance to the start sector
$sector_distance = array();

// putting start sector in queues
array_push($sector_queue, $from);
$sector_distance[$from] = 0;

while (sizeof($sector_queue) > 0) {

	// get current sector and
	$curr_sec_id = array_shift($sector_queue);
	
	$curr_sec = $uni_array[$curr_sec_id];
	
	// if we havn't visited this sector we go on.
	// except if this is the target sector.
	
	if (in_array($curr_sec_id, $user_array) && $curr_sec_id != $to) {

		unset($sector_distance[$curr_sector->getSectorID()]);
		continue;
	}
	
	// get the distance for this sector from the source
	$distance = $sector_distance[$curr_sector->getSectorID()];

	if ($curr_sec_id == $to)
		$target_distance = $distance;

	// enqueue all neighbours
	//1=north/up
	//2=right/east
	//3=south/down
	//4=left/left
	//5=warp
	$sector_up = $curr_sec[1];
	$sector_down = $curr_sec[3];
	$sector_left = $curr_sec[4];
	$sector_right = $curr_sec[2];
	$sector_warp = $curr_sec[5];
	if ($sector_up > 0 && (!isset($sector_distance[$sector_up]) || $sector_distance[$sector_up] > $distance + 1) && ($target_distance == 0 || $target_distance > $distance)) {

		array_push($sector_queue, $sector_up);
		$sector_distance[$sector_up] = $distance + 1;
	}

	if ($sector_down > 0 && (!isset($sector_distance[$sector_down]) || $sector_distance[$sector_down] > $distance + 1) && ($target_distance == 0 || $target_distance > $distance)) {

		array_push($sector_queue, $sector_down);
		$sector_distance[$sector_down] = $distance + 1;
	}

	if ($sector_left > 0 && (!isset($sector_distance[$sector_left]) || $sector_distance[$sector_left] > $distance + 1) && ($target_distance == 0 || $target_distance > $distance)) {

		array_push($sector_queue, $sector_left);
		$sector_distance[$sector_left] = $distance + 1;
	}

	if ($sector_right > 0 && (!isset($sector_distance[$sector_right]) || $sector_distance[$sector_right] > $distance + 1) && ($target_distance == 0 || $target_distance > $distance)) {

		array_push($sector_queue, $sector_right);
		$sector_distance[$sector_right] = $distance + 1;
	}

	if ($sector_warp > 0 && (!isset($sector_distance[$sector_warp]) || $sector_distance[$sector_warp] > $distance + 5) && ($target_distance == 0 || $target_distance > $distance + 4)) {

		array_push($sector_queue, $sector_warp);
		$sector_distance[$sector_warp] = $distance + 5;
	}
}

// Check if we found a distance
if (!isset($sector_distance[$to]))
	create_error('You can\'t plot through unknown space!');

// initialize the array where our path will be in
$sector_path = array();

// put target sector in that list
array_push($sector_path, $to);

// now we start at target sector and trace back to start sector
while (!in_array($from, $sector_path)) {

	// get the last sector in the current path list (without removing it)
	$curr_sector_id = $sector_path[sizeof($sector_path) - 1];
	
	$curr_sec = $uni_array[$curr_sector_id];
	$curr_sector_up = $curr_sec[1];
	$curr_sector_down = $curr_sec[3];
	$curr_sector_left = $curr_sec[4];
	$curr_sector_right = $curr_sec[2];
	$curr_sector_warp = $curr_sec[5];

	// get the four surrounding sector and check which one have the distance = current_distance - 1
	if ($curr_sector_down > 0 && isset($sector_distance[$curr_sector_down]) && $sector_distance[$curr_sector_down] + 1 == $sector_distance[$curr_sector_id])
		array_push($sector_path, $curr_sector_down);
	elseif ($curr_sector_up > 0 && isset($sector_distance[$curr_sector_up])&& $sector_distance[$curr_sector_up] + 1 == $sector_distance[$curr_sector_id])
		array_push($sector_path, $curr_sector_up);
	elseif ($curr_sector_left > 0 && isset($sector_distance[$curr_sector_left])&& $sector_distance[$curr_sector_left] + 1 == $sector_distance[$curr_sector_id])
		array_push($sector_path, $curr_sector_left);
	elseif ($curr_sector_right > 0 && isset($sector_distance[$curr_sector_right])&& $sector_distance[$curr_sector_right] + 1 == $sector_distance[$curr_sector_id])
		array_push($sector_path, $curr_sector_right);
	elseif ($curr_sector_warp > 0 && isset($sector_distance[$curr_sector_warp])&& $sector_distance[$curr_sector_warp] + 5 == $sector_distance[$curr_sector_id])
		array_push($sector_path, $curr_sector_warp);
	else
		create_error('Something really, really strange happened. Contact SPOCK');
}

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'course_plot_result.php';
$container['plotted_course'] = serialize(array_reverse($sector_path));
$container['distance'] = $sector_distance[$to];

forward($container);
?>