<?
$action = $_REQUEST['action'];
if ($action == 'Skip >>') {

	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'universe_create_ports.php';
	$container['game_id']	= $var['game_id'];
	forward($container);

}
$id = $_REQUEST['id'];
if (!isset($id))
	create_error('Error while transmitting data from previous form!');

include(get_file_loc('universe_create_location.inc'));

foreach($id as $location_type_id => $location_array) {

	// first we deal with race HQ
	if ($location_type_id == $GOVERNMENT) {

		foreach($location_array as $galaxy_id => $hq_type) {

			// 1 means no hq
			if ($hq_type == 1)
				continue;

			// put actual hq in
			$hq_sector = create_location($var['game_id'], $galaxy_id, $GOVERNMENT + $hq_type);

			// ship shop and co (for racials only)
			create_location($var['game_id'], $galaxy_id, $RACIAL_SHIPS + $hq_type - 1, $hq_sector);
			create_location($var['game_id'], $galaxy_id, $RACIAL_SHOPS + $hq_type - 1, $hq_sector);

			// create fed around hq
			create_fed($var['game_id'], $galaxy_id, $hq_sector);

		}

	} elseif ($location_type_id == $FED) {

		foreach($location_array as $galaxy_id => $checked) {

			// ignore this sector if it already got a hq
			if ($id[$GOVERNMENT][$galaxy_id] > 1)
				continue;

			// create fed
			if ($checked == 'on')
				create_fed($var['game_id'], $galaxy_id);

		}

	} elseif ($location_type_id == $UNDERGROUND) {

		foreach($location_array as $galaxy_id => $checked) {

			if ($checked == 'on')
				create_location($var['game_id'], $galaxy_id, $UNDERGROUND);

		}

	} else {

		foreach($location_array as $galaxy_id => $amount) {

			for ($i = 0; $i < $amount; $i++) {

				create_location($var['game_id'], $galaxy_id, $location_type_id);

			}

		}

	}

}

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'universe_create_ports.php';
$container['game_id']	= $var['game_id'];
forward($container);

?>