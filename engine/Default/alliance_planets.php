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

$alliancePlanets = $alliance->getPlanets();
$template->assignByRef('AlliancePlanets',$alliancePlanets);

?>
