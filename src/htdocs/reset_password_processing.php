<?php declare(strict_types=1);
try {
	require_once('../bootstrap.php');

	$password = Smr\Request::get('password');
	if (empty($password)) {
		create_error('Password is missing!');
	}

	$pass_verify = Smr\Request::get('pass_verify');
	if ($password != $pass_verify) {
		create_error('The passwords you entered do not match.');
	}

	$login = Smr\Request::get('login');
	if ($login == $password) {
		create_error('Your password cannot be the same as your login!');
	}

	$passwordReset = Smr\Request::get('password_reset');
	try {
		$account = SmrAccount::getAccountByName($login);
		if (empty($passwordReset) || $account->getPasswordReset() != $passwordReset) {
			throw new Smr\Exceptions\AccountNotFound('Wrong password reset code');
		}
	} catch (Smr\Exceptions\AccountNotFound) {
		create_error('User does not exist or reset password code is incorrect.');
	}

	$account->setPassword($password);
	$account->update();

	header('Location: /login.php');
} catch (Throwable $e) {
	handleException($e);
}
