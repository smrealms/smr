<?php declare(strict_types=1);

try {
	require_once('../bootstrap.php');

	$template = new Template();
	$template->assign('Body', 'login/resend_password.php');
	$template->display('login/skeleton.php');

} catch (Throwable $e) {
	handleException($e);
}
