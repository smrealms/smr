<?php
$action = $_REQUEST['action'];
if ($action == 'Skip >>') {

	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'universe_create_weapons.php';
	$container['game_id']	= $var['game_id'];
	forward($container);

}
$id = $_REQUEST['id'];
if (!isset($id))
	create_error('Couldn\'t determine how many shipyards to add!');

$db2 = new SmrMySqlDatabase();

reset($id);
foreach($id as $location_type_id => $temp_array) {

	foreach($temp_array as $galaxy_id => $amount) {

		$count = 0;

		// get one sector where we put it
		$db->query('SELECT * FROM sector
					WHERE game_id = ' . $db->escapeNumber($var['game_id']) . '
						AND galaxy_id = ' . $db->escapeNumber($galaxy_id) . '
					ORDER BY rand()');
		while ($count < $amount && $db->nextRecord()) {
			$sector_id = $db->getInt('sector_id');

			// does this sector already have a ship yard?
			$db2->query('SELECT *
						FROM location
						WHERE game_id = ' . $db->escapeNumber($var['game_id']) . '
							AND sector_id = ' . $db->escapeNumber($sector_id));
			if ($db2->getNumRows() > 0) continue;

			// ok we did $count locations so far
			$count++;

			// now putting the location in
			$db2->query('INSERT INTO location (game_id, sector_id, location_type_id)
						VALUES (' . $db->escapeNumber($var['game_id']) . ', ' . $db->escapeNumber($sector_id) . ', ' . $db->escapeNumber($location_type_id) . ')');
		}
	}
}

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'universe_create_weapons.php';
$container['game_id']	= $var['game_id'];
forward($container);

?>