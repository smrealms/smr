<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$allianceID = $var['alliance_id'] ?? $player->getAllianceID();

$alliance = SmrAlliance::getAlliance($allianceID, $player->getGameID());
$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

$db = Smr\Database::getInstance();
$dbResult = $db->read('
SELECT
sum(mines) as tot_mines,
sum(combat_drones) as tot_cds,
sum(scout_drones) as tot_sds
FROM sector_has_forces JOIN player ON player.game_id=sector_has_forces.game_id AND sector_has_forces.owner_id=player.account_id
WHERE player.game_id=' . $db->escapeNumber($alliance->getGameID()) . '
AND player.alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
AND expire_time >= ' . $db->escapeNumber(Smr\Epoch::time()));

$hardwareTypes = Globals::getHardwareTypes();

$total = [];
$totalCost = [];
if ($dbResult->hasRecord()) {
	$dbRecord = $dbResult->record();
	// Get total number of forces
	$total['Mines'] = $dbRecord->getInt('tot_mines');
	$total['CDs'] = $dbRecord->getInt('tot_cds');
	$total['SDs'] = $dbRecord->getInt('tot_sds');
	// Get total cost of forces
	$totalCost['Mines'] = $total['Mines'] * $hardwareTypes[HARDWARE_MINE]['Cost'];
	$totalCost['CDs'] = $total['CDs'] * $hardwareTypes[HARDWARE_COMBAT]['Cost'];
	$totalCost['SDs'] = $total['SDs'] * $hardwareTypes[HARDWARE_SCOUT]['Cost'];
}
$template->assign('Total', $total);
$template->assign('TotalCost', $totalCost);

$dbResult = $db->read('
SELECT sector_has_forces.*
FROM player
JOIN sector_has_forces ON player.game_id = sector_has_forces.game_id AND player.account_id = sector_has_forces.owner_id
WHERE player.game_id=' . $db->escapeNumber($alliance->getGameID()) . '
AND player.alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
AND expire_time >= ' . $db->escapeNumber(Smr\Epoch::time()) . '
ORDER BY sector_id ASC');

$forces = [];
foreach ($dbResult->records() as $dbRecord) {
	$forces[] = SmrForce::getForce($player->getGameID(), $dbRecord->getInt('sector_id'), $dbRecord->getInt('owner_id'), false, $dbRecord);
}
$template->assign('Forces', $forces);
