<?
$action = $_REQUEST['action'];
$db->query('SELECT * FROM sector WHERE game_id = ' . $var['game_id'] . ' GROUP BY galaxy_id');
$num_gals = $db->nf();
if ($action == 'Skip >>' || $num_gals == 1) {

	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'universe_create_location.php';
	$container['game_id']	= $var['game_id'];
	forward($container);

}

$db2 = new SmrMySqlDatabase();
$warp = $_REQUEST['warp'];
foreach($warp as $galaxy_id_1 => $temp_array) {

	foreach($temp_array as $galaxy_id_2 => $warp_value) {

		if ($warp_value == 'on') {

			unset($found);

			// get one sector where we put it
			$db->query('SELECT sector_id FROM sector WHERE game_id = ' . $var['game_id'] . ' AND ' .
														  'galaxy_id = '.$galaxy_id_1.' ' .
													'ORDER BY rand()');
			while (!isset($found) && $db->next_record()) {

				$sector_id_1 = $db->f('sector_id');

				// does this sector already has a warp?
				$db2->query('SELECT * FROM warp WHERE game_id = ' . $var['game_id'] . ' AND ' .
													 '(sector_id_1 = '.$sector_id_1.' OR sector_id_2 = '.$sector_id_1.')');
				if ($db2->nf() > 0) continue;

				// ok we found a sector
				$found = true;
			}

			unset($found);

			// get one sector where we put it
			$db->query('SELECT sector_id FROM sector WHERE game_id = ' . $var['game_id'] . ' AND ' .
														  'galaxy_id = '.$galaxy_id_2.' ' .
													'ORDER BY rand()');
			while (!isset($found) && $db->next_record()) {

				$sector_id_2 = $db->f('sector_id');

				// does this sector already has a warp?
				$db2->query('SELECT * FROM warp WHERE game_id = ' . $var['game_id'] . ' AND ' .
													 '(sector_id_1 = '.$sector_id_2.' OR sector_id_2 = '.$sector_id_2.')');
				if ($db2->nf() > 0) continue;

				// ok we found a sector
				$found = true;
			}

			// do we have two sector numbers?
			if ($sector_id_1 > 0 && $sector_id_2 > 0) {

				$db->query('INSERT INTO warp (game_id, sector_id_1, sector_id_2) ' .
									  'VALUES(' . $var['game_id'] . ', '.$sector_id_1.', '.$sector_id_2.')');

			}
		}
	}
}

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'universe_create_location.php';
$container['game_id']	= $var['game_id'];
forward($container);

?>