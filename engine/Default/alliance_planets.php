<?php
if (!isset($var['alliance_id']))
	SmrSession::updateVar('alliance_id',$player->getAllianceID());

$alliance =& SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic',$alliance->getAllianceName() . ' (' . $alliance->getAllianceID() . ')');
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

// Ugly, but funtional
$db->query('
SELECT planet.sector_id
FROM player
JOIN planet ON player.game_id = planet.game_id
WHERE player.game_id=' . $alliance->getGameID() . '
AND player.alliance_id=' . $alliance->getAllianceID() . '
ORDER BY planet.sector_id
');

$alliancePlanets = array();

while ($db->nextRecord())
{
	$sectorID = $db->getInt('sector_id');
	$alliancePlanets[$sectorID] =& SmrPlanet::getPlanet($player->getAllianceID(),$sectorID);
	$alliancePlanets[$sectorID]->getCurrentlyBuilding(); //In case anything gets updated here we want to do it before template.
}
$template->assignByRef('AlliancePlanets',$alliancePlanets);
?>