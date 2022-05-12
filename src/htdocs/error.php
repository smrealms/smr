<?php declare(strict_types=1);

try {
	require_once('../bootstrap.php');

	$template = Smr\Template::getInstance();
	$template->assign('Body', 'login/error.php');
	$template->assign('ErrorMessage', Smr\Request::get('msg', 'No error message found!'));
	$template->display('login/skeleton.php');

} catch (Throwable $e) {
	handleException($e);
}
