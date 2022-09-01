<?php declare(strict_types=1);

use Smr\Database;
use Smr\Request;

try {
	require_once('../bootstrap.php');

	$session = Smr\Session::getInstance();

	$gameID = Request::getInt('game');
	if (!$session->hasAccount() || !SmrGame::gameExists($gameID)) {
		header('Location: /login.php');
		exit;
	}
	$game = SmrGame::getGame($gameID);
	$account = $session->getAccount();

	if (!$game->isEnabled() && !$account->hasPermission(PERMISSION_UNI_GEN)) {
		create_error('You do not have permission to view this map!');
	}

	$nodes = [];
	$links = [];

	// The d3 graph nodes are the galaxies
	foreach ($game->getGalaxies() as $galaxy) {
		$nodes[] = [
			'name' => $galaxy->getName(),
			'id' => $galaxy->getGalaxyID(),
			'group' => array_search($galaxy->getGalaxyType(), SmrGalaxy::TYPES),
			'size' => $galaxy->getSize(),
		];
	}

	// The d3 graph links are the warp connections between galaxies
	$db = Database::getInstance();
	$dbResult = $db->read('SELECT sector_id, warp FROM sector WHERE warp !=0 AND game_id = ' . $db->escapeNumber($gameID));
	foreach ($dbResult->records() as $dbRecord) {
		$warp1 = SmrSector::getSector($gameID, $dbRecord->getInt('sector_id'));
		$warp2 = SmrSector::getSector($gameID, $dbRecord->getInt('warp'));
		$links[] = [
			'source' => $warp1->getGalaxy()->getName(),
			'target' => $warp2->getGalaxy()->getName(),
		];
	}

	// Encode the data for use in the javascript
	$data = json_encode([
		'nodes' => $nodes,
		'links' => $links,
	]);

	$template = Smr\Template::getInstance();
	$template->assign('GameName', $game->getName());
	$template->assign('GraphData', $data);
	$template->display('map_warps.php');
} catch (Throwable $e) {
	handleException($e);
}
