<?php declare(strict_types=1);

use Smr\Account;
use Smr\Exceptions\AccountNotFound;
use Smr\Request;

try {
	require_once('../bootstrap.php');

	$password = Request::get('password');
	if (empty($password)) {
		create_error('Password is missing!');
	}

	$pass_verify = Request::get('pass_verify');
	if ($password !== $pass_verify) {
		create_error('The passwords you entered do not match.');
	}

	$login = Request::get('login');
	if ($login === $password) {
		create_error('Your password cannot be the same as your login!');
	}

	$passwordReset = Request::get('password_reset');
	try {
		$account = Account::getAccountByLogin($login);
		if (empty($passwordReset) || $account->getPasswordReset() !== $passwordReset) {
			throw new AccountNotFound('Wrong password reset code');
		}
	} catch (AccountNotFound) {
		create_error('User does not exist or reset password code is incorrect.');
	}

	$account->setPassword($password);
	$account->update();

	$msg = 'You have successfully reset your password!';
	header('Location: /login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
} catch (Throwable $e) {
	handleException($e);
}
