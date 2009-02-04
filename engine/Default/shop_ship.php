<?
$db2 = new SmrMySqlDatabase();
$smarty->assign('PageTopic','SHIP DEALER');

$db->query('SELECT 
	ship_type.ship_name as ship_name,
	ship_type.ship_type_id as ship_type_id,
	ship_type.cost as cost,
	ship_type.lvl_needed as lvl_needed
	FROM location, location_sells_ships,ship_type
	WHERE location.sector_id=' . $player->getSectorID() . '
	AND location.game_id=' . $player->getGameID() . '
	AND location_sells_ships.location_type_id = location.location_type_id
	AND ship_type.ship_type_id = location_sells_ships.ship_type_id
');

if ($db->getNumRows() > 0 ) {

	$PHP_OUTPUT.= '<table cellspacing="0" class="standard"><tr><th>Name</th><th>Cost</th><th>Action</th></tr>';

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'shop_ship.php';

	while ($db->nextRecord()) {

		$ship_name = $db->getField('ship_name');
		$ship_type_id = $db->getField('ship_type_id');
		$cost = $db->getField('cost');
        $level_needed = $db->getField('lvl_needed');

		$container['ship_id'] = $ship_type_id;
        $container['level_needed'] = $level_needed;
		$PHP_OUTPUT.= '<tr><td>' . $ship_name;
		$PHP_OUTPUT.= '</td><td>' . $cost . '</td><td>';
		$PHP_OUTPUT.= create_button($container,'View Details');
		$PHP_OUTPUT.= '</td></tr>';
	}

	$PHP_OUTPUT.= '</table><br />';

}

if (isset($var['ship_id'])) {

	$db->query('SELECT * FROM ship_type WHERE ship_type_id = ' . $var['ship_id'] . ' LIMIT 1');
	if ($db->nextRecord()) {

		$ship_name			= $db->getField('ship_name');
		$speed				= $db->getField('speed');
		$cost				= $db->getField('cost');
		$hardpoint 			= $db->getField('hardpoint');
		$race_id 			= $db->getField('race_id');
		$buyer_restriction	= $db->getField('buyer_restriction');
        $level_needed		= $db->getField('lvl_needed');

	}
	$db->query('SELECT game_speed FROM game WHERE game_id=' . $player->getGameID() . ' LIMIT 1');
	$db->nextRecord();
	$game_speed = $db->getField('game_speed');
	$dis_speed = $speed * $game_speed;
	$max_hardware = array();

	// get supported hardware from db
	$db->query('SELECT * FROM ship_type_support_hardware NATURAL JOIN hardware_type WHERE ship_type_id=' . $var['ship_id']);

	while ($db->nextRecord())
	    $max_hardware[$db->getField('hardware_type_id')] = $db->getField('max_amount');

	$db->query('SELECT * FROM hardware_type');
	while ($db->nextRecord()) {

		$hardware_type_id = $db->getField('hardware_type_id');

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

	$PHP_OUTPUT.= '<table cellspacing="0"class="standard"><tr><th>&nbsp;</th><th>';
	$PHP_OUTPUT.= $ship->getName();
	$PHP_OUTPUT.= '</th><th>';
	$PHP_OUTPUT.= $ship_name;
	$PHP_OUTPUT.= '</th></tr>';

	$PHP_OUTPUT.= '<tr><td>Shields</td><td>';
	$PHP_OUTPUT.= $ship->getMaxShields();
	$PHP_OUTPUT.= '</td><td>';
	$PHP_OUTPUT.= $max_hardware[1];
	$PHP_OUTPUT.= '</td></tr>';

	$PHP_OUTPUT.= '<tr><td>Armour</td><td>';
	$PHP_OUTPUT.= $ship->getMaxArmour();
	$PHP_OUTPUT.= '</td><td>';
	$PHP_OUTPUT.= $max_hardware[2];
	$PHP_OUTPUT.= '</td></tr>';

	$PHP_OUTPUT.= '<tr><td>Combat Drones</td><td>';
	$PHP_OUTPUT.= $ship->getMaxCDs();
	$PHP_OUTPUT.= '</td><td>';
	$PHP_OUTPUT.= $max_hardware[4];
	$PHP_OUTPUT.= '</td></tr>';

	$PHP_OUTPUT.= '<tr><td>Scout Drones</td><td>';
	$PHP_OUTPUT.= $ship->getMaxSDs();
	$PHP_OUTPUT.= '</td><td>';
	$PHP_OUTPUT.= $max_hardware[5];
	$PHP_OUTPUT.= '</td></tr>';

	$PHP_OUTPUT.= '<tr><td>Mines</td><td>';
	$PHP_OUTPUT.= $ship->getMaxMines();
	$PHP_OUTPUT.= '</td><td>';
	$PHP_OUTPUT.= $max_hardware[6];
	$PHP_OUTPUT.= '</td></tr>';

	$PHP_OUTPUT.= '<tr><td>Cargo Holds</td><td>';
	$PHP_OUTPUT.= $ship->getMaxCargoHolds();
	$PHP_OUTPUT.= '</td><td>';
	$PHP_OUTPUT.= $max_hardware[3];
	$PHP_OUTPUT.= '</td></tr>';

	$PHP_OUTPUT.= '<tr><td>Hardpoints</td><td>';
	$PHP_OUTPUT.= $ship->getHardpoints();
	$PHP_OUTPUT.= '</td><td>';
	$PHP_OUTPUT.= $hardpoint;
	$PHP_OUTPUT.= '</td></tr>';

	$PHP_OUTPUT.= '<tr><td>Speed</td><td>';
	$PHP_OUTPUT.= $ship->getSpeed() * $game_speed;
	$PHP_OUTPUT.= ' TPH</td><td>';
	$PHP_OUTPUT.= $dis_speed;
	$PHP_OUTPUT.= ' TPH</td></tr>';

	$PHP_OUTPUT.= '<tr><td>Scanner</td><td>';
	if ($ship->canHaveScanner()) $PHP_OUTPUT.= '+';
	else $PHP_OUTPUT.= '-';
	$PHP_OUTPUT.= '</td><td>';
	if (!empty($max_hardware[7])) $PHP_OUTPUT.= '+';
	else $PHP_OUTPUT.= '-';
	$PHP_OUTPUT.= '</td></tr>';

	$PHP_OUTPUT.= '<tr><td>Illusion</td><td>';
	if ($ship->canHaveIllusion()) $PHP_OUTPUT.= '+';
	else $PHP_OUTPUT.= '-';
	$PHP_OUTPUT.= '</td><td>';
	if (!empty($max_hardware[9])) $PHP_OUTPUT.= '+';
	else $PHP_OUTPUT.= '-';
	$PHP_OUTPUT.= '</td></tr>';

	$PHP_OUTPUT.= '<tr><td>Jump</td><td>';
	if ($ship->canHaveJump()) $PHP_OUTPUT.= '+';
	else $PHP_OUTPUT.= '-';
	$PHP_OUTPUT.= '</td><td>';
	if (!empty($max_hardware[10])) $PHP_OUTPUT.= '+';
	else $PHP_OUTPUT.= '-';
	$PHP_OUTPUT.= '</td></tr>';

	$PHP_OUTPUT.= '<tr><td>Cloak</td><td>';
	if ($ship->canHaveCloak()) $PHP_OUTPUT.= '+';
	else $PHP_OUTPUT.= '-';
	$PHP_OUTPUT.= '</td><td>';
	if (!empty($max_hardware[8])) $PHP_OUTPUT.= '+';
	else $PHP_OUTPUT.= '-';
	$PHP_OUTPUT.= '</td></tr>';

	$PHP_OUTPUT.= '<tr><td>DCS</td><td>';
	if ($ship->canHaveDCS()) $PHP_OUTPUT.= '+';
	else $PHP_OUTPUT.= '-';
	$PHP_OUTPUT.= '</td><td>';
	if (!empty($max_hardware[11])) $PHP_OUTPUT.= '+';
	else $PHP_OUTPUT.= '-';
	$PHP_OUTPUT.= '</td></tr>';

	$PHP_OUTPUT.= '</table><br />';

	$PHP_OUTPUT.= '<table cellspacing="0"class="nobord">';
	$PHP_OUTPUT.= '<tr><td><hr style="width:200px"></td></tr>';
	$PHP_OUTPUT.= '<tr><td class="right">+ ';
	$PHP_OUTPUT.= number_format($cost);
	$PHP_OUTPUT.= '</td></tr>';
	$PHP_OUTPUT.= '<tr><td><hr style="width:200px"></td></tr>';
	$PHP_OUTPUT.= '<tr><td class="right">- ';
	$PHP_OUTPUT.= number_format( $ship->getCost() >> 1);
	$PHP_OUTPUT.= '</td></tr>';
	$PHP_OUTPUT.= '<tr><td><hr style="width:200px"></td></tr>';
	$PHP_OUTPUT.= '<tr><td class="right">= ';
	$PHP_OUTPUT.= number_format( $cost - ($ship->getCost() >> 1));
	$PHP_OUTPUT.= '</td></tr>';
	$PHP_OUTPUT.= '<tr><td><hr style="width:200px"></td></tr>';
	$PHP_OUTPUT.= '<tr><td class="right">';
	$PHP_OUTPUT.=create_button($container,'Buy');
	$PHP_OUTPUT.= '</td></tr>';
	$PHP_OUTPUT.= '</table>';
}

?>
