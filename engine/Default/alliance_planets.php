<?php
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id',$player->getAllianceID());
}

$alliance =& SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic', $alliance->getAllianceName(false, true));
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

// Determine if the player can view bonds on the planet list
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
$template->assignByRef('CanViewBonds', $viewBonds);

// Ugly, but funtional
$db->query('
SELECT planet.sector_id
FROM player
JOIN planet ON player.game_id = planet.game_id AND player.account_id = planet.owner_id
WHERE player.game_id=' . $db->escapeNumber($alliance->getGameID()) . '
AND player.alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
ORDER BY planet.sector_id
');

$alliancePlanets = array();
while ($db->nextRecord()) {
	$sectorID = $db->getInt('sector_id');
	$alliancePlanets[$sectorID] =& SmrPlanet::getPlanet($player->getGameID(),$sectorID);
	$alliancePlanets[$sectorID]->getCurrentlyBuilding(); //In case anything gets updated here we want to do it before template.
}
$template->assignByRef('AlliancePlanets',$alliancePlanets);

?>
