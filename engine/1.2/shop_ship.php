<?php
$db2 = new SmrMySqlDatabase();
print_topic('SHIP DEALER');

$db->query('SELECT 
	ship_type.ship_name as ship_name,
	ship_type.ship_type_id as ship_type_id,
	ship_type.cost as cost,
	ship_type.lvl_needed as lvl_needed
	FROM location, location_sells_ships,ship_type
	WHERE location.sector_id=' . $player->sector_id . '
	AND location.game_id=' . $player->game_id . '
	AND location_sells_ships.location_type_id = location.location_type_id
	AND ship_type.ship_type_id = location_sells_ships.ship_type_id
');

if ($db->nf() > 0 ) {

	echo '<table cellspacing="0" class="standard"><tr><th>Name</th><th>Cost</th><th>Action</th></tr>';

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'shop_ship.php';

	while ($db->next_record()) {

		$ship_name = $db->f('ship_name');
		$ship_type_id = $db->f('ship_type_id');
		$cost = $db->f('cost');
        $level_needed = $db->f('lvl_needed');

		$container['ship_id'] = $ship_type_id;
        $container['level_needed'] = $level_needed;
		echo '<tr><td>' . $ship_name;
		echo '</td><td>' . $cost . '</td><td>';
		echo print_button($container,'View Details');
		echo '</td></tr>';
	}

	echo '</table><br>';

}

if (isset($var['ship_id'])) {

	$db->query('SELECT * FROM ship_type WHERE ship_type_id = ' . $var['ship_id'] . ' LIMIT 1');
	if ($db->next_record()) {

		$ship_name			= $db->f('ship_name');
		$speed				= $db->f('speed');
		$cost				= $db->f('cost');
		$hardpoint 			= $db->f('hardpoint');
		$race_id 			= $db->f('race_id');
		$buyer_restriction	= $db->f('buyer_restriction');
        $level_needed		= $db->f('lvl_needed');

	}
	$db->query('SELECT game_speed FROM game WHERE game_id=' . $player->game_id . ' LIMIT 1');
	$db->next_record();
	$game_speed = $db->f('game_speed');
	$dis_speed = $speed * $game_speed;
	$max_hardware = array();

	// get supported hardware from db
	$db->query('SELECT * FROM ship_type_support_hardware NATURAL JOIN hardware_type WHERE ship_type_id=' . $var['ship_id']);

	while ($db->next_record())
	    $max_hardware[$db->f('hardware_type_id')] = $db->f('max_amount');

	$db->query('SELECT * FROM hardware_type');
	while ($db->next_record()) {

		$hardware_type_id = $db->f('hardware_type_id');

		// initialize empty hardware
		if (empty($max_hardware[$hardware_type_id]))
			$max_hardware[$hardware_type_id] = 0;

	}

	$container = array();
	$container['url']		= 'shop_ship_processing.php';
	$container['speed']		= $speed;
	$container['cost']		= $cost;
	$container['race_id']	= $race_id;
	$container['buyer_restriction']	= $buyer_restriction;
    $container['level_needed'] = $level_needed;
	transfer('ship_id');

	echo '<table cellspacing="0"class="standard"><tr><th>&nbsp;</th><th>';
	echo $ship->ship_name;
	echo '</th><th>';
	echo $ship_name;
	echo '</th></tr>';

	echo '<tr><td>Shields</td><td>';
	echo $ship->max_hardware[1];
	echo '</td><td>';
	echo $max_hardware[1];
	echo '</td></tr>';

	echo '<tr><td>Armor</td><td>';
	echo $ship->max_hardware[2];
	echo '</td><td>';
	echo $max_hardware[2];
	echo '</td></tr>';

	echo '<tr><td>Combat Drones</td><td>';
	echo $ship->max_hardware[4];
	echo '</td><td>';
	echo $max_hardware[4];
	echo '</td></tr>';

	echo '<tr><td>Scout Drones</td><td>';
	echo $ship->max_hardware[5];
	echo '</td><td>';
	echo $max_hardware[5];
	echo '</td></tr>';

	echo '<tr><td>Mines</td><td>';
	echo $ship->max_hardware[6];
	echo '</td><td>';
	echo $max_hardware[6];
	echo '</td></tr>';

	echo '<tr><td>Cargo Holds</td><td>';
	echo $ship->max_hardware[3];
	echo '</td><td>';
	echo $max_hardware[3];
	echo '</td></tr>';

	echo '<tr><td>Hardpoints</td><td>';
	echo $ship->hardpoint;
	echo '</td><td>';
	echo $hardpoint;
	echo '</td></tr>';

	echo '<tr><td>Speed</td><td>';
	echo $ship->speed * $game_speed;
	echo ' TPH</td><td>';
	echo $dis_speed;
	echo ' TPH</td></tr>';

	echo '<tr><td>Scanner</td><td>';
	if (!empty($ship->max_hardware[7])) echo '+';
	else echo '-';
	echo '</td><td>';
	if (!empty($max_hardware[7])) echo '+';
	else echo '-';
	echo '</td></tr>';

	echo '<tr><td>Illusion</td><td>';
	if (!empty($ship->max_hardware[9])) echo '+';
	else echo '-';
	echo '</td><td>';
	if (!empty($max_hardware[9])) echo '+';
	else echo '-';
	echo '</td></tr>';

	echo '<tr><td>Jump</td><td>';
	if (!empty($ship->max_hardware[10])) echo '+';
	else echo '-';
	echo '</td><td>';
	if (!empty($max_hardware[10])) echo '+';
	else echo '-';
	echo '</td></tr>';

	echo '<tr><td>Cloak</td><td>';
	if (!empty($ship->max_hardware[8])) echo '+';
	else echo '-';
	echo '</td><td>';
	if (!empty($max_hardware[8])) echo '+';
	else echo '-';
	echo '</td></tr>';

	echo '<tr><td>DCS</td><td>';
	if (!empty($ship->max_hardware[11])) echo '+';
	else echo '-';
	echo '</td><td>';
	if (!empty($max_hardware[11])) echo '+';
	else echo '-';
	echo '</td></tr>';

	echo '</table><br>';

	echo '<table cellspacing="0"class="nobord">';
	echo '<tr><td><hr style="width:200px"></td></tr>';
	echo '<tr><td class="right">+ ';
	echo number_format($cost);
	echo '</td></tr>';
	echo '<tr><td><hr style="width:200px"></td></tr>';
	echo '<tr><td class="right">- ';
	echo number_format( $ship->cost >> 1);
	echo '</td></tr>';
	echo '<tr><td><hr style="width:200px"></td></tr>';
	echo '<tr><td class="right">= ';
	echo number_format( $cost - ($ship->cost >> 1));
	echo '</td></tr>';
	echo '<tr><td><hr style="width:200px"></td></tr>';
	echo '<tr><td class="right">';
	echo print_button($container,'Buy');
	echo '</td></tr>';
	echo '</table>';
}

?>
