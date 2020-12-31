<?php declare(strict_types=1);

try {

	if (isset($_GET['type'])) {
		$type = $_GET['type'];

		// Social logins often verify authenticity by checking login URL query
		// parameters against data stored in a PHP session by the login URL
		// generator. The SocialLogin class starts the PHP session, so to
		// ensure that the session cannot expire before validation, this script
		// immediately forwards to the social login URL after it is generated.

		require_once('config.inc');
		try {
			header('Location: ' . SocialLogin::get($type)->getLoginUrl());
		} catch (SocialLoginNotFound $e) {
			header('location: /error.php?msg=' . urlencode('Unknown social login type'));
		}
	}

} catch (Throwable $e) {
	handleException($e);
}
