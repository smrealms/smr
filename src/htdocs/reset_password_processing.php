<?php declare(strict_types=1);
try {
	require_once('../bootstrap.php');

	$password = Smr\Request::get('password');
	if (empty($password)) {
		$msg = 'Password is missing!';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

	$pass_verify = Smr\Request::get('pass_verify');
	if ($password != $pass_verify) {
		$msg = 'The passwords you entered do not match.';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

	$login = Smr\Request::get('login');
	if ($login == $password) {
		$msg = 'Your password cannot be the same as your login!';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

	$passwordReset = Smr\Request::get('password_reset');
	try {
		$account = SmrAccount::getAccountByName($login);
		if (empty($passwordReset) || $account->getPasswordReset() != $passwordReset) {
			throw new Smr\Exceptions\AccountNotFound('Wrong password reset code');
		}
	} catch (Smr\Exceptions\AccountNotFound) {
		header('Location: /error.php?msg=' . rawurlencode('User does not exist or reset password code is incorrect.'));
		exit;
	}

	$account->setPassword($password);
	$account->update();

	header('Location: /login.php');
} catch (Throwable $e) {
	handleException($e);
}
