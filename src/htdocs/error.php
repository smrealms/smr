<?php declare(strict_types=1);

use Smr\Request;

try {
	require_once('../bootstrap.php');

	$template = Smr\Template::getInstance();
	$template->assign('Body', 'login/error.php');
	$template->assign('ErrorMessage', Request::get('msg', 'No error message found!'));
	$template->display('login/skeleton.php');

} catch (Throwable $e) {
	handleException($e);
}
