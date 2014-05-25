<?php

$template->assign('PageTopic','Combat Logs');
require_once(get_file_loc('menu.inc'));
create_combat_log_menu();
if (isset($_REQUEST['action'])) {
	$submitAction = $_REQUEST['action'];
	if (isset($_REQUEST['id'])) {
		$logIDs = array_keys($_REQUEST['id']);
		if($submitAction == 'Save') {
			//save the logs we checked
			// Query means people can only save logs that they are allowd to view.
			$db->query('INSERT IGNORE INTO player_saved_combat_logs (account_id, game_id, log_id)
						SELECT ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeNumber($player->getGameID()) . ', log_id
						FROM combat_logs
						WHERE log_id IN (' . $db->escapeArray($logIDs) . ')
							AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
							AND (
								attacker_id = ' . $db->escapeNumber($player->getAccountID()) . '
								OR defender_id = ' . $db->escapeNumber($player->getAccountID()) .
								($player->hasAlliance() ? '
									OR attacker_alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
									OR defender_alliance_id = ' . $db->escapeNumber($player->getAllianceID())
								: '') . '
							)
						LIMIT ' . count($logIDs));
			$template->assign('Message', $db->getChangedRows() . ' new logs have been saved.');
			//back to viewing
			$var['action'] = $var['old_action'];
		}
		else if($submitAction == 'Delete') {
			$db->query('DELETE FROM player_saved_combat_logs
						WHERE log_id IN (' . $db->escapeArray($logIDs) . ')
							AND account_id = ' . $db->escapeNumber($player->getAccountID()) . '
							AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
						LIMIT ' . count($logIDs));
			$template->assign('Message', $db->getChangedRows() . ' new logs have been deleted.');
			//back to viewing
			$var['action'] = $var['old_action'];
		}
	}
	else {
		$var['action'] = $var['old_action'];
	}
}
if(!isset($var['action'])) {
	SmrSession::updateVar('action',0);
}
$action = $var['action'];

if($action == 5) {
	if(!isset($_REQUEST['id']) && !isset($var['log_ids'])) {
		$action = $var['old_action'];
	}
	else {
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'combat_log_viewer.php';
		if(!isset($var['log_ids'])) {
			$container['log_ids'] = array_keys($_REQUEST['id']);
			sort($container['log_ids']);
			$container['current_log'] = 0;
			SmrSession::updateVar('log_ids',$container['log_ids']);
			SmrSession::updateVar('current_log',0);
		}
		else {
			$container['log_ids'] = $var['log_ids'];
			$container['current_log'] = $var['current_log'];
		}
		$container['action'] = 5;
		
		if($var['direction']) {
			if($var['direction'] == 1) {
				--$container['current_log'];
			}
			else {
				++$container['current_log'];
			}
		}
		$display_id = $container['log_ids'][$container['current_log']];
		if(count($container['log_ids']) > 1) {
			if($container['current_log'] > 0) {
				$container['direction'] = 1;
				$template->assign('PreviousLogHREF',SmrSession::getNewHREF($container));
			}
			if($container['current_log'] < count($container['log_ids']) - 1) {
				$container['direction'] = 2;
				$template->assign('NextLogHREF',SmrSession::getNewHREF($container));
			}
		}
	}
}

if(isset($display_id)) {
	//These are required in case we unzip these classes.
	require_once(get_file_loc('SmrPort.class.inc'));
	require_once(get_file_loc('SmrPlanet.class.inc'));
	$db->query('SELECT timestamp,sector_id,result,type FROM combat_logs WHERE log_id=' . $db->escapeNumber($display_id) . ' LIMIT 1');

	if($db->nextRecord()) {
		$template->assign('CombatLogSector',$db->getField('sector_id'));
		$template->assign('CombatLogTimestamp',date(DATE_FULL_SHORT,$db->getField('timestamp')));
		$results = unserialize(gzuncompress($db->getField('result')));
		$template->assign('CombatResultsType',$db->getField('type'));
		$template->assignByRef('CombatResults',$results);
	}
	else {
		create_error('Combat log not found');
	}
}

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

if($action != 5) {
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

	$logs = array();
	if($db->getNumRows() > 0) {
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
		$container = create_container('skeleton.php', 'combat_log_viewer.php');
		$container['action'] = 5;
		$container['old_action'] = $action;
		$container['direction'] = 0;
		$template->assign('LogFormHREF', SmrSession::getNewHREF($container));

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
}

?>