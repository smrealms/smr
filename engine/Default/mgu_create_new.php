<?php
/*
 * Script:
 * 		/engine/Default/mgu_create_new.php
 * 
 * Changelog:
 * 		30/09/09 - Created (Curufir)
 * 
 * Notes:
 * 		MGU map creator.
 * 		This takes a bit more memory than most other scripts.
 * 		Sectors must have contiguous ids.
 * 		Goods must have contiguous ids.
 * 
 * TODO:
 * 		0/0/0 planets will show up as enemy
 */

/*
 * Setup the sectors array
 */
 
// First things first, grab all the sectors and dump them into an array
$game_id = $player->getGameID();

// Game name
$db->query(
	'SELECT game_name FROM game WHERE game_id=' .  $game_id . ' LIMIT 1'
);

$db->nextRecord();
$game_name = $db->getField('game_name');
$total_sectors = 0;

// Build the galaxy array.
$galaxies =& SmrGalaxy::getGameGalaxies($game_id);
foreach ($galaxies as &$galaxy) {
	$total_sectors += $galaxy->getSize();
} unset($galaxy);

// Fill the sectors array with visited sectors
$db->query(
	'SELECT sector_id,galaxy_id,link_up,link_left,link_right,link_down ' .
	'FROM sector WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' ' .
	'AND sector_id NOT IN ( ' .
	'SELECT sector_id FROM player_visited_sector ' .
	'WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ' .
	'AND account_id = ' . $db->escapeNumber($player->getAccountID()) . ' ) '
);

while($db->nextRecord()) {
	$sectors[$db->getField('sector_id')] = array(
		'up' => $db->getField('link_up'),
		'left' => $db->getField('link_left'),
		'right' => $db->getField('link_right'),
		'down' => $db->getField('link_down')
	);
}

// Locations
$db->query(
	'SELECT ' .
	'location.sector_id as sector_id,' .
	'location_type.mgu_id as mgu_id ' .
	'FROM location,location_type ' .
	'WHERE location.game_id=' . $db->escapeNumber($player->getGameID()) . ' ' .
	'AND location.location_type_id = location_type.location_type_id ' .
	'AND location.sector_id NOT IN ( ' .
	'SELECT sector_id FROM player_visited_sector ' .
	'WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ' .
	'AND account_id = ' . $db->escapeNumber($player->getAccountID()) . ' ) '
);

// Adjust the sectors array
while($db->nextRecord()) {
	$sectors[$db->getField('sector_id')]['location'][] = $db->getField('mgu_id');
}

// Warps
$db->query(
	'SELECT ' .
	'sector_id_1,sector_id_2 ' .
	'FROM warp ' .
	'WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' ' .
	'AND ((sector_id_1 NOT IN ( ' .
	'SELECT sector_id FROM player_visited_sector ' .
	'WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ' .
	'AND account_id = ' . $db->escapeNumber($player->getAccountID()) . ' ))' .
	' OR (sector_id_2 NOT IN ( ' .
	'SELECT sector_id FROM player_visited_sector ' .
	'WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ' .
	'AND account_id = ' . $db->escapeNumber($player->getAccountID()) . ' ))' .
	') '
);

// Adjust the sectors array
while($db->nextRecord()) {
	if(isset($sectors[$db->getField('sector_id_1')])) {
		$sectors[$db->getField('sector_id_1')]['warp'] = $db->getField('sector_id_2');
	}
	if(isset($sectors[$db->getField('sector_id_2')])) {
		$sectors[$db->getField('sector_id_2')]['warp'] = $db->getField('sector_id_1');
	}
}

// Grab the information for any ports the player has knowledge of
$ports = array();
$db->query(
	'SELECT ' .
	'port.race_id as race_id,' .
	'port.sector_id as sector_id, ' .
	'player_visited_port.port_info as port_info ' .
	'FROM player_visited_port,port ' .
	'WHERE port.game_id=' . $db->escapeNumber($player->getGameID()) . ' ' .
	'AND player_visited_port.game_id=' . $db->escapeNumber($player->getGameID()) . ' ' .
	'AND port.sector_id = player_visited_port.sector_id ' .
	'AND player_visited_port.account_id=' . $db->escapeNumber($player->getAccountID())
);

// Adjust the sectors array
while($db->nextRecord()) {
	// Adjust races for MGU
	$race = $db->getField('race_id');
	if($race == 1) {
		$race = 9;
	}
	else {
		--$race;
	}
	$sectors[$db->getField('sector_id')]['port'] = array(
		'info' => $db->getField('port_info'),
		'race' => $race
	);
}

// Grab the 'safe' sectors (Those with a friendly mine in)
$safe = array();
$query = 
	'SELECT sector_has_forces.sector_id as sector_id ' .
	'FROM sector_has_forces,player ' .
	'WHERE sector_has_forces.owner_id = player.account_id ' .
	'AND ';

