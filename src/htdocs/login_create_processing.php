<?php declare(strict_types=1);

use ReCaptcha\ReCaptcha;
use Smr\Account;
use Smr\Exceptions\AccountNotFound;
use Smr\Request;
use Smr\Session;

try {

	require_once('../bootstrap.php');

	$session = Session::getInstance();

	if ($session->hasAccount()) {
		create_error('You\'re already logged in! Creating multis is against the rules!');
	}

	$socialId = null;
	if (Request::has('socialReg')) {
		session_start();
		if (!isset($_SESSION['socialId'])) {
			create_error('Your session has expired. Please try again.');
		}
		/** @var Smr\SocialLogin\SocialIdentity $socialId */
		$socialId = $_SESSION['socialId'];
	}

	//Check the captcha if it's a standard registration.
	if ($socialId === null && RECAPTCHA_PRIVATE !== '') {
		$reCaptcha = new ReCaptcha(RECAPTCHA_PRIVATE);
		// Was there a reCAPTCHA response?
		$resp = $reCaptcha->verify(
			Request::get('g-recaptcha-response', ''),
			$_SERVER['REMOTE_ADDR'],
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

	if ($login === '') {
		create_error('Login name is missing!');
	}

	if ($socialId === null && $password === '') {
		create_error('Password is missing!');
	}

	$pass_verify = Request::get('pass_verify');
	if ($password !== $pass_verify) {
		create_error('The passwords you entered do not match.');
	}

	$email = $socialId?->email ?: Request::get('email');

	// Sanity check email address
	Account::checkEmail($email);

	if ($login === $password) {
		create_error('Your login and password cannot be the same!');
	}

	try {
		Account::getAccountByLogin($login);
		create_error('This login name is already registered.');
	} catch (AccountNotFound) {
		// Proceed, login is not yet registered
	}

	$referral = Request::getInt('referral_id');

	$timez = Request::getInt('timez');

	// creates a new user account object
	try {
		$account = Account::createAccount($login, $password, $email, $timez, $referral);
	} catch (AccountNotFound) {
		create_error('Invalid referral account ID!');
	}
	$account->increaseSmrRewardCredits(2 * CREDITS_PER_DOLLAR); // Give $2 worth of "reward" credits for joining.
	if ($socialId !== null) {
		$account->addAuthMethod($socialId);
		$account->setValidated(true);
		$account->update();
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
