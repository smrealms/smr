<?php declare(strict_types=1);

use Smr\Exceptions\AccountNotFound;

try {
	require_once('../bootstrap.php');

	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	if (!isset($_SESSION['socialLogin'])) {
		$msg = 'Authentication data not found!';
		header('Location: /login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	$socialLogin = $_SESSION['socialLogin'];

	$template = Smr\Template::getInstance();
	$template->assign('SocialLogin', $socialLogin);

	// Pre-populate the login field if an account with this email exists.
	// (Also disable creating a new account because they would just get
	// an "Email already registered" error anyway.)
	try {
		$account = SmrAccount::getAccountByEmail($socialLogin->getEmail());
		$template->assign('MatchingLogin', $account->getLogin());
	} catch (AccountNotFound) {
		// Proceed without matching account
	}

	$template->assign('Body', 'login/login_social_create.php');
	$template->display('login/skeleton.php');

} catch (Throwable $e) {
	handleException($e);
}
