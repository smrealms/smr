<?php
$action = $_REQUEST['action'];
if ($action == 'Skip >>') {

	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'universe_create_warps.php';
	$container['game_id']	= $var['game_id'];
	forward($container);

}

$sector_count = 0;
$galaxy = $_REQUEST['galaxy'];
$size = $_REQUEST['size'];
for($galaxy_counter = 0; $galaxy_counter < count($galaxy); $galaxy_counter++) {
	$galaxy_id	= $galaxy[$galaxy_counter];
	$curr_size	= $size[$galaxy_counter];

	for($curr_sector = $sector_count + 1; $curr_sector <= $sector_count + $curr_size * $curr_size; $curr_sector++) {

		// specifiy the line number in the current 'block'
		$line = floor(($curr_sector - $sector_count - 1) / $curr_size) + 1;

		// sector numbers on the most left, right, up and down
		$right_border	= $line * $curr_size + $sector_count;
		$left_border	= $right_border - $curr_size + 1;
		$up_border		= $curr_sector - ($curr_size * ($line - 1));
		$down_border	= $curr_sector + ($curr_size * ($curr_size - $line));

		$left = $curr_sector - 1;
		if ($left < $left_border) $left = $right_border;

		$right = $curr_sector + 1;
		if ($right > $right_border) $right = $left_border;

		$up = $curr_sector - $curr_size;
		if ($up < $up_border) $up = $down_border;

		$down = $curr_sector + $curr_size;
		if ($down > $down_border) $down = $up_border;

		$db->query('INSERT INTO sector (sector_id, game_id, galaxy_id, link_up, link_down, link_left, link_right)
					VALUES(' . $db->escapeNumber($curr_sector) . ', ' . $db->escapeNumber($var['game_id']) . ', ' . $db->escapeNumber($galaxy_id) . ', ' . $db->escapeNumber($up) . ', ' . $db->escapeNumber($down) . ', ' . $db->escapeNumber($left) . ', ' . $db->escapeNumber($right) . ')');
	}

	$sector_count += $curr_size * $curr_size;

}

$container = array();
$container['url']			= 'universe_create_galaxies_processing.php';
$container['game_id']		= $var['game_id'];
$container['galaxy']		= $galaxy;
$container['galaxy_idx']	= 0;
forward($container);

?>