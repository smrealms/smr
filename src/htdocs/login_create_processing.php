<?php declare(strict_types=1);
try {

	require_once('../bootstrap.php');

	$session = Smr\Session::getInstance();

	if ($session->hasAccount()) {
		create_error('You\'re already logged in! Creating multis is against the rules!');
	}
	$socialLogin = Smr\Request::has('socialReg');
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
			Smr\Request::get('g-recaptcha-response', ''),
			$_SERVER['REMOTE_ADDR']
		);

		if (!$resp->isSuccess()) {
			create_error('Please make sure to complete the recaptcha!');
		}
	}

	$login = Smr\Request::get('login');
	$password = Smr\Request::get('password');
	if (strstr($login, '\'')) {
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

	$pass_verify = Smr\Request::get('pass_verify');
	if ($password != $pass_verify) {
		create_error('The passwords you entered do not match.');
	}

	// The user inputs an e-mail address in two scenarios:
	// 1. non-social account creation
	// 2. social account creation without an associated e-mail
	// In these two cases, we still need to validate the input address.
	if (!$socialLogin || empty($_SESSION['socialLogin']->getEmail())) {
		$email = Smr\Request::get('email');
		$validatedBySocial = false;
	} else {
		$email = $_SESSION['socialLogin']->getEmail();
		$validatedBySocial = true;
	}

	if (empty($email)) {
		create_error('Email address is missing!');
	}

	if (strstr($email, ' ')) {
		create_error('The email is invalid! It cannot contain any spaces.');
	}

	// get user and host for the provided address
	[$user, $host] = explode('@', $email);

	// check if the host got a MX or at least an A entry
	if (!checkdnsrr($host, 'MX') && !checkdnsrr($host, 'A')) {
		create_error('This is not a valid email address! The domain ' . $host . ' does not exist.');
	}

	if ($login == $password) {
		create_error('Your login and password cannot be the same!');
	}

	try {
		SmrAccount::getAccountByName($login);
		create_error('This user name is already registered.');
	} catch (Smr\Exceptions\AccountNotFound) {
		// Proceed, login is not yet registered
	}

	try {
		SmrAccount::getAccountByEmail($email);
		create_error('This email address is already registered.');
	} catch (Smr\Exceptions\AccountNotFound) {
		// Proceed, email is not yet registered
	}

	$referral = Smr\Request::getInt('referral_id');

	// create account
	$timez = Smr\Request::getInt('timez');

	// creates a new user account object
	try {
		$account = SmrAccount::createAccount($login, $password, $email, $timez, $referral);
	} catch (Smr\Exceptions\AccountNotFound) {
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
