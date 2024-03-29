<?php declare(strict_types=1);

use Smr\Template;

try {
	require_once('../bootstrap.php');

	$template = Template::getInstance();
	$template->assign('Body', 'login/resend_password.php');
	$template->display('login/skeleton.php');

} catch (Throwable $e) {
	handleException($e);
}
