<?php

$template->assign('PageTopic','Log Console');

$loggedAccounts = array();

$db->query('SELECT account_id as account_id, login, count(*) as number_of_entries
			FROM account_has_logs
			JOIN account USING(account_id)
			GROUP BY account_id');
if ($db->getNumRows())
{
	$db2 = new SmrMySqlDatabase();
	while ($db->nextRecord())
	{
		$accountID = $db->getInt('account_id');
		$loggedAccounts[$accountID] = array('AccountID' => $accountID,
								'Login' => $db->getField('login'),
								'TotalEntries' => $db->getField('number_of_entries'),
								'Checked' => is_array($var['account_ids']) && in_array($accountID, $var['account_ids']),
								'Notes' => '');

		$db2->query('SELECT notes FROM log_has_notes WHERE account_id = '.$accountID);
		if ($db2->nextRecord())
			$loggedAccounts[$accountID]['Notes'] = nl2br($db2->getField('notes'));
	}
	
	// put hidden fields in for log type to have all fields selected on next page.
	$logTypes = array();
	$db->query('SELECT log_type_id FROM log_type');
	while ($db->nextRecord())
		$logTypes[] = $db->getInt('log_type_id');
	$template->assignByRef('LogTypes', $logTypes);
	
	$template->assign('LogConsoleFormHREF', SmrSession::get_new_href(create_container('skeleton.php', 'log_console_detail.php')));
	$template->assign('AnonAccessHRE', SmrSession::get_new_href(create_container('skeleton.php', 'log_anonymous_account.php')));
}
$template->assignByRef('LoggedAccounts',$loggedAccounts);
?>