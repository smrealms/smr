<?php declare(strict_types=1);

use Smr\Account;
use Smr\Exceptions\AccountNotFound;
use Smr\Request;

try {
	require_once('../bootstrap.php');

	$email = Request::get('email', ''); // default prevents crawler bug report spam
	if ($email === '') {
		create_error('You must specify an e-mail address!');
	}

	// get this user from db
	try {
		$account = Account::getAccountByEmail($email);
	} catch (AccountNotFound) {
		// unknown user
		create_error('The specified e-mail address is not registered!');
	}

	$account->generatePasswordReset();
	$account->update();

	$resetURL = URL . '/reset_password.php?login=' . $account->getLogin() . '&resetcode=' . $account->getPasswordReset();
	$emailMessage =
		'A user from ' . getIpAddress() . ' requested to reset your password!' . EOL . EOL
		. '   Your game login is: ' . $account->getLogin() . EOL
		. '   Your password reset code is: ' . $account->getPasswordReset() . EOL . EOL
		. '   You can use this url: ' . $resetURL . EOL . EOL
		. 'The Space Merchant Realms server is on the web at ' . URL . '/';

	// send email with password to user
	$mail = setupMailer();
	$mail->Subject = 'Space Merchant Realms Password';
	$mail->setFrom('support@smrealms.de', 'SMR Support');
	$mail->msgHTML(nl2br($emailMessage));
	$mail->addAddress($account->getEmail(), $account->getLogin());
	$mail->send();

	header('Location: /reset_password.php');
	exit;

} catch (Throwable $e) {
	handleException($e);
}
