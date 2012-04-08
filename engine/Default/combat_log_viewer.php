<?php

$template->assign('PageTopic','Combat Logs');
require_once(get_file_loc('menu.inc'));
create_combat_log_menu();
if (isset($_REQUEST['action'])) {
	$submitAction = $_REQUEST['action'];
	if ($submitAction == 'Save' && isset($_REQUEST['id'])) {
		//save the logs we checked
		$log_ids = array_keys($_REQUEST['id']);
		$db->query('SELECT * FROM combat_logs WHERE log_id IN (' . $db->escapeArray($log_ids) . ') LIMIT ' . count($log_ids));
		$unsavedLogs = array();
		$savedLogs = array();
		while ($db->nextRecord()) {
			if (!$db->getField('saved')) {
				$unsavedLogs[] = $db->getInt('log_id');
			}
			else {
				$savedLogs[] = array($db->getInt('game_id'),$db->getField('type'),$db->getInt('sector_id'),$db->getInt('timestamp'),$db->getField('attacker_id'),$db->getInt('attacker_alliance_id'),$db->getInt('defender_id'),$db->getInt('defender_alliance_id'),$db->getField('result'));
			}
		}
		if (sizeof($unsavedLogs)) {
			$db->query('UPDATE combat_logs SET saved = ' . $db->escapeNumber($player->getAccountID()) . ' WHERE log_id IN (' . $db->escapeArray($unsavedLogs) . ') LIMIT ' . count($log_ids));
		}
		if (sizeof($savedLogs)) {
			foreach ($savedLogs as $a) {
				if (!empty($query)) {
					$query .= ',';
				}
				$query .= '('.$a[0].','.$db->escape_string($a[1]).','.$a[2].','.$a[3].','.$a[4].','.$a[5].','.$a[6].','.$a[7].',' . $db->escape_string($a[8]) . ',' . $db->escapeNumber($player->getAccountID()) . ')';
			}
			$db->query('INSERT INTO combat_logs
						(game_id,type,sector_id,timestamp,attacker_id,attacker_alliance_id,defender_id,defender_alliance_id,result,saved)
						VALUES ' . $query);
		}
		$PHP_OUTPUT.=('<div align="center">' . count($log_ids) . ' logs have been saved.<br />');
		//back to viewing
		$var['action'] = $var['old_action'];
	} elseif (!isset($_REQUEST['id'])) $var['action'] = $var['old_action'];
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
		$PHP_OUTPUT.= '<span class="red bold">Error:</span> log not found';
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
		$query = 'saved = ' . $db->escapeNumber($player->getAccountID());
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
	$db->query('SELECT count(*) as count FROM combat_logs WHERE '.$query.' LIMIT 1');
	if($db->nextRecord()) {
		$totalLogs = $db->getInt('count');
	}
	$db->query('SELECT attacker_id,defender_id,timestamp,sector_id,log_id FROM combat_logs WHERE '.$query.' ORDER BY log_id DESC, sector_id LIMIT '.($page*COMBAT_LOGS_PER_PAGE).', '.COMBAT_LOGS_PER_PAGE);
}

if($action != 5) {
	$PHP_OUTPUT.= '<div align="center">';
	if($db->getNumRows() > 0) {
		$num = $db->getNumRows();
		$PHP_OUTPUT.= 'There ';
		if ($totalLogs > 1) {
			$PHP_OUTPUT.= 'are ';
		}
		else {
			$PHP_OUTPUT.= 'is ';
		}
		$PHP_OUTPUT.= $totalLogs;
		switch($action) {
			case 0:
				$PHP_OUTPUT.= ' personal';
			break;
			case 1:
				$PHP_OUTPUT.= ' alliance';
			break;
			case 2:
				$PHP_OUTPUT.= ' port';
			break;
			case 3:
				$PHP_OUTPUT.= ' planet';
			break;
			case 4:
				$PHP_OUTPUT.= ' saved';
			break;
			case 6:
				$PHP_OUTPUT.= ' force';
			break;
		}
		$PHP_OUTPUT.= ' log';
		if ($num > 1) {
			$PHP_OUTPUT.= 's';
		}
		$PHP_OUTPUT.= ' available for viewing of which '.$num;
		if ($totalLogs > 1) {
			$PHP_OUTPUT.= ' are';
		}
		else {
			$PHP_OUTPUT.= ' is';
		}
		$PHP_OUTPUT.=' being shown.<br /><br />';
		$container = create_container('skeleton.php', 'combat_log_viewer.php');
		$container['action'] = 5;
		$container['old_action'] = $action;
		$container['direction'] = 0;
		$actions = array();
		$actions[] = array('View','View');
		$actions[] = array('Save','Save');
		$form = create_form($container,$actions);
		$PHP_OUTPUT.= $form['form'];
		$container = $var;
		$container['page'] = $page-1;
		$PHP_OUTPUT.='<table class="fullwidth center"><tr><td style="width: 30%" valign="middle">';
		if($page>0) {
			$PHP_OUTPUT.='<a href="'.SmrSession::getNewHREF($container).'"><img src="'.URL.'/images/album/rew.jpg" alt="Previous Page" border="0"></a>';
		}
		$PHP_OUTPUT.='</td><td>';
		$PHP_OUTPUT.= $form['submit']['View'];
		$PHP_OUTPUT.= '&nbsp;';
		$PHP_OUTPUT.= $form['submit']['Save'];
		$PHP_OUTPUT.='</td><td style="width: 30%" valign="middle">';
		$container['page'] = $page+1;
		if(($page+1)*COMBAT_LOGS_PER_PAGE<$totalLogs) {
			$PHP_OUTPUT.='<a href="'.SmrSession::getNewHREF($container).'"><img src="'.URL.'/images/album/fwd.jpg" alt="Next Page" border="0"></a>';
		}
		$PHP_OUTPUT.='</td></tr></table>';
		$PHP_OUTPUT.= '<br /><br /><table class="standard fullwidth">';
		$PHP_OUTPUT.= '<tr><th>View</th><th>Date</th><th>Sector</th><th>Attacker</th><th>Defender</th></tr>';
		while($db->nextRecord()) {
			//attacker_id,defender_id,timestamp,sector_id,log_id
			$logs[$db->getField('log_id')] = array($db->getField('attacker_id'),$db->getField('defender_id'),$db->getField('timestamp'),$db->getField('sector_id'));
			$playerIDs[] = $db->getField('attacker_id');
			$playerIDs[] = $db->getField('defender_id');
		}
		array_unique($playerIDs);
		$db->query('SELECT * FROM player
					WHERE account_id IN (' . $db->escapeArray($playerIDs) . ')
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					LIMIT ' . sizeof($playerIDs));
		while ($db->nextRecord()) {
			$currPlayer =& SmrPlayer::getPlayer($db->getRow(), $player->getGameID());
			$players[$db->getField('account_id')] = $currPlayer->getLinkedDisplayName(false);
		}
		foreach ($logs as $id => $info) {
			$container['id'] = $id;
			$PHP_OUTPUT.= '<tr>';
			$PHP_OUTPUT.= '<td class="center shrink">';
			$PHP_OUTPUT.= '<input type="checkbox" value="on" name="id[' . $id . ']">';
			$PHP_OUTPUT.= '</td>';
			if(isset($players[$info[0]])) {
				$attacker_name = $players[$info[0]];
			}
			else {
				$attacker_name = 'Unknown Attacker';
			}
			if(isset($players[$info[1]])) {
				$defender_name = $players[$info[1]];
			}
			else {
				$defender_name = 'Unknown Defender';
			}
			$PHP_OUTPUT.= '<td class="shrink noWrap">' . date(DATE_FULL_SHORT,$info[2]) . '</td>';
			$PHP_OUTPUT.= '<td class="center shrink">' . $info[3] . '</td>';
			$PHP_OUTPUT.= '<td>' . $attacker_name . '</td>';
			$PHP_OUTPUT.= '<td>' . $defender_name . '</td>';
		}
		$PHP_OUTPUT.= '</table>';
		$PHP_OUTPUT.= '</form>';
	}
	else {
		$PHP_OUTPUT.= 'No combat logs found';
	}
	$PHP_OUTPUT.= '</div>';
}

?>