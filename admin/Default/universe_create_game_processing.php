<?php

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'universe_create_galaxies.php';
$action = $_REQUEST['action'];
if (isset($_REQUEST['game_id'])) $game_id = $_REQUEST['game_id'];
if ($action == 'Create >>') {
	$game_description = $_REQUEST['game_description'];
	if (strlen($game_description) > 255)
		create_error('No more than 255 characters are allowed in the game description!');
	list($day,$month,$year) = explode("/",$_POST['start_date']);
	$start_date = mktime(0,0,0,$month,$day,$year);
	list($day,$month,$year) = explode("/",$_POST['end_date']);
	$end_date = mktime(0,0,0,$month,$day,$year);
	$game_name = $_REQUEST['game_name'];
	$max_player = $_REQUEST['max_player'];
	$game_type = $_REQUEST['game_type'];
	$credits = $_REQUEST['credits'];
	$speed = $_REQUEST['speed'];
	// create the game
	$db->query('INSERT INTO game (game_name, game_description, start_date, end_date, max_players, game_type, credits_needed, game_speed)
				VALUES(' . $db->escapeString($game_name) . ', ' . $db->escapeString($game_description) . ', ' . $db->escapeString($start_date) . ', ' . $db->escapeString($end_date) . ', ' . $db->escapeNumber($max_player) . ', ' . $db->escapeString($game_type) . ', ' . $db->escapeNumber($credits) . ', ' . $db->escapeNumber($speed) . ')');

	$container['game_id'] = $db->getInsertID();

	$db->query('SELECT * FROM race');
	$race_count = $db->getNumRows();

	for ($race_id_1 = 2; $race_id_1 <= $race_count; $race_id_1++) {

		for ($race_id_2 = 2; $race_id_2 <= $race_count; $race_id_2++) {

			if ($race_id_1 == $race_id_2)
				$relation = 500;
			else
				$relation = -500;

			$db->query('INSERT INTO race_has_relation
						(game_id, race_id_1, race_id_2, relation)
						VALUES(' . $db->escapeNumber($container['game_id']) . ', ' . $db->escapeNumber($race_id_1) . ', ' . $db->escapeNumber($race_id_2) . ', ' . $db->escapeNumber($relation) . ')');
		}
	}
}
else
	$container['game_id'] = $game_id;

forward($container);

?>