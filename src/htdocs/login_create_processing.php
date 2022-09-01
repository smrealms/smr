<?php declare(strict_types=1);

use Smr\Exceptions\AccountNotFound;
use Smr\Request;

try {

	require_once('../bootstrap.php');

	$session = Smr\Session::getInstance();

	if ($session->hasAccount()) {
		create_error('You\'re already logged in! Creating multis is against the rules!');
	}
	$socialLogin = Request::has('socialReg');
	if ($socialLogin) {
		session_start();
		if (!$_SESSION['socialLogin']) {
			create_error('Tried a social registration without having a social session.');
		}
	}

	//Check the captcha if it's a standard registration.
	if (!$socialLogin && !empty(RECAPTCHA_PRIVATE)) {
		$reCaptcha = new \ReCaptcha\ReCaptcha(RECAPTCHA_PRIVATE);
		// Was there a reCAPTCHA response?
		$resp = $reCaptcha->verify(
			Request::get('g-recaptcha-response', ''),
			$_SERVER['REMOTE_ADDR']
		);

		if (!$resp->isSuccess()) {
			create_error('Please make sure to complete the recaptcha!');
		}
	}

	$login = Request::get('login');
	$password = Request::get('password');
	if (str_contains($login, '\'')) {
		create_error('Illegal character in login detected! Don\'t use the apostrophe.');
	}
	if (stripos($login, 'NPC') === 0) {
		create_error('Login names cannot begin with "NPC".');
	}

	if (empty($login)) {
		create_error('Login name is missing!');
	}

	if (!$socialLogin && empty($password)) {
		create_error('Password is missing!');
	}

	$pass_verify = Request::get('pass_verify');
	if ($password != $pass_verify) {
		create_error('The passwords you entered do not match.');
	}

	// The user inputs an e-mail address in two scenarios:
	// 1. non-social account creation
	// 2. social account creation without an associated e-mail
	// In these two cases, we still need to validate the input address.
	if (!$socialLogin || empty($_SESSION['socialLogin']->getEmail())) {
		$email = Request::get('email');
		$validatedBySocial = false;
	} else {
		$email = $_SESSION['socialLogin']->getEmail();
		$validatedBySocial = true;
	}

	// Sanity check email address
	SmrAccount::checkEmail($email);

	if ($login == $password) {
		create_error('Your login and password cannot be the same!');
	}

	try {
		SmrAccount::getAccountByLogin($login);
		create_error('This login name is already registered.');
	} catch (AccountNotFound) {
		// Proceed, login is not yet registered
	}

	$referral = Request::getInt('referral_id');

	$timez = Request::getInt('timez');

	// creates a new user account object
	try {
		$account = SmrAccount::createAccount($login, $password, $email, $timez, $referral);
	} catch (AccountNotFound) {
		create_error('Invalid referral account ID!');
	}
	$account->increaseSmrRewardCredits(2 * CREDITS_PER_DOLLAR); // Give $2 worth of "reward" credits for joining.
	if ($socialLogin) {
		$account->addAuthMethod(
			$_SESSION['socialLogin']->getLoginType(),
			$_SESSION['socialLogin']->getUserID()
		);
		if ($validatedBySocial) {
			$account->setValidated(true);
			$account->update();
		}
		session_destroy();
	}

	// register session
	$session->setAccount($account);

	// save ip
	$account->updateIP();

	if (!$account->isValidated()) {
		$account->sendValidationEmail();
	}

	require('login_processing.php');
} catch (Throwable $e) {
	handleException($e);
}
