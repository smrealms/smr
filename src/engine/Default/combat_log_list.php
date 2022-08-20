<?php declare(strict_types=1);

use Smr\CombatLogType;

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Combat Logs');
Menu::combatLog();

// Do we have a message from the processing page?
if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
}

// $var['action'] is the page log type
if (!isset($var['action'])) {
	$var['action'] = CombatLogType::Personal;
}
/** @var \Smr\CombatLogType $action */
$action = $var['action'];

$query = match ($action) {
	CombatLogType::Personal, CombatLogType::Alliance => 'type=\'PLAYER\'',
	CombatLogType::Port => 'type=\'PORT\'',
	CombatLogType::Planet => 'type=\'PLANET\'',
	CombatLogType::Saved => 'EXISTS(
					SELECT 1
					FROM player_saved_combat_logs
					WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
						AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND log_id = c.log_id
				)',
	CombatLogType::Force => 'type=\'FORCE\'',
};

$query .= ' AND game_id=' . $db->escapeNumber($player->getGameID());
if ($action != CombatLogType::Personal && $player->hasAlliance()) {
	$query .= ' AND (attacker_alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' OR defender_alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ') ';
} else {
	$query .= ' AND (attacker_id=' . $db->escapeNumber($player->getAccountID()) . ' OR defender_id=' . $db->escapeNumber($player->getAccountID()) . ') ';
}

$page = $var['page'] ?? 0;
$dbResult = $db->read('SELECT count(*) as count FROM combat_logs c WHERE ' . $query);
$totalLogs = $dbResult->record()->getInt('count'); // count always returns a record
$template->assign('TotalLogs', $totalLogs);

$dbResult = $db->read('SELECT attacker_id,defender_id,timestamp,sector_id,log_id FROM combat_logs c WHERE ' . $query . ' ORDER BY log_id DESC, sector_id LIMIT ' . ($page * COMBAT_LOGS_PER_PAGE) . ', ' . COMBAT_LOGS_PER_PAGE);

$getParticipantName = function($accountID, $sectorID) use ($player): string {
	if ($accountID == ACCOUNT_ID_PORT) {
		return '<a href="' . Globals::getPlotCourseHREF($player->getSectorID(), $sectorID) . '">Port <span class="sectorColour">#' . $sectorID . '</span></a>';
	}
	if ($accountID == ACCOUNT_ID_PLANET) {
		return '<span class="yellow">Planetary Defenses</span>';
	}
	return SmrPlayer::getPlayer($accountID, $player->getGameID())->getLinkedDisplayName(false);
};

// For display purposes, describe the type of log
$template->assign('LogType', strtolower($action->name));

// Construct the list of logs of this type
$logs = [];
if ($dbResult->hasRecord()) {
	// 'View' and 'Save' share the same form, so we use 'old_action' as a
	// way to return to this page when we only want to save the logs.
	$container = Page::create('combat_log_list_processing.php');
	$container['old_action'] = $action;
	$template->assign('LogFormHREF', $container->href());

	// Set the links for the "view next/previous log list" buttons
	$container = Page::copy($var);
	if ($page > 0) {
		$container['page'] = $page - 1;
		$template->assign('PreviousPage', $container->href());
	}
	if (($page + 1) * COMBAT_LOGS_PER_PAGE < $totalLogs) {
		$container['page'] = $page + 1;
		$template->assign('NextPage', $container->href());
	}
	// Saved logs
	$template->assign('CanDelete', $action == CombatLogType::Saved);
	$template->assign('CanSave', $action != CombatLogType::Saved);

	foreach ($dbResult->records() as $dbRecord) {
		$sectorID = $dbRecord->getInt('sector_id');
		$logs[$dbRecord->getInt('log_id')] = [
			'Attacker' => $getParticipantName($dbRecord->getInt('attacker_id'), $sectorID),
			'Defender' => $getParticipantName($dbRecord->getInt('defender_id'), $sectorID),
			'Time' => $dbRecord->getInt('timestamp'),
			'Sector' => $sectorID,
		];
	}
}
$template->assign('Logs', $logs);
