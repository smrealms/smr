<?php

$disabled = $account->isDisabled();
if ($disabled === false) {
	throw new Exception('Should not reach this page if account is not disabled');
} else {
	$msg = 'Your account is disabled: ' . $disabled['Reason'] . '<br />It is set to ';
	if ($disabled['Time'] > 0) {
		$msg .= 'reopen on ' . date(DEFAULT_DATE_FULL_LONG, $disabled['Time']);
	} else {
		$msg .= 'never reopen';
	}
	$msg .= '.<br />Please contact an admin for further information.';
}

// Destroy the session, since there is no way to "log off" from the login page
SmrSession::destroy();

$template->assign('Message', $msg);
require_once(ENGINE . 'Default/login.inc');
