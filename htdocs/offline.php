<?php
try {
	// includes
	require_once('config.inc');
	require_once(LIB . 'Default/smr.inc');
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
	require_once(get_file_loc('SmrAccount.class.inc'));
	require_once(get_file_loc('SmrSession.class.inc'));
	
	$db = new SmrMySqlDatabase();
	
	$db->query('SELECT * FROM game_disable');
	if ($db->nextRecord()) {
		$template->assign('Message','Space Merchant Realms is currently <strong>OFF-LINE</strong>.<br />'.$db->getField('reason'));
	}
	else header('Location: /');
	
	require_once(LIB . 'Login/loginSmarty.php');
}
catch(Throwable $e) {
	handleException($e);
}
