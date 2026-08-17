<?php declare(strict_types=1);

use Smr\Account;
use Smr\Exceptions\AccountNotFound;
use Smr\Pages\Login\LoginSocialCreateRenderer;
use Smr\Pages\Login\SkeletonRenderer;
use Smr\Template;

try {
	require_once('../bootstrap.php');

	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	if (!isset($_SESSION['socialId'])) {
		$msg = 'Authentication data not found!';
		header('Location: /login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	/** @var Smr\SocialLogin\SocialIdentity $socialId */
	$socialId = $_SESSION['socialId'];

	// Pre-populate the login field if an account with this email exists.
	// (Also disable creating a new account because they would just get
	// an "Email already registered" error anyway.)
	try {
		$account = Account::getAccountByEmail($socialId->email);
		$matchingLogin = $account->getLogin();
	} catch (AccountNotFound) {
		// Proceed without matching account
		$matchingLogin = null;
	}

	$body = fn() => LoginSocialCreateRenderer::render($matchingLogin);
	Template::getInstance()->display(
		fn() => SkeletonRenderer::render($body),
	);

} catch (Throwable $e) {
	handleException($e);
}
