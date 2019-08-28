<?php

try {
	require_once('config.inc');

	$template = new Template();
	$template->assign('Body', 'login/reset_password.php');
	$template->display('login/skeleton.php');

} catch (Throwable $e) {
	handleException($e);
}
