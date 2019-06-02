<?php
try {
	// includes
	require_once('config.inc');
	require_once(LIB . 'Default/smr.inc');
	
	$db = new SmrMySqlDatabase();
	
	$template = new Template();
	$db->query('SELECT * FROM game_disable');
	if ($db->nextRecord()) {
		$template->assign('Message', '<span class="red">Space Merchant Realms is temporarily offline.<br />' . $db->getField('reason') . '</span>');
	}

	// We need to destroy the session so that the login page doesn't
	// redirect to the in-game loader (bypassing the server closure).
	SmrSession::destroy();
	require_once(ENGINE . 'Default/login.inc');
}
catch (Throwable $e) {
	handleException($e);
}
