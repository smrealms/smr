<?php

$template->assign('PageTopic','Combat Logs');
Menu::combat_log();

// Do we have a message from the processing page?
if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
}

// $var['action'] is the page log type
if(!isset($var['action'])) {
	SmrSession::updateVar('action', COMBAT_LOG_PERSONAL);
}
$action = $var['action'];

switch($action) {
	case COMBAT_LOG_PERSONAL:
	case COMBAT_LOG_ALLIANCE:
		$query = 'type=\'PLAYER\'';
	break;
	case COMBAT_LOG_PORT:
		$query = 'type=\'PORT\'';
	break;
	case COMBAT_LOG_PLANET:
		$query = 'type=\'PLANET\'';
	break;
	case COMBAT_LOG_SAVED:
		$query = 'EXISTS(
					SELECT 1
					FROM player_saved_combat_logs
					WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
						AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND log_id = c.log_id
				)';
	break;
	case COMBAT_LOG_FORCE:
		$query = 'type=\'FORCE\'';
	break;
	default:
}
if(isset($query) && $query) {
	$query .= ' AND game_id=' . $db->escapeNumber($player->getGameID());
	if($action != COMBAT_LOG_PERSONAL
		&& $player->hasAlliance()) {
		$query .= ' AND (attacker_alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' OR defender_alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ') ';
	}
	else {
		$query .= ' AND (attacker_id=' . $db->escapeNumber($player->getAccountID()) . ' OR defender_id=' . $db->escapeNumber($player->getAccountID()) . ') ';
	}
	$page = 0;
	if(isset($var['page'])) {
		$page = $var['page'];
	}
	$db->query('SELECT count(*) as count FROM combat_logs c WHERE '.$query.' LIMIT 1');
	if($db->nextRecord()) {
		$totalLogs = $db->getInt('count');
		$template->assign('TotalLogs', $totalLogs);
	}
	$db->query('SELECT attacker_id,defender_id,timestamp,sector_id,log_id FROM combat_logs c WHERE '.$query.' ORDER BY log_id DESC, sector_id LIMIT '.($page*COMBAT_LOGS_PER_PAGE).', '.COMBAT_LOGS_PER_PAGE);
}

function getParticipantName($accountID, $sectorID) {
	global $player;
	if($accountID == ACCOUNT_ID_PORT) {
		return '<a href="' . Globals::getPlotCourseHREF($player->getSectorID(), $sectorID) . '">Port <span class="sectorColour">#' . $sectorID . '</span></a>';
	}
	if($accountID == ACCOUNT_ID_PLANET) {
		return '<span class="yellow">Planetary Defenses</span>';
	}

	return SmrPlayer::getPlayer($accountID, $player->getGameID())->getLinkedDisplayName(false);
}

// For display purposes, describe the type of log
switch($action) {
	case COMBAT_LOG_PERSONAL:
		$type = ' personal';
	break;
	case COMBAT_LOG_ALLIANCE:
		$type = ' alliance';
	break;
	case COMBAT_LOG_PORT:
		$type = ' port';
	break;
	case COMBAT_LOG_PLANET:
		$type = ' planet';
	break;
	case COMBAT_LOG_SAVED:
		$type = ' saved';
	break;
	case COMBAT_LOG_FORCE:
		$type = ' force';
	break;
}
$template->assign('LogType', $type);

// Construct the list of logs of this type
$logs = array();
if($db->getNumRows() > 0) {
	// 'View' and 'Save' share the same form, so we use 'old_action' as a
	// way to return to this page when we only want to save the logs.
	$container = create_container('combat_log_list_processing.php');
	$container['old_action'] = $action;
	$template->assign('LogFormHREF', SmrSession::getNewHREF($container));

	// Set the links for the "view next/previous log list" buttons
	$container = $var;
	if($page>0) {
		$container['page'] = $page-1;
		$template->assign('PreviousPage', SmrSession::getNewHREF($container));
	}
	if(($page+1)*COMBAT_LOGS_PER_PAGE<$totalLogs) {
		$container['page'] = $page+1;
		$template->assign('NextPage', SmrSession::getNewHREF($container));
	}
	// Saved logs
	$template->assign('CanDelete', $action == COMBAT_LOG_SAVED);
	$template->assign('CanSave', $action != COMBAT_LOG_SAVED);

	while($db->nextRecord()) {
		$sectorID = $db->getInt('sector_id');
		$logs[$db->getField('log_id')] = array(
			'Attacker' => getParticipantName($db->getInt('attacker_id'), $sectorID),
			'Defender' => getParticipantName($db->getInt('defender_id'), $sectorID),
			'Time' => $db->getInt('timestamp'),
			'Sector' => $sectorID
		);
	}
}
$template->assign('Logs', $logs);
