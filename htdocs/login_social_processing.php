<?php

try {

	if (isset($_GET['type'])) {
		$type = $_GET['type'];

		// Social logins often verify authenticity by checking login URL query
		// parameters against data stored in a PHP session by the login URL
		// generator. The SocialLogin class starts the PHP session, so to
		// ensure that the session cannot expire before validation, this script
		// immediately forwards to the social login URL after it is generated.

		require_once('config.inc');
		if ($type == 'facebook') {
			$url = SocialLogin::getFacebookLoginUrl();
		} elseif ($type == 'twitter') {
			$url = SocialLogin::getTwitterLoginUrl();
		} else {
			throw new Exception('Unknown social login type: ' . $type);
		}

		header('Location: ' . $url);
		exit;
	}

} catch (Throwable $e) {
	handleException($e);
}
