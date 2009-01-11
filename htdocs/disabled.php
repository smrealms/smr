<?php

// includes
require_once('config.inc');
require_once(ENGINE . 'Default/smr.inc');
require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
require_once(get_file_loc('SmrAccount.class.inc'));
require_once(get_file_loc('SmrSession.class.inc'));

$db = new SmrMySqlDatabase();

if (SmrSession::$account_id > 0) {

	$account =& SmrAccount::getAccount(SmrSession::$account_id);
	$db->query('SELECT * FROM account_is_closed WHERE account_id = '.SmrSession::$account_id);
	if ($db->nextRecord())
		$time = $db->getField('expires');
	
	$reason = $account->is_disabled();
	if ($time > 0) $reason .= '  Your account is set to reopen ' . date(DATE_FULL_SHORT, $time) . '.';
	else $reason .= '  Your account is set to never reopen.  If you believe this is wrong contact an admin.';

	SmrSession::destroy();

} else $reason = 'Accessing Account Information Failed.  Contact an admin if you have questions.';

$smarty->assign('Message',$reason);
$smarty->display('login.tpl');
?>