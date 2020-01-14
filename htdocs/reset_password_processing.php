<?php declare(strict_types=1);
try {
	require_once('config.inc');
	require_once(LIB . 'Default/smr.inc');
	
	$password = Request::get('password');
	if (empty($password)) {
		$msg = 'Password is missing!';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	$pass_verify = Request::get('pass_verify');
	if ($password != $pass_verify) {
		$msg = 'The passwords you entered do not match.';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	$login = Request::get('login');
	if ($login == $password) {
		$msg = 'Your password cannot be the same as your login!';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	// creates a new user account object
	$account = SmrAccount::getAccountByName($login);
	$passwordReset = Request::get('password_reset');
	if ($account == null || empty($passwordReset) || $account->getPasswordReset() != $passwordReset) {
		// unknown user
		header('Location: /error.php?msg=' . rawurlencode('User does not exist or reset password code is incorrect.'));
		exit;
	}
	
	$account->setPassword($password);
	
	
	header('Location: /login.php');
} catch (Throwable $e) {
	handleException($e);
}
