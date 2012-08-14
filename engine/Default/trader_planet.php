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

$isLeader = false;

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
	
	// Check if they are alliance leader (which will show bonds)
	$db->query('SELECT COUNT(1) FROM alliance WHERE alliance.alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND alliance.game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance.leader_id = ' . $db->escapeNumber($player->getAccountID()) . ' LIMIT 1');
	$db->next_record();
	if($db->getInt('COUNT(1)')) {
		$isLeader = true;
	}
}

$template->assignByRef('isLeader',$isLeader);

?>