<?
$action = $_REQUEST['action'];
if ($action == 'Skip >>') {

	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'universe_create_admin.php';
	$container['game_id']	= $var['game_id'];
	forward($container);

}
$id = $_REQUEST['id'];
if (!isset($id))
	create_error('Couldn\'t determine how many hardware shops to add!');

$db2 = new SmrMySqlDatabase();

reset($id);
foreach($id as $location_type_id => $temp_array) {

	foreach($temp_array as $galaxy_id => $amount) {

		$count = 0;

		// get one sector where we put it
		$db->query('SELECT * FROM sector WHERE game_id = ' . $var['game_id'] . ' AND ' .
											  'galaxy_id = '.$galaxy_id.' ' .
										'ORDER BY rand()');
		while ($count < $amount && $db->next_record()) {

			$sector_id = $db->f('sector_id');

			// does this sector already have a ship yard?
			$db2->query('SELECT * FROM location WHERE game_id = ' . $var['game_id'] . ' AND ' .
													 'sector_id = '.$sector_id);
			if ($db2->nf() > 0) continue;

			// ok we did $count locations so far
			$count++;

			// now putting the location in
			$db2->query('INSERT INTO location (game_id, sector_id, location_type_id) ' .
									  'VALUES (' . $var['game_id'] . ', '.$sector_id.', '.$location_type_id.')');
		}
	}
}

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'universe_create_admin.php';
$container['game_id']	= $var['game_id'];
forward($container);

?>