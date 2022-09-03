<?php declare(strict_types=1);

use Smr\Exceptions\SocialLoginInvalidType;
use Smr\SocialLogin\SocialLogin;

try {

	if (isset($_GET['type'])) {
		$type = $_GET['type'];

		// Social logins often verify authenticity by checking login URL query
		// parameters against data stored in a PHP session by the login URL
		// generator. The SocialLogin class starts the PHP session, so to
		// ensure that the session cannot expire before validation, this script
		// immediately forwards to the social login URL after it is generated.

		require_once('../bootstrap.php');
		try {
			header('Location: ' . SocialLogin::get($type)->getLoginUrl());
		} catch (SocialLoginInvalidType) {
			create_error('Unknown social login type');
		}
	}

} catch (Throwable $e) {
	handleException($e);
}
