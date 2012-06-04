<?

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'universe_create_galaxies.php';
$action = $_REQUEST['action'];
if (isset($_REQUEST['game_id'])) $game_id = $_REQUEST['game_id'];
if ($action == 'Create >>') {
	$game_description = $_REQUEST['game_description'];
	if (strlen($game_description) > 255)
		create_error('Not more than 255 characters in game description!');
	$start_date = $_REQUEST['start_date'];
	$end_date = $_REQUEST['end_date'];
	$game_name = $_REQUEST['game_name'];
	$max_player = $_REQUEST['max_player'];
	$game_type = $_REQUEST['game_type'];
	$credits = $_REQUEST['credits'];
	$speed = $_REQUEST['speed'];
	// create the game
	$db->query('INSERT INTO game (game_name, game_description, start_date, end_date, max_players, game_type, credits_needed, game_speed) ' .
						  'VALUES('.$db->escapeString($game_name).', '.$db->escapeString($game_description).', '.$db->escapeString($start_date).', '.$db->escapeString($end_date).', '.$max_player.', '.$db->escapeString($game_type).', '.$credits.', '.$speed.')');

	$container['game_id']	= $db->getInsertID();

	$db->query('SELECT * FROM race');
	$race_count = $db->getNumRows();

	for ($race_id_1 = 2; $race_id_1 <= $race_count; $race_id_1++) {

		for ($race_id_2 = 2; $race_id_2 <= $race_count; $race_id_2++) {

			if ($race_id_1 == $race_id_2)
				$relation = 500;
			else
				$relation = -500;

			$db->query('INSERT INTO race_has_relation ' .
					   '(game_id, race_id_1, race_id_2, relation) ' .
					   'VALUES(' . $container['game_id'] . ', '.$race_id_1.', '.$race_id_2.', '.$relation.')');

		}

	}

} else
	$container['game_id']	= $game_id;

forward($container);

?>