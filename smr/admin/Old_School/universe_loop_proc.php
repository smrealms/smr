<?

//this gets us around the universe problem temporarly so I can add stuff to universe.
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'universe_create_planets.php';
$container['game_id'] = $game_id;
forward($container);

?>