if($player->getAllianceID()) {
	$query .= '(player.alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' OR ' .
	'sector_has_forces.owner_id = ' . $db->escapeNumber($player->getAccountID()) . ' ) ';
}
else {
	$query .= 'sector_has_forces.owner_id = ' . $db->escapeNumber($player->getAccountID()) . ' ';
}

$query .= 
	'AND player.game_id = sector_has_forces.game_id ' .
	'AND sector_has_forces.game_id = ' . $db->escapeNumber($player->getGameID()) . ' ' .
	'AND sector_has_forces.mines > 0 ' .
	'AND sector_has_forces.sector_id NOT IN ( ' .
	'SELECT sector_id FROM player_visited_sector ' .
	'WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ' .
	'AND account_id = ' . $db->escapeNumber($player->getAccountID()) . ' ) ' .
	'GROUP BY sector_has_forces.sector_id';

$db->query($query);

// Adjust the sectors array
while($db->nextRecord()) {
	$sectors[$db->getField('sector_id')]['safe'] = true;
}

// Grab the planets
$planets=array();
$db->query(
	'SELECT sector_id ' .
	'FROM planet ' .
	'WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' ' .
	'AND sector_id NOT IN ( ' .
	'SELECT sector_id FROM player_visited_sector ' .
	'WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ' .
	'AND account_id = ' . $db->escapeNumber($player->getAccountID()) . ' ) '
); 

// Adjust the sectors array
while($db->nextRecord()) {
	$sectors[$db->getField('sector_id')]['planet'] = true;
}

// Determine level of player/alliance owned planets
$query =
		'SELECT ' .
		'SUM(planet_has_building.amount) as level,' .
		'planet_has_building.sector_id as sector_id ' .
		'FROM planet_has_building,planet,player ' .
		'WHERE planet.game_id=' . $db->escapeNumber($player->getGameID()) . ' ' .
		'AND planet_has_building.game_id=' . $db->escapeNumber($player->getGameID()) . ' ' .
		'AND player.game_id=' . $db->escapeNumber($player->getGameID()) . ' ' .
		'AND planet_has_building.sector_id = planet.sector_id ' .
		'AND player.account_id = planet.owner_id ' .
		'AND planet_has_building.sector_id NOT IN ( ' .
		'SELECT sector_id FROM player_visited_sector ' .
		'WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ' .
		'AND account_id = ' . $db->escapeNumber($player->getAccountID()) . ' ) ' .
		'AND ';

if($player->getAllianceID()) {
	$query .= '(player.alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' OR ' .
	'planet.owner_id = ' . $db->escapeNumber($player->getAccountID()) . ' ) ';
}
else {
	$query .= 'planet.owner_id = ' . $db->escapeNumber($player->getAccountID()) . ' ';
}		

$query .= 'GROUP BY sector_id';

$db->query($query);

// Adjust the sectors array (The cast is important, we test against it later)
while($db->nextRecord()) {
	$sectors[$db->getField('sector_id')]['planet'] = (int)$db->getField('level');
}

/*
 * Build the output file
 */
$file = 'CMF by ^V^ Productions ©2004 ';

// Number of galaxies
$file .= pack('C',count($galaxies));

// Galaxies
foreach($galaxies as $galaxy_id => $galaxy) {
	// Names
	$file .= pack('C',strlen($galaxy->getName()));
	$file .= $galaxy->getName();
	$file .= pack('C', 0);
	// Race
	if($galaxy_id < 9) {
		$file .= pack('C', $galaxy_id);
	}
	else {
		$file .= pack('C', 9);
	}
	// Height/Width
	$file .= pack('C', $galaxy->getWidth());
	$file .= pack('C', $galaxy->getHeight());
}

$file .= pack('C', 2);
$file .= pack('C', 8);
$file .= 'Friendly';
$file .= pack('C', 5);
$file .= 'Enemy';

$max = $total_sectors + 1;

$galaxy_ids = array_keys($galaxies);
$galaxy_index = 0;
$current_galaxy = $galaxy_ids[$galaxy_index];

