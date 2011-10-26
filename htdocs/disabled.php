<?php
try
{
	// includes
	require_once('config.inc');
	require_once(ENGINE . 'Default/smr.inc');
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
	require_once(get_file_loc('SmrAccount.class.inc'));
	require_once(get_file_loc('SmrSession.class.inc'));
	
	$db = new SmrMySqlDatabase();
	
	if (SmrSession::$account_id > 0)
	{
		$account =& SmrAccount::getAccount(SmrSession::$account_id);
		$db->query('SELECT * FROM account_is_closed WHERE account_id = '.SmrSession::$account_id);
		if ($db->nextRecord())
		{
			$time = $db->getField('expires');
		
			$reason = $account->isDisabled();
			if ($time > 0) $reason .= '  Your account is set to reopen ' . date(DEFAULT_DATE_FULL_LONG, $time) . '.';
			else $reason .= '  Your account is set to never reopen.  If you believe this is wrong contact an admin.';
		}
	
	//	SmrSession::destroy();
	}
	else if(USE_COMPATIBILITY && SmrSession::$old_account_id > 0)
	{
		foreach(Globals::getCompatibilityDatabases('Game') as $databaseClassName => $gameType)
		{
			require_once(get_file_loc($databaseClassName.'.class.inc'));
			$db = new $databaseClassName();
			$db->query('SELECT * FROM account_is_closed JOIN closing_reason USING(reason_id) WHERE account_id = '.SmrSession::$old_account_id);
			if ($db->nextRecord())
			{
				$time = $db->getField('expires');
				$reason = $db->getField('reason');
			
				if ($time > 0) $reason .= '  Your account is set to reopen ' . date(DEFAULT_DATE_FULL_LONG, $time) . '.';
				else $reason .= '  Your account is set to never reopen.  If you believe this is wrong contact an admin.';
			}
		}
	//	SmrSession::destroy();
	}
	 else $reason = 'Accessing Account Information Failed.  Contact an admin if you have questions.';
	
	$template->assign('Message',$reason);
	require_once(LIB . 'Login/loginSmarty.php');
}
catch(Exception $e)
{
	handleException($e);
}
?>