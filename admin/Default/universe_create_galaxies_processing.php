<?php
$action = $_REQUEST['action'];
if ($action == 'Skip >>') {

	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'universe_create_warps.php';
	$container['game_id']	= $var['game_id'];
	forward($container);

}
$galaxy = $_REQUEST['galaxy'];
$size = $_REQUEST['size'];
/**
 * Function to test wether all 4 cutter groups have been linked up yet
 * @param $CArray	array to test
 */
function test_connection($CArray) {

	for ($i = 0; $i < 4; $i++) {

		if(in_array(False, $CArray[0]))
			return False;

		if(in_array(False, $CArray[1]))
			return False;

		if(in_array(False, $CArray[2]))
			return False;

		if(in_array(False, $CArray[3]))
			return False;

	}

	return True;

}

// Form some useful arrays for the large map array
// Four possible exits north, east, south, west
$exits=array(False, False, False, False);

// Determine which cutter groups have visited.
// Try not to make the number of cutters insanely huge
$visitor=array(False, False, False, False);

$start_sector = 0;

for($galaxy_counter = 0; $galaxy_counter < count($galaxy); $galaxy_counter++) {

	$galaxy_id	= $galaxy[$galaxy_counter];
	$curr_size	= $size[$galaxy_counter];

	// Now form a single column of the map array
	$map_Y = array();
	for($i = 0; $i < $curr_size; $i++){

		$map_Y[$i] = array(
			'Visited' => False,
			'Exits' => $exits,
			'Visitors' => $visitor
		);

	}

	// Now form the full map
	$gal_map = array();
	for($i = 0; $i < $curr_size; $i++)
		$gal_map[$i] = $map_Y;

	//Initialise the cutters start positions and the cutter connectivity array
	$cutter_data = array();
	for($i = 0; $i < 6; $i++) {

		//Cutters start symmetrically distributed about the centre of the map
		$cutter_data[] = array(
			'Xpos' => (int)($curr_size * 0.25),
			'Ypos' => (int)($curr_size * 0.25),
			'Direction' => (int)(4 * mt_rand() / mt_getrandmax()),
			'Group' => 0
		);
		$cutter_data[] = array(
			'Xpos' => (int)($curr_size*0.75),
			'Ypos' => (int)($curr_size*0.25),
			'Direction' => (int)(4 * mt_rand() / mt_getrandmax()),
			'Group' => 1
		);
		$cutter_data[] = array(
			'Xpos' => (int)($curr_size*0.25),
			'Ypos' => (int)($curr_size*0.75),
			'Direction' => (int)(4 * mt_rand() / mt_getrandmax()),
			'Group' => 2
		);
		$cutter_data[] = array(
			'Xpos' => (int)($curr_size*0.75),
			'Ypos' => (int)($curr_size*0.75),
			'Direction' => (int)(4 * mt_rand() / mt_getrandmax()),
			'Group' => 3
		);

	}

	// Deal with the connection array. Don't need 4 entries each, but it makes the code less complex
	$cutter_c = array(
		array(True, False, False, False),
		array(False, True, False, False),
		array(False, False, True, False),
		array(False, False, False, True)
	);

	$uncut_sectors = $curr_size * $curr_size;

	// Ok, now let the cutters chew up the map until
	// a) The 4 groups are connected,
	// b) There are no uncut sectors.
	// This is the point where things may time out on larger maps.
	// Suggest increasing the connectivity to avoid this
	do {

		// Move the cutters
		for($i = 0; $i < 24; $i++) {

			// Personal preference, I like having my variables out of the array where I can see them
			$Xpos = $cutter_data[$i]['Xpos'];
			$Ypos = $cutter_data[$i]['Ypos'];
			$direction = $cutter_data[$i]['Direction'];
			$group = $cutter_data[$i]['Group'];

			//Ok, now deal with the current sector

			// 0-north, 1-east, 2-south, 3-west
			//Move cutter into the next square. Note 0,0 is top left of grid
			if ($direction == 0) {

				// markup the exit in the last sector
				$gal_map[$Xpos][$Ypos]['Exits'][0] = True;
				$Ypos--;
				if ($Ypos < 0)
					$Ypos = $curr_size - 1;

				// Open exit it entered from South in this case
				$gal_map[$Xpos][$Ypos]['Exits'][2] = True;

			} elseif ($direction == 1) {

				// markup the exit in the last sector
				$gal_map[$Xpos][$Ypos]['Exits'][1] = True;
				$Xpos++;
				if ($Xpos > $curr_size - 1)
					$Xpos = 0;

				// Open exits. East and West in this case
				$gal_map[$Xpos][$Ypos]['Exits'][3] = True;

			} elseif ($direction == 2) {

				// markup the exit in the last sector
				$gal_map[$Xpos][$Ypos]['Exits'][2] = True;
				$Ypos++;
				if ($Ypos > $curr_size - 1)
					$Ypos = 0;

				// Open exits. North and South in this case
				$gal_map[$Xpos][$Ypos]['Exits'][0] = True;

			} elseif ($direction == 3) {

				//markup the exit in the last sector
				$gal_map[$Xpos][$Ypos]['Exits'][3] = True;
				$Xpos--;
				if ($Xpos < 0)
					$Xpos=$curr_size - 1;

				// Open exits. East and West in this case
				$gal_map[$Xpos][$Ypos]['Exits'][1] = True;

			}

			// Mark Visited by this cutter group
			$gal_map[$Xpos][$Ypos]['Visitors'][$group] = True;

			// If sector hasn't been visited yet mark it as visited.
			if ($gal_map[$Xpos][$Ypos]['Visited'] == False) {

				//Unvisited, mark true, decrement uncut sector count, mark as visited by this group
				$gal_map[$Xpos][$Ypos]['Visited'] = True;

				//Decrement uncut sectors
				$uncut_sectors--;

			}

			//Get information on which sectors adjoining this one have not been cut
			$adj_sectors = array();

			$TestX = $Xpos + 1;
			if ($TestX > ($curr_size - 1))
				$TestX = 0;
			if ($gal_map[$TestX][$Ypos]['Visited'] == False)
				$adj_sectors[] = 1;

			$TestX = $Xpos - 1;
			if ($TestX < 0)
				$TestX = $curr_size - 1;
			if ($gal_map[$TestX][$Ypos]['Visited'] == False)
				$adj_sectors[] = 3;

			$TestY = $Ypos - 1;
			if ($TestY < 0)
				$TestY = $curr_size - 1;
			if ($gal_map[$Xpos][$TestY]['Visited'] == False)
				$adj_sectors[] = 0;

			$TestY = $Ypos + 1;
			if ($TestY > ($curr_size-1))
				$TestY = 0;
			if ($gal_map[$Xpos][$TestY]['Visited'] == False)
				$adj_sectors[] = 2;

			$Tester = (float)(mt_rand() / mt_getrandmax());
			if ($Tester < 0.95 && sizeof($adj_sectors) > 0){

				shuffle($adj_sectors);
				$direction = $adj_sectors[0];
				$cutter_data[$i]['Direction'] = $direction;

			} else {

				$direction = (int)(4 * mt_rand() / mt_getrandmax());
				$cutter_data[$i]['Direction'] = $direction;

			}

			// Update Position
			$cutter_data[$i]['Xpos'] = $Xpos;
			$cutter_data[$i]['Ypos'] = $Ypos;

		}

	} while($uncut_sectors > 0 && !test_connection($cutter_c));

	$Xpos = 0;
	$Ypos = 0;
	for($curr_sector = $start_sector + 1; $curr_sector <= $start_sector + $curr_size * $curr_size; $curr_sector++) {

		// specifiy the line number in the current 'block'
		$line = floor(($curr_sector - $start_sector - 1) / $curr_size) + 1;

		// sector numbers on the most left, right, up and down
		$right_border	= $line * $curr_size + $start_sector;
		$left_border	= $right_border - $curr_size + 1;
		$up_border		= $curr_sector - ($curr_size * ($line - 1));
		$down_border	= $curr_sector + ($curr_size * ($curr_size - $line));

		$left = $curr_sector - 1;
		if ($left < $left_border)
			$left = $right_border;

		$right = $curr_sector + 1;
		if ($right > $right_border)
			$right = $left_border;

		$up = $curr_sector - $curr_size;
		if ($up < $up_border)
			$up = $down_border;

		$down = $curr_sector + $curr_size;
		if ($down > $down_border)
			$down = $up_border;


		if($Xpos > ($curr_size - 1)) {

			$Xpos = 0;
			$Ypos++;

		}

		// now dowblecheck with our gal_map
		// 0-north, 1-east, 2-south, 3-west
		if ($gal_map[$Xpos][$Ypos]['Exits'][0] == False)
			$up = 0;
		if ($gal_map[$Xpos][$Ypos]['Exits'][1] == False)
			$right = 0;
		if ($gal_map[$Xpos][$Ypos]['Exits'][2] == False)
			$down = 0;
		if ($gal_map[$Xpos][$Ypos]['Exits'][3] == False)
			$left = 0;

		$db->query('INSERT INTO sector (sector_id, game_id, galaxy_id, link_up, link_down, link_left, link_right)
					VALUES(' . $db->escapeNumber($curr_sector) . ', ' . $db->escapeNumber($var['game_id']) . ', ' . $db->escapeNumber($galaxy_id) . ', ' . $db->escapeNumber($up) . ', ' . $db->escapeNumber($down) . ', ' . $db->escapeNumber($left) . ', ' . $db->escapeNumber($right) . ')');

		$Xpos++;

	}

	$start_sector += $curr_size * $curr_size;

}

$container = create_container('skeleton.php', 'universe_create_warps.php');
transfer('game_id');

forward($container);

?>