for($i=1;$i<$max;++$i) {

	$byte = 0;
	
	if($i > $galaxies[$current_galaxy]['end']) {
		++$galaxy_index;
		$current_galaxy = $galaxy_ids[$galaxy_index];
	}
	
	if(isset($sectors[$i])) {
		// Sector is known, all information is pulled from the $sectors array
		if($sectors[$i]['up']) {
			$byte |= 128;
		}
		if($sectors[$i]['right']) {
			$byte |= 64;
		}
		if($sectors[$i]['down']) {
			$byte |= 32;
		}
		if($sectors[$i]['left']) {
			$byte |= 16;
		}
		if(isset($sectors[$i]['planet'])) {
			$byte |= 8;
		}
		if(isset($sectors[$i]['port'])) {
			$byte |= 4;
		}
		if(isset($sectors[$i]['safe'])) {
			$byte |= 1;
		}
		
		$file .= pack('C', $byte);
		
		$byte = 0;
		
		if(isset($sectors[$i]['port'])) {
			$info = unserialize(gzuncompress($sectors[$i]['port']['info']));
			for($j=0;$j<3;++$j) {
				$byte = 0;
				for($k=0;$k<4;++$k) {
					$good_id = (4 * $j) + $k + 1;
					if($info->hasGood($good_id)) {
						$good = $info->getGood($good_id);
						if ($good['TransactionType'] == 'Sell') {
							$byte |= 1 << (2*(4 - $k) - 1);
						}
						else {
							$byte |= 1 << (2*(4 - $k) - 2);
						}
					}

				}
				$file .= pack('C', $byte);
			}
			
			// Port race byte is combined with planet byte
			$byte = $sectors[$i]['port']['race'] << 4;	
	
		}		
				
		if(isset($sectors[$i]['planet'])) {
			if(is_int($sectors[$i]['planet'])) {
				// Friendly planet;
				$file .= pack('C', ($byte | 1));
				$file .= pack('C', $sectors[$i]['planet']);
			}
			else {
				$file .= pack('C', ($byte | 2));
				$file .= pack('C', 0);
			}
		}
		else if(isset($sectors[$i]['port'])) {
			$file .= pack('C', $byte);
		}
		
		$byte = 0;
		
		if(isset($sectors[$i]['warp'])) {
			$byte |= 128;
		}
		
		if(isset($sectors[$i]['location'])) {
			$byte |= count($sectors[$i]['location']);
		}
		
		$file .= pack('C', $byte);
		
		if(isset($sectors[$i]['warp'])) {
			$file .= pack('v', $sectors[$i]['warp']);
		}
		
		if(isset($sectors[$i]['location'])) {
			foreach($sectors[$i]['location'] as $mgu_id) {
				$file .= pack('v', $mgu_id);
			}
		}
	}
	else {
		// We generate unvisited sectors manually
		$byte += 3;
		
		// Ok, first we want to know where we are.
		// Find X and Y inside the universe.
		$base = $i - $galaxies[$current_galaxy]['start'];
		$y = floor($base/$galaxies[$current_galaxy]['width']);
		$x = $base % $galaxies[$current_galaxy]['width'];
		
		// Check for a visited sector up.
		$check_x = $x;
		$check_y = $y - 1;
		if($check_y < 0) $check_y += $galaxies[$current_galaxy]['height'];
		$check_sector =
			$check_x + ($check_y * $galaxies[$current_galaxy]['width']) +
			$galaxies[$current_galaxy]['start'];

		if(isset($sectors[$check_sector]) && $sectors[$check_sector]['down']) {
			$byte |= 128;
		}
		
		// Check for a visited sector right.
		$check_x = $x + 1;
		$check_y = $y;
		if($check_x >= $galaxies[$current_galaxy]['width']) $check_x = 0;
		$check_sector =
			$check_x + ($check_y * $galaxies[$current_galaxy]['width']) +
			$galaxies[$current_galaxy]['start'];

		if(isset($sectors[$check_sector]) && $sectors[$check_sector]['left']) {
			$byte |= 64;
		}
		
		// Check for a visited sector down.
		$check_x = $x;
		$check_y = $y + 1;
		if($check_y >= $galaxies[$current_galaxy]['height']) $check_y = 0;
		$check_sector =
			$check_x + ($check_y * $galaxies[$current_galaxy]['width']) +
			$galaxies[$current_galaxy]['start'];

		if(isset($sectors[$check_sector]) && $sectors[$check_sector]['up']) {
			$byte |= 32;
		}
		
		// Check for a visited sector left.
		$check_x = $x - 1;
		$check_y = $y;
		if($check_x < 0) $check_x += $galaxies[$current_galaxy]['width'];
		$check_sector =
			$check_x + ($check_y * $galaxies[$current_galaxy]['width']) +
			$galaxies[$current_galaxy]['start'];

		if(isset($sectors[$check_sector]) && $sectors[$check_sector]['right']) {
			$byte |= 16;
		}
		
		$file .= pack('C', $byte);
		$file .= pack('C', 0);

		continue;
	}
}

$size = strlen($file);

header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private',false);
header('Content-Type: application/force-download');
header('Content-Disposition: attachment; filename="'.$game_name.'.cmf"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: '.$size);

echo $file;
exit;
//var_dump($sectors);

?>
