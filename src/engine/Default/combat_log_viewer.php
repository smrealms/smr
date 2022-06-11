<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

if (!isset($var['log_ids']) && !isset($var['current_log'])) {
	create_error('You must select a combat log to view');
}

// Set properties for the current display page
$display_id = $var['log_ids'][$var['current_log']];
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT timestamp,sector_id,result,type FROM combat_logs WHERE log_id=' . $db->escapeNumber($display_id) . ' LIMIT 1');

if (!$dbResult->hasRecord()) {
	create_error('Combat log not found');
}
$dbRecord = $dbResult->record();
$template->assign('CombatLogSector', $dbRecord->getInt('sector_id'));
$template->assign('CombatLogTimestamp', date($account->getDateTimeFormat(), $dbRecord->getInt('timestamp')));
$results = $dbRecord->getObject('result', true);
$template->assign('CombatResultsType', $dbRecord->getString('type'));
$template->assign('CombatResults', $results);

// Create a container for the next/previous log.
// We initialize it with the current $var, then modify it to set
// which log to view when we press the next/previous log buttons.
$container = Page::create('combat_log_viewer.php', $var);
if ($var['current_log'] > 0) {
	$container['current_log'] = $var['current_log'] - 1;
	$template->assign('PreviousLogHREF', $container->href());
}
if ($var['current_log'] < count($container['log_ids']) - 1) {
	$container['current_log'] = $var['current_log'] + 1;
	$template->assign('NextLogHREF', $container->href());
}

$template->assign('PageTopic', 'Combat Logs');
Menu::combatLog();
