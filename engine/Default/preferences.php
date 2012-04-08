<?php
$template->assign('PageTopic','Preferences');

if (isset($var['reason']))
	$template->assign('Reason',$var['reason']);

$template->assign('PreferencesFormHREF', SmrSession::getNewHREF(create_container('preferences_processing.php', '')));

$template->assign('PreferencesConfirmFormHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'preferences_confirm.php')));

$transferAccounts = array();
//if(SmrSession::$game_id>0)
// {
//	$db->query('SELECT account_id,player_name,player_id FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY player_name');
//	while ($db->nextRecord())
// {
//		$transferAccounts[$db->getField('account_id')] = $db->getField('player_name') .' ('. $db->getField('player_id').')';
//	}
//}
//else {
	$db->query('SELECT account_id,hof_name FROM account WHERE validated = ' . $db->escapeBoolean(true) . ' ORDER BY hof_name');
	while ($db->nextRecord()) {
		$transferAccounts[$db->getField('account_id')] = $db->getField('hof_name');
	}
//}
$template->assignByRef('TransferAccounts',$transferAccounts);
?>