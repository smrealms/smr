<?php declare(strict_types=1);
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id', $player->getAllianceID());
}

$alliance = SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID(), $alliance->getLeaderID());

$db->query('
SELECT
sum(mines) as tot_mines,
sum(combat_drones) as tot_cds,
sum(scout_drones) as tot_sds
FROM sector_has_forces JOIN player USING (game_id, player_id)
WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
AND expire_time >= ' . $db->escapeNumber(TIME));

$hardwareTypes = Globals::getHardwareTypes();

$total = array();
$totalCost = array();
if ($db->nextRecord()) {
	// Get total number of forces
	$total['Mines'] = $db->getInt('tot_mines');
	$total['CDs'] = $db->getInt('tot_cds');
	$total['SDs'] = $db->getInt('tot_sds');
	// Get total cost of forces
	$totalCost['Mines'] = $total['Mines'] * $hardwareTypes[HARDWARE_MINE]['Cost'];
	$totalCost['CDs'] = $total['CDs'] * $hardwareTypes[HARDWARE_COMBAT]['Cost'];
	$totalCost['SDs'] = $total['SDs'] * $hardwareTypes[HARDWARE_SCOUT]['Cost'];
}
$template->assign('Total', $total);
$template->assign('TotalCost', $totalCost);

$db->query('
SELECT sector_has_forces.*
FROM player
JOIN sector_has_forces USING (game_id, player_id)
WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
AND expire_time >= ' . $db->escapeNumber(TIME) . '
ORDER BY sector_id ASC');

$forces = array();
while ($db->nextRecord()) {
	$forces[] = SmrForce::getForce($player->getGameID(), $db->getInt('sector_id'), $db->getInt('player_id'), false, $db);
}
$template->assign('Forces', $forces);
