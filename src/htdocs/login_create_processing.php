<?php declare(strict_types=1);
try {

	require_once('../bootstrap.php');

	$session = Smr\Session::getInstance();

	if ($session->hasAccount()) {
		$msg = 'You\'re already logged in! Creating multis is against the rules!';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	$socialLogin = Smr\Request::has('socialReg');
	if ($socialLogin) {
		session_start();
		if (!$_SESSION['socialLogin']) {
			$msg = 'Tried a social registration without having a social session.';
			header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
			exit;
		}
	}

	//Check the captcha if it's a standard registration.
	if (!$socialLogin && !empty(RECAPTCHA_PRIVATE)) {
		if (!Smr\Request::has('g-recaptcha-response')) {
			$msg = 'Please make sure to complete the recaptcha!';
			header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
			exit;
		}

		$reCaptcha = new \ReCaptcha\ReCaptcha(RECAPTCHA_PRIVATE);
		// Was there a reCAPTCHA response?
		$resp = $reCaptcha->verify(
			Smr\Request::get('g-recaptcha-response'),
			$_SERVER['REMOTE_ADDR']
		);

		if (!$resp->isSuccess()) {
			$msg = 'Invalid captcha!';
			header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
			exit;
		}
	}

	$login = Smr\Request::get('login');
	$password = Smr\Request::get('password');
	if (strstr($login, '\'')) {
		$msg = 'Illegal character in login detected! Don\'t use the apostrophe.';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	if (stripos($login, 'NPC') === 0) {
		$msg = 'Login names cannot begin with "NPC".';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	if (!Smr\Request::has('agreement') || empty(Smr\Request::get('agreement'))) {
		$msg = 'You must accept the agreement!';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

	if (empty($login)) {
		$msg = 'Login name is missing!';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

	if (!$socialLogin && empty($password)) {
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
		$msg = 'Email address is missing!';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

	if (strstr($email, ' ')) {
		$msg = 'The email is invalid! It cannot contain any spaces.';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

	// get user and host for the provided address
	[$user, $host] = explode('@', $email);

	// check if the host got a MX or at least an A entry
	if (!checkdnsrr($host, 'MX') && !checkdnsrr($host, 'A')) {
		$msg = 'This is not a valid email address! The domain ' . $host . ' does not exist.';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

	if ($login == $password) {
		$msg = 'Your login and password cannot be the same!';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

	try {
		SmrAccount::getAccountByName($login);
		$msg = 'This user name is already registered.';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	} catch (Smr\Exceptions\AccountNotFound) {
		// Proceed, login is not yet registered
	}

	try {
		SmrAccount::getAccountByEmail($email);
		$msg = 'This email address is already registered.';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
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
		$msg = 'Invalid referral account ID!';
		header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
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
