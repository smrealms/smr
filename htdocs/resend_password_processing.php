<?php
try {

	// ********************************
	// *
	// * I n c l u d e s   h e r e
	// *
	// ********************************

	require_once('config.inc');
	require_once(LIB . 'Default/smr.inc');
	require_once(get_file_loc('SmrAccount.class.inc'));


	// get this user from db
	$email = $_REQUEST['email'];
	$account = SmrAccount::getAccountByEmail($email);
	if ($account==null) {
		// unknown user
		header('Location: '.URL.'/error.php?msg=' . rawurlencode('The specified e-mail address is not registered!'));
		exit;
	}

	$account->generatePasswordReset();

	$resetURL = URL.'/reset_password.php?login='.$account->getLogin().'&resetcode='.$account->getPasswordReset();
	$emailMessage =
		 'A user from ' . getIpAddress() . ' requested to reset your password!'.EOL.EOL.
		 '   Your game login is: ' . $account->getLogin().EOL.
		 '   Your password reset code is: ' . $account->getPasswordReset().EOL.EOL.
		 '   You can use this url: '.$resetURL .EOL.EOL.
		 'The Space Merchant Realms server is on the web at '.URL.'/';

	// send email with password to user
	$mail = new \PHPMailer\PHPMailer\PHPMailer();
	$mail->Subject = 'Space Merchant Realms Password';
	$mail->setFrom('support@smrealms.de', 'SMR Support');
	$mail->msgHTML(nl2br($emailMessage));
	$mail->addAddress($account->getEmail(), $account->getHofName());
	$mail->send();

	header('Location: '.URL.'/reset_password.php');
	exit;

}
catch(Exception $e) {
	handleException($e);
}
?>
