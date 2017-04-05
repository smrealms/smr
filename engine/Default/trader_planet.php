<?php
$template->assign('PageTopic','Planets');

require_once(get_file_loc('menu.inc'));
create_trader_menu();

$db->query('SELECT sector_id FROM planet
			WHERE planet.game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND planet.owner_id = ' . $db->escapeNumber($player->getAccountID()));
$traderPlanets = array();
while ($db->nextRecord()) {
	$sectorID = $db->getInt('sector_id');
	$traderPlanets[$sectorID] =& SmrPlanet::getPlanet($player->getGameID(),$sectorID);
	$traderPlanets[$sectorID]->getCurrentlyBuilding(); //In case anything gets updated here we want to do it before template.
}
$template->assignByRef('TraderPlanets',$traderPlanets);

// Determine if the player can view bonds on the planet list
// If not in an alliance, they can always view bonds
$viewBonds = TRUE;
if ($player->hasAlliance()) {
	$role_id = $player->getAllianceRole($player->getAllianceID());
	$db->query('
	SELECT *
	FROM alliance_has_roles
	WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
	AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
	AND role_id = ' . $db->escapeNumber($role_id)
	);
	$db->nextRecord();
	$viewBonds = $db->getBoolean('view_bonds');
}
$template->assignByRef('CanViewBonds', $viewBonds);

if ($player->hasAlliance()) {
	$db->query('SELECT planet.sector_id FROM player
				JOIN planet ON player.account_id = planet.owner_id AND player.game_id = planet.game_id
				WHERE player.game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND player.account_id != ' . $db->escapeNumber($player->getAccountID()) . '
					AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
				ORDER BY planet.sector_id');
	$alliancePlanets = array();
	while ($db->nextRecord()) {
		$sectorID = $db->getInt('sector_id');
		$alliancePlanets[$sectorID] =& SmrPlanet::getPlanet($player->getGameID(),$sectorID);
		$alliancePlanets[$sectorID]->getCurrentlyBuilding(); //In case anything gets updated here we want to do it before template.
	}
	$template->assignByRef('AlliancePlanets',$alliancePlanets);
}

?>
