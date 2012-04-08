<?php
function draw_rand_array($array, $draws) {

	$lastIndex = count($array) - 1;

	while($draws >= 1) {

		if ($lastIndex > 0)
			$rndIndex = mt_rand(0, $lastIndex);
		else
			$rndIndex = 0;

		$dummy = array_splice($array, $rndIndex, 1);
		$return[] = $dummy[0];
		$draws--;
		$lastIndex--;

	}

	// if it has only one element we don't return an array
	if (sizeof($return) == 1)
		return $return[0];
	else
		return $return;

}


$action = $_REQUEST['action'];
if ($action == 'Skip >>') {

	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'universe_create_planets.php';
	$container['game_id']	= $var['game_id'];
	forward($container);

}

$db2 = new SmrMySqlDatabase();

$goods = array();
$curr_good_class = 0;

$db->query('SELECT * FROM good ORDER BY good_class');
while ($db->nextRecord()) {

	$good_class = $db->getField('good_class');
	$good_id = $db->getField('good_id');

	if($good_class != $curr_good_class) {

		if (is_array($curr_goods))
			$goods[$curr_good_class] = $curr_goods;
		$curr_goods = array();
		$curr_good_class = $good_class;

	}

	$curr_goods[] = '00'.$good_id;

}
$goods[$curr_good_class] = $curr_goods;

$ports = $_REQUEST['ports'];
$input = $_REQUEST['input'];
foreach($ports as $galaxy_id => $amount) {

	// get array for current gal (and sort it)
	$levels = $input[$galaxy_id];

	// get a sector to put a port in
	$db->query('SELECT sector_id FROM sector
				WHERE game_id = ' . $db->escapeNumber($var['game_id']) . '
					AND galaxy_id = ' . $db->escapeNumber($galaxy_id) . '
				ORDER BY rand()');

	$count = 0;

	// we stop if we added all ports for his gal or if we don't have any free sectors
	while ($count < $amount && $db->nextRecord()) {

		$sector_id = $db->getField('sector_id');

		// does this sector has a fed?
		$db2->query('SELECT * FROM location
				WHERE game_id = ' . $db->escapeNumber($var['game_id']) . '
					AND sector_id = ' . $db->escapeNumber($sector_id) . '
					AND location_type_id = ' . $db->escapeNumber(FED));
		if ($db2->getNumRows() > 0) continue;


		// does this sector already have a port?
		$db2->query('SELECT sector_id FROM port WHERE game_id = ' . $db->escapeNumber($var['game_id']) . ' AND sector_id = ' . $db->escapeNumber($sector_id));
		if ($db2->getNumRows() > 0) continue;
		

		// ok we did $count ports so far
		$count++;

		// determine which lvl this port should have
		$rnd = mt_rand() / mt_getrandmax();

		$curr_sum = 0;
		for ($i = 1; $i < 10; $i++) {

			$curr_sum += $levels[$i] / 100;

			if ($rnd < $curr_sum) {

				$level = $i;
				break;

			}

		}

		if ($galaxy_id > 8)
			$race_id = ($count % 9) + 1;
		else
			$race_id = $galaxy_id + 1;

		$credits = ($level - 1) * 3000000;
		$shields = $level * 1000;
		$armour = $level * 1000;
		$drones = $level * 100;

		// insert port into db
		$db2->query('INSERT INTO port
					(game_id, sector_id, level, credits, race_id, shields, armour, combat_drones)
					VALUES (' . $db2->escapeNumber($var['game_id']) . ', ' . $db2->escapeNumber($sector_id) . ', ' . $db2->escapeNumber($level) . ', ' . $db2->escapeNumber($credits) . ', ' . $db2->escapeNumber($race_id) . ', ' . $db2->escapeNumber($shields) . ', ' . $db2->escapeNumber($armour) . ', ' . $db2->escapeNumber($drones) .')');

		// get a temp of that array with all the good classes
		// only working with that one!
		$curr_goods = $goods;

		// add 4 from good_class one
		$good_ids = draw_rand_array(& $curr_goods[1], 4);

		// now go through each lvl. and add one good from it's class each time
		for ($level_count = 2; $level_count <= $level; $level_count++) {

			if ($level_count == 1 || $level_count == 2)
				$good_class = 1;
			if ($level_count == 3 || $level_count == 4 || $level_count == 5 || $level_count == 6)
				$good_class = 2;
			if ($level_count == 7 || $level_count == 8 || $level_count == 9)
				$good_class = 3;

			// get one good
			$good_ids[] = draw_rand_array(& $curr_goods[$good_class], 1);

		}

		// Make sure everything is good and random
		shuffle($good_ids);

		// We rig matters so that there is at least one good bought/sold
		// and the number sold doesn't hugely outweigh the number sold
		// since we can only fit 9 on the local/galaxy map without exploding the table
		$num_goods = count($good_ids);
		$range_low = 1 + floor(($num_goods) * 0.35);
		$range_high = floor(($num_goods) * 0.67);

		$num_sold = mt_rand($range_low,$range_high);

		$bought = $sold = 0;
		for($i=0;$i<$num_goods;++$i) {
			if($i < $num_sold) {
				$transaction = 'Sell';
			}
			else {
				$transaction = 'Buy';
			}
			$good_id = $good_ids[$i];
			$db2->query('INSERT INTO port_has_goods
				(game_id, sector_id, good_id, transaction_type, amount)
				VALUES(' . $db2->escapeNumber($var['game_id']) . ', ' . $db2->escapeNumber($sector_id) . ', ' . $db2->escapeNumber($good_id) . ', ' . $db2->escapeString($transaction) . ', 0)');

		}
	}
}

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'universe_create_planets.php';
$container['game_id']	= $var['game_id'];
forward($container);

?>
