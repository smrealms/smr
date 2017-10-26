<?php

$template->assign('PageTopic','Combat Logs');
require_once(get_file_loc('menu.inc'));
create_combat_log_menu();

if(!isset($var['log_ids']) && !isset($var['current_log'])) {
	create_error('You must select a combat log to view');
}

// Create a container for the next/previous log.
// We initialize it with the current $var, then modify it to set
// which log to view when we press the next/previous log buttons.
$container = create_container('skeleton.php', 'combat_log_viewer.php', $var);
if($var['current_log'] > 0) {
	$container['current_log'] = $var['current_log'] - 1;
	$template->assign('PreviousLogHREF',SmrSession::getNewHREF($container));
}
if($var['current_log'] < count($container['log_ids']) - 1) {
	$container['current_log'] = $var['current_log'] + 1;
	$template->assign('NextLogHREF',SmrSession::getNewHREF($container));
}

// Set properties for the current display page
$display_id = $var['log_ids'][$var['current_log']];
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

?>
