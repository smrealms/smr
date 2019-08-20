<?php declare(strict_types=1);
try {
	require_once('config.inc');

	$gameID = $_GET['game'];
	if (!SmrSession::hasAccount() || !Globals::isValidGame($gameID)) {
		header('Location: /login.php');
		exit;
	}

	$account = SmrSession::getAccount();
	if (!SmrGame::getGame($gameID)->isEnabled() && !$account->hasPermission(PERMISSION_UNI_GEN)) {
		header('location: /error.php?msg=You do not have permission to view this map!');
		exit;
	}

	$nodes = [];
	$links = [];

	// The d3 graph nodes are the galaxies
	foreach (SmrGalaxy::getGameGalaxies($gameID) as $galaxy) {
		$nodes[] = [
			'name' => $galaxy->getName(),
			'id' => $galaxy->getGalaxyID(),
			'group' => array_search($galaxy->getGalaxyType(), SmrGalaxy::TYPES),
			'size' => $galaxy->getSize(),
		];
	}

	// The d3 graph links are the warp connections between galaxies
	$db = new SmrMySqlDatabase();
	$db->query('SELECT sector_id, warp FROM sector WHERE warp !=0 AND game_id = ' . $db->escapeNumber($gameID));
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

} catch (Throwable $e) {
	handleException($e);
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title><?php echo PAGE_TITLE . ": " . SmrGame::getGame($gameID)->getName(); ?></title>
		<meta charset="utf-8">
		<style>
		body { background-image: url("images/stars2.png"); }
		</style>
	</head>

	<body>
		<script src="https://d3js.org/d3.v5.min.js"></script>
		<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
		<script>
			const graph = <?php echo $data; ?>;
		</script>
		<script src="js/map_warps.js"></script>
	</body>
</html>
