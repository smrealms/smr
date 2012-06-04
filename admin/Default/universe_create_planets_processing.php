<?
$action = $_REQUEST['action'];
if ($action == 'Skip >>') {

	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'universe_create_ships.php';
	$container['game_id']	= $var['game_id'];
	forward($container);

}
$planet = $_REQUEST['planet'];
if (!isset($planet))
	create_error('Couldn\'t determine how many planets to add!');

$db2 = new SmrMySqlDatabase();
$db2->query('SELECT * FROM game WHERE game_id = '.$var['game_id']);
$db2->next_record();
$date = $db2->f('start_date');
list ($year, $month, $day) = split('-', $date);
// adjust the time so it is game start time
$time = mktime(0,0,0,$month,$day,$year);
reset($planet);
foreach($planet as $galaxy_id => $amount) {

	$count = 0;

	// get a sector with none port
	$db->query('SELECT * FROM sector WHERE game_id = ' . $var['game_id'] . ' AND ' .
										  'galaxy_id = '.$galaxy_id.' ' .
									'ORDER BY rand()');
	while ($count < $amount && $db->next_record()) {

		$sector_id = $db->f('sector_id');

		// does this sector have a fed beacon??
		$db2->query('SELECT * FROM location WHERE game_id = ' . $var['game_id'] . ' AND ' .
												 'sector_id = '.$sector_id.' AND ' .
												 'location_type_id = 1');
		if ($db2->nf() > 0) continue;

		// ok we did $count planets so far
		$count++;
		$inhabitable_time = $time + pow(mt_rand(45, 85), 3);

		// insert planet into db
		$db2->query('INSERT INTO planet (game_id, sector_id, inhabitable_time) ' .
							   'VALUES (' . $var['game_id'] . ', '.$sector_id.', '.$inhabitable_time.')');

	}
}

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'universe_create_ships.php';
$container['game_id']	= $var['game_id'];
forward($container);

?>