<?php

try {
	require_once('config.inc');

	$template = new Template();
	$template->assign('Body', 'login/login_create.php');
	$template->display('login/skeleton.php');

} catch (Throwable $e) {
	handleException($e);
}
