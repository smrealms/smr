<?php
try {
	// includes
	require_once('config.inc');
	require_once(LIB . 'Default/smr.inc');
	require_once(get_file_loc('SmrAccount.class.inc'));
	require_once(get_file_loc('SmrSession.class.inc'));

	$db = new SmrMySqlDatabase();

	// do we have a session?
	if (SmrSession::$account_id == 0) {
		header('Location: '.URL.'/login.php');
		exit;
	}

	// get account
	$account =& SmrAccount::getAccount(SmrSession::$account_id);

	if ($_POST['email'] != $_POST['email_verify']) {
		$msg = 'The eMail addresses you entered do not match!';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

	if ($_POST['email'] == $account->getEmail()) {
		$msg = 'You have to use a different email than the registered one!';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

	// get user and host for the provided address
	list($user, $host) = explode('@', $_POST['email']);

	// check if the host got a MX or at least an A entry
	if (!checkdnsrr($host, 'MX') && !checkdnsrr($host, 'A')) {
		$msg = 'This is not a valid email address!';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

	$db->query('SELECT * FROM account WHERE email = ' . $db->escape_string($_POST['email']));
	if ($db->getNumRows() > 0) {
		$msg = 'This eMail address is already registered.';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

	$account->setEmail($_POST['email']);
	$account->setValidationCode(substr(SmrSession::$session_id, 0, 10));
	$account->setValidated(false);
	$account->update();

	// remember when we sent validation code
	$db->query('REPLACE INTO notification (notification_type, account_id, time)
				VALUES(\'validation_code\', '.$db->escapeNumber($account->getAccountID()).', ' . $db->escapeNumber(TIME) . ')');

	$emailMessage =
		'You changed your email address registered within SMR and need to revalidate now!'.EOL.EOL.
		'   Your new validation code is: '.$account->getValidationCode().EOL.EOL.
		'The Space Merchant Realms server is on the web at '.URL.'/.'.EOL.
		'Please verify within the next 7 days or your account will be automatically deleted.';

	$mail = setupMailer();
	$mail->Subject = 'Your validation code!';
	$mail->setFrom('support@smrealms.de', 'SMR Support');
	$mail->msgHTML(nl2br($emailMessage));
	$mail->addAddress($account->getEmail(), $account->getHofName());
	$mail->send();

	// get rid of that email permission
	$db->query('DELETE FROM account_is_closed
				WHERE account_id = '.$db->escapeNumber($account->getAccountID()).' AND reason_id = 1');

	$container = array();
	$container['login'] = $login;
	$container['password'] = $password;
	$container['url'] = 'login_processing.php';
	forwardURL($container);
	exit;
}
catch(Exception $e) {
	handleException($e);
}
?>
