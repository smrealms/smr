<?php

$smarty->assign('PageTopic','Combat Logs');
include(ENGINE . 'global/menue.inc');
$smarty->assign('MenuBar',create_combat_log_menue());
if (isset($_REQUEST['action'])) {
	$submitAction = $_REQUEST['action'];
	if ($submitAction == 'Save' && isset($_POST['id'])) {
		//save the logs we checked
		$log_ids = array_keys($_POST['id']);
		$db->query('SELECT * FROM combat_logs WHERE log_id IN (' . implode(',', $log_ids) . ') LIMIT ' . count($log_ids));
		$unsavedLogs = array();
		$savedLogs = array();
		while ($db->nextRecord()) {
			if (!$db->getField('saved'))
				$unsavedLogs[] = $db->getField('log_id');
			else
				$savedLogs[] = array($db->getField('game_id'),$db->getField('type'),$db->getField('sector_id'),$db->getField('timestamp'),$db->getField('attacker_id'),$db->getField('attacker_alliance_id'),$db->getField('defender_id'),$db->getField('defender_alliance_id'),$db->getField('result'));
		}
		if (sizeof($unsavedLogs))
			$db->query('UPDATE combat_logs SET saved = ' . $player->getAccountID() . ' WHERE log_id IN (' . implode(',', $unsavedLogs) . ') LIMIT ' . count($log_ids));
		if (sizeof($savedLogs)) {
			foreach ($savedLogs as $a) {
				if (!empty($query)) $query .= ',';
				$query .= '('.$a[0].','.$db->escape_string($a[1]).','.$a[2].','.$a[3].','.$a[4].','.$a[5].','.$a[6].','.$a[7].',' . $db->escape_string($a[8]) . ',' . $player->getAccountID() . ')';
			}
			$db->query('INSERT INTO combat_logs 
						(game_id,type,sector_id,timestamp,attacker_id,attacker_alliance_id,defender_id,defender_alliance_id,result,saved)
						VALUES ' . $query);
		}
		$PHP_OUTPUT.=('<div align="center">' . count($log_ids) . ' logs have been saved.<br />');
		//back to viewing
		$var['action'] = $var['old_action'];
	} elseif (!isset($_POST['id'])) $var['action'] = $var['old_action'];
}
if(!isset($var['action'])) $action = 0;
else $action = $var['action'];

if($action == 5) {

	if(!isset($_POST['id']) && !isset($var['log_ids'])) {
		$action = $var['old_action'];
	}
	else {
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'combat_log_viewer.php';
		if(!isset($var['log_ids'])) {
			$container['log_ids'] = array_keys($_POST['id']);
			sort($container['log_ids']);
			$container['current_log'] = 0;
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
		if(count($container['log_ids']) > 1)
		{
			if($container['current_log'])
			{
				$container['direction'] = 1;
				$smarty->assign('PreviousLogHREF',SmrSession::get_new_href($container));
			}
			$PHP_OUTPUT.= '&nbsp;&nbsp;&nbsp;';
			if($container['current_log'] < count($container['log_ids']) - 1)
			{
				$container['direction'] = 2;
				$smarty->assign('NextLogHREF',SmrSession::get_new_href($container));
			}
		}
	}
}

if(isset($display_id))
{
	$db->query('SELECT timestamp,sector_id,result,type FROM combat_logs WHERE log_id=' . $display_id . ' LIMIT 1');

	if($db->nextRecord())
	{
		$smarty->assign('CombatLogSector',$db->getField('sector_id'));
		$smarty->assign('CombatLogTimestamp',date(DATE_FULL_SHORT,$db->getField('timestamp')));
		$results = gzuncompress($db->getField('result'));
		if($db->getField('type')=='PLAYER' || $db->getField('type') == 'FORCE')
			$results = unserialize($results);
		$smarty->assign('CombatResultsType',$db->getField('type'));
		$smarty->assign_by_ref('CombatResults',$results);
	}
	else
	{
		$PHP_OUTPUT.= '<span class="red bold">Error:</span> log not found';
	}
}

switch($action){
	case(0):
	case(1):
		$query = 'type=\'PLAYER\' AND game_id=' . SmrSession::$game_id;
	break;
	case(2):
		$query = 'type=\'PORT\' AND game_id=' . SmrSession::$game_id;
		break;
	case(3):
		$query = 'type=\'PLANET\' AND game_id=' . SmrSession::$game_id;
		break;
	case(4):
		$query = 'saved = ' . $player->getAccountID() . ' AND game_id = ' . $player->getGameID();
		break;
	case(6):
		$query = 'type=\'FORCE\' AND game_id=' . SmrSession::$game_id;
		break;
	default:
}
if(isset($query) && $query)
{
	if($action != 0 //personal
		&& $player->hasAlliance())
	{
		$query .= ' AND (attacker_alliance_id=' . $player->getAllianceID() . ' OR defender_alliance_id=' . $player->getAllianceID() . ') ';
	}
	else
	{
		$query .= ' AND (attacker_id=' . $player->getAccountID() . ' OR defender_id=' . $player->getAccountID() . ') ';
	}
	$db->query('SELECT attacker_id,defender_id,timestamp,sector_id,log_id FROM combat_logs WHERE '.$query.' ORDER BY log_id DESC, sector_id');
}

if($action != 5) {
	$PHP_OUTPUT.= '<div align="center">';
	if($db->getNumRows() > 0) {
		$num = $db->getNumRows();
		$PHP_OUTPUT.= 'There ';
		if ($num > 1) $PHP_OUTPUT.= 'are ';
		else $PHP_OUTPUT.= 'is ';
		$PHP_OUTPUT.= $num;
		switch($action){
			case(0):
				$PHP_OUTPUT.= ' personal';
				break;
			case(1):
				$PHP_OUTPUT.= ' alliance';
				break;
			case(2):
				$PHP_OUTPUT.= ' port';
				break;
			case(3):
				$PHP_OUTPUT.= ' planet';
				break;
			case(4):
				$PHP_OUTPUT.= ' saved';
			case(6):
				$PHP_OUTPUT.= ' force';
				break;
		}
		$PHP_OUTPUT.= ' log';
		if ($num > 1) $PHP_OUTPUT.= 's';
		$PHP_OUTPUT.= ' available for viewing.<br /><br />';
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'combat_log_viewer.php';
		$container['action'] = 5;
		$container['old_action'] = $action;
		$container['direction'] = 0;
		$actions = array();
		$actions[] = array('View','View');
		$actions[] = array('Save','Save');
		$form = create_form($container,$actions);
		$PHP_OUTPUT.= $form['form'];
		$PHP_OUTPUT.= $form['submit']['View'];
		$PHP_OUTPUT.= '&nbsp';
		$PHP_OUTPUT.= $form['submit']['Save'];
		$PHP_OUTPUT.= '<br /><br /><table cellspacing="0" cellpadding="5" class="standard fullwidth">';
		$PHP_OUTPUT.= '<tr><th>View</th><th>Date</th><th>Sector</th><th>Attacker</th><th>Defender</th></tr>';
		while($db->nextRecord()) {
			//attacker_id,defender_id,timestamp,sector_id,log_id
			$logs[$db->getField('log_id')] = array($db->getField('attacker_id'),$db->getField('defender_id'),$db->getField('timestamp'),$db->getField('sector_id'));
			$player_ids[] = $db->getField('attacker_id');
			$player_ids[] = $db->getField('defender_id');
		}
		array_unique($player_ids);
		$db->query('SELECT player_name, account_id FROM player
					WHERE account_id IN (' . implode(',',$player_ids) . ')
					AND game_id = '.SmrSession::$game_id.'
					LIMIT ' . sizeof($player_ids));
		while ($db->nextRecord()) $players[$db->getField('account_id')] = stripslashes($db->getField('player_name'));
		foreach ($logs as $id => $info) {
			$container['id'] = $id;
			$PHP_OUTPUT.= '<tr>';
			$PHP_OUTPUT.= '<td class="center shrink">';
			$PHP_OUTPUT.= '<input type="checkbox" value="on" name="id[' . $id . ']">';
			$PHP_OUTPUT.= '</td>';
			if(isset($players[$info[0]]))
				$attacker_name = $players[$info[0]];
			else
				$attacker_name = 'Unknown Attacker';
			if(isset($players[$info[1]]))
				$defender_name = $players[$info[1]];
			else
				$defender_name = 'Unknown Defender';
			$PHP_OUTPUT.= '<td class="shrink nowrap">' . date(DATE_FULL_SHORT,$info[2]) . '</td>';
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