<?php declare(strict_types=1);
try {

	if (empty(Request::get('email'))) {
		header('Location: /error.php?msg=' . rawurlencode('You must specify an e-mail address!'));
		exit;
	}

	require_once('config.inc');
	require_once(LIB . 'Default/smr.inc');

	// get this user from db
	$account = SmrAccount::getAccountByEmail(Request::get('email'));
	if ($account == null) {
		// unknown user
		header('Location: /error.php?msg=' . rawurlencode('The specified e-mail address is not registered!'));
		exit;
	}

	$account->generatePasswordReset();

	$resetURL = URL . '/reset_password.php?login=' . $account->getLogin() . '&resetcode=' . $account->getPasswordReset();
	$emailMessage =
		 'A user from ' . getIpAddress() . ' requested to reset your password!' . EOL . EOL .
		 '   Your game login is: ' . $account->getLogin() . EOL .
		 '   Your password reset code is: ' . $account->getPasswordReset() . EOL . EOL .
		 '   You can use this url: ' . $resetURL . EOL . EOL .
		 'The Space Merchant Realms server is on the web at ' . URL . '/';

	// send email with password to user
	$mail = setupMailer();
	$mail->Subject = 'Space Merchant Realms Password';
	$mail->setFrom('support@smrealms.de', 'SMR Support');
	$mail->msgHTML(nl2br($emailMessage));
	$mail->addAddress($account->getEmail(), $account->getHofName());
	$mail->send();

	header('Location: /reset_password.php');
	exit;

} catch (Throwable $e) {
	handleException($e);
}
