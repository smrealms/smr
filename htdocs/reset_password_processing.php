<?php
try {
	require_once('config.inc');
	require_once(LIB . 'Default/smr.inc');
	require_once(get_file_loc('SmrAccount.class.inc'));
	
	
	$password = $_REQUEST['password'];
	if (strstr($password, '\'')) {
		$msg = 'Illegal character in password detected! Don\'t use the apostrophe.';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	if (empty($password)) {
		$msg = 'Password is missing!';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	$pass_verify = $_REQUEST['pass_verify'];
	if ($password != $pass_verify) {
		$msg = 'The passwords you entered do not match.';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	
	// get this user from db
	$login = $_REQUEST['login'];
	if ($login == $password) {
		$msg = 'Your chosen password is invalid!';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	// creates a new user account object
	$account =& SmrAccount::getAccountByName($login);
	$passwordReset = $_REQUEST['password_reset'];
	if ($account==null || empty($passwordReset) || $account->getPasswordReset() != $passwordReset) {
		// unknown user
		header('Location: /error.php?msg=' . rawurlencode('User does not exist or reset password code is incorrect.'));
		exit;
	}
	
	$account->setPassword($password);
	
	
	header('Location: /login.php');
}
catch(Throwable $e) {
	handleException($e);
}
