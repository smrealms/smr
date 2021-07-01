<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Examine Planet');

$planet = $player->getSectorPlanet();
$template->assign('ThisPlanet', $planet);

$planetLand =
	!$planet->hasOwner()
	|| $planet->getOwner()->sameAlliance($player)
	|| in_array($player->getAccountID(), Globals::getHiddenPlayers());

if (!$planetLand) {
	// Only check treaties if we can't otherwise land.
	$ownerAllianceID = 0;
	if ($planet->hasOwner()) {
		$ownerAllianceID = $planet->getOwner()->getAllianceID();
	}
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('
		SELECT 1
		FROM alliance_treaties
		WHERE (alliance_id_1 = ' . $db->escapeNumber($ownerAllianceID) . ' OR alliance_id_1 = ' . $db->escapeNumber($player->getAllianceID()) . ')
		AND (alliance_id_2 = ' . $db->escapeNumber($ownerAllianceID) . ' OR alliance_id_2 = ' . $db->escapeNumber($player->getAllianceID()) . ')
		AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
		AND planet_land = 1
		AND official = ' . $db->escapeBoolean(true)
	);
	$planetLand = $dbResult->hasRecord();
}
$template->assign('PlanetLand', $planetLand);
