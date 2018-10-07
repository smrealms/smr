<?php
try {
	require_once('config.inc');
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
	require_once(LIB . 'Default/Globals.class.inc');
	require_once(get_file_loc('SmrSector.class.inc'));
	require_once(get_file_loc('SmrGalaxy.class.inc'));

	// Require that we are logged and have joined a game
	if (SmrSession::$account_id == 0 || SmrSession::$game_id == 0) {
		header('Location: /login.php');
		exit;
	}

	$galaxyTypes = [
		'Racial' => 1,
		'Neutral' => 2,
		'Planet' => 3,
	];

	$nodes = [];
	$links = [];

	// The d3 graph nodes are the galaxies
	$gameID = SmrSession::$game_id;
	foreach (SmrGalaxy::getGameGalaxies($gameID) as $galaxy) {
		$nodes[] = [
			'name' => $galaxy->getName(),
			'id' => $galaxy->getGalaxyID(),
			'group' => $galaxyTypes[$galaxy->getGalaxyType()],
			'size' => $galaxy->getSize(),
		];
	}

	// The d3 graph links are the warp connections between galaxies
	$db = new SmrMySqlDatabase();
	$db->query('SELECT sector_id, warp FROM sector WHERE warp !=0 AND game_id = '.$db->escapeNumber($gameID));
	while ($db->nextRecord()) {
		$warp1 = SmrSector::getSector($gameID, $db->getInt('sector_id'));
		$warp2 = SmrSector::getSector($gameID, $db->getInt('warp'));
		$links[] = [
			'source' => $warp1->getGalaxyName(),
			'target' => $warp2->getGalaxyName(),
		];
	}

	// Encode the data for use in the javascript
	$data = json_encode([
		'nodes' => $nodes,
		'links' => $links,
	]);

}
catch(Throwable $e) {
	handleException($e);
}
?>

<!DOCTYPE html>
<meta charset="utf-8">

<style>
body { background-image: url("images/stars2.png"); }
</style>

<body></body>

<script src="https://d3js.org/d3.v5.min.js"></script>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script>
	const graph = <?php echo $data; ?>;
</script>
<script src="js/map_warps.js"></script>
