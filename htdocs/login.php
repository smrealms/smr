<?php

try
{
	
	// ********************************
	// *
	// * I n c l u d e s   h e r e
	// *
	// ********************************
	
	require_once('config.inc');
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
	require_once(ENGINE . 'Default/smr.inc');
	require_once(get_file_loc('SmrSession.class.inc'));
	require_once(get_file_loc('SmrAccount.class.inc'));
	
	
	// ********************************
	// *
	// * S e s s i o n
	// *
	// ********************************
	
	
	if (SmrSession::$account_id > 0)
	{
		// creates a new user account object
		$account =& SmrAccount::getAccount(SmrSession::$account_id);
	
		// update last login column
		$account->updateLastLogin();
	
		$container = array();
		$container['url'] = 'validate_check.php';
		$href = SmrSession::getNewHREF($container,true);
		SmrSession::update();
	
		header('Location: '.$href);
		exit;
	}
	
	if(isset($_REQUEST['msg']))
		$template->assign('Message',htmlentities(trim($_REQUEST['msg']),ENT_COMPAT,'utf-8'));
		
	require_once(LIB . 'Login/loginSmarty.php');

}
catch(Exception $e)
{
	handleException($e);
}
?>