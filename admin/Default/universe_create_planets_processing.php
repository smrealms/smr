<?php
$action = $_REQUEST['action'];
if ($action == 'Skip >>') {
	$container = create_container('skeleton.php', 'universe_create_ships.php');
	$container['game_id']	= $var['game_id'];
	forward($container);
}
$planet = $_REQUEST['planet'];
if (!isset($planet))
	create_error('Couldn\'t determine how many planets to add!');

$db2 = new SmrMySqlDatabase();
$db2->query('SELECT * FROM game WHERE game_id = ' . $db->escapeNumber($var['game_id']));
$db2->nextRecord();
$date = $db2->getField('start_date');
list ($year, $month, $day) = explode('-', $date);
// adjust the time so it is game start time
$time = mktime(0,0,0,$month,$day,$year);
reset($planet);
foreach($planet as $galaxy_id => $amount) {

	$count = 0;

	// get a sector with none port
	$db->query('SELECT * FROM sector
				WHERE game_id = ' . $db->escapeNumber($var['game_id']) . '
				AND galaxy_id = ' . $db->escapeNumber($galaxy_id) . '
				ORDER BY rand()');
	while ($count < $amount && $db->nextRecord()) {
		$sector_id = $db->getInt('sector_id');

		// does this sector have a fed beacon??
		$db2->query('SELECT * FROM location
					WHERE game_id = ' . $db->escapeNumber($var['game_id']) . '
						AND sector_id = ' . $db->escapeNumber($sector_id) . '
						AND location_type_id = 1');
		if ($db2->getNumRows() > 0) continue;

		// ok we did $count planets so far
		$count++;
		$inhabitable_time = $time + pow(mt_rand(45, 85), 3);

		// insert planet into db
		$db2->query('INSERT INTO planet (game_id, sector_id, inhabitable_time)
					VALUES (' . $db->escapeNumber($var['game_id']) . ', ' . $db->escapeNumber($sector_id) . ', ' . $db->escapeNumber($inhabitable_time) . ')');

	}
}

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'universe_create_ships.php';
$container['game_id']	= $var['game_id'];
forward($container);

?>