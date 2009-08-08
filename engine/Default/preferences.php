<?php
$template->assign('PageTopic','PREFERENCES');

if (isset($var['reason']))
	$template->assign('Reason',$var['reason']);

$template->assign('PreferencesFormHREF', SmrSession::get_new_href(create_container('preferences_processing.php', '')));

$template->assign('PreferencesConfirmFormHREF', SmrSession::get_new_href(create_container('skeleton.php', 'preferences_confirm.php')));

$transferAccounts = array();
if(SmrSession::$game_id>0)
{
	$db->query('SELECT account_id,player_name,player_id FROM player WHERE game_id = '.SmrSession::$game_id.' ORDER BY player_name');
	while ($db->nextRecord())
	{
		$transferAccounts[$db->getField('account_id')] = $db->getField('player_name') .' ('. $db->getField('player_id').')';
	}
}
else
{
	$db->query('SELECT * FROM account ORDER BY login');
	while ($db->nextRecord())
	{
		$transferAccounts[$db->getField('account_id')] = $db->getField('login');
	}
}
$template->assignByRef('TransferAccounts',$transferAccounts);
?>