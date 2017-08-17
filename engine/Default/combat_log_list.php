<?php

$template->assign('PageTopic','Combat Logs');
require_once(get_file_loc('menu.inc'));
create_combat_log_menu();

// Do we have a message from the processing page?
if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
}

// $var['action'] is the page log type
if(!isset($var['action'])) {
	SmrSession::updateVar('action',0);
}
$action = $var['action'];

switch($action) {
	case 0:
	case 1:
		$query = 'type=\'PLAYER\'';
	break;
	case 2:
		$query = 'type=\'PORT\'';
	break;
	case 3:
		$query = 'type=\'PLANET\'';
	break;
	case 4:
		$query = 'EXISTS(
					SELECT 1
					FROM player_saved_combat_logs
					WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
						AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND log_id = c.log_id
				)';
	break;
	case 6:
		$query = 'type=\'FORCE\'';
	break;
	default:
}
if(isset($query) && $query) {
	$query .= ' AND game_id=' . $db->escapeNumber($player->getGameID());
	if($action != 0 //personal
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
	case 0:
		$type = ' personal';
	break;
	case 1:
		$type = ' alliance';
	break;
	case 2:
		$type = ' port';
	break;
	case 3:
		$type = ' planet';
	break;
	case 4:
		$type = ' saved';
	break;
	case 6:
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
	$template->assign('CanDelete', $action == 4);
	$template->assign('CanSave', $action != 4);

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

?>
