<?php declare(strict_types=1);

use Smr\Container\DiContainer;
use Smr\SectorLock;

function logException(Throwable $e): void {
	$message = '';
	$delim = "\n\n-----------\n\n";

	$session = Smr\Session::getInstance();

	if ($session->hasAccount()) {
		$account = $session->getAccount();
		$message .= 'Login: ' . $account->getLogin() . "\n" .
			'E-Mail: ' . $account->getEmail() . "\n" .
			'Account ID: ' . $account->getAccountID() . "\n" .
			'Game ID: ' . $session->getGameID() . $delim;
	}
	$message .= 'Error Message: ' . $e . $delim;

	$var = $session->hasCurrentVar() ? $session->getCurrentVar() : null;
	$message .= '$var: ' . print_r($var, true);

	// Don't display passwords input by users in the log message!
	if (isset($_REQUEST['password'])) {
		$_REQUEST['password'] = '*****';
	}
	$message .= "\n\n" . '$_REQUEST: ' . var_export($_REQUEST, true);
	$message .= $delim;

	$message .=
		'User IP: ' . getIpAddress() . "\n" .
		'User Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'undefined') . "\n" .
		'USING_AJAX: ' . (defined('USING_AJAX') ? var_export(USING_AJAX, true) : 'undefined') . "\n" .
		'URL: ' . (defined('URL') ? URL : 'undefined');

	// Try to release lock so they can carry on normally
	if (class_exists(SectorLock::class, false)) {
		try {
			SectorLock::getInstance()->release();
		} catch (Throwable $ee) {
			$message .= $delim .
					'Releasing Lock Failed' . "\n" .
					'Message: ' . $ee . "\n";
		}
	}

	if (defined('SCRIPT_ID')) {
		$message = 'Script: ' . SCRIPT_ID . $delim . $message . "\n\n";
	}

	// Unconditionally send error message to the log
	error_log($message);

	if (ENABLE_DEBUG) {
		// Display error message on the page (redundant with error_log for CLI)
		if (PHP_SAPI !== 'cli') {
			echo '<pre>' . $message . '</pre>';
		}
		// Skip remaining log methods (too disruptive during development)
		return;
	}

	// Send error message to the in-game auto bugs mailbox
	if ($session->hasGame()) {
		$session->getPlayer()->sendMessageToBox(BOX_BUGS_AUTO, $message);
	} elseif ($session->hasAccount()) {
		// Will be logged without a game_id
		$session->getAccount()->sendMessageToBox(BOX_BUGS_AUTO, $message);
	} else {
		// Will be logged without a game_id or sender_id
		SmrAccount::doMessageSendingToBox(0, BOX_BUGS_AUTO, $message, 0);
	}

	// Send error message to e-mail so that we have a permanent record
	if (!empty(BUG_REPORT_TO_ADDRESSES)) {
		$mail = setupMailer();
		$mail->Subject = (defined('PAGE_PREFIX') ? PAGE_PREFIX : '??? ') .
		                 'Automatic Bug Report';
		$mail->setFrom('bugs@smrealms.de');
		$mail->Body = $message;
		foreach (BUG_REPORT_TO_ADDRESSES as $toAddress) {
			$mail->addAddress($toAddress);
		}
		$mail->send();
	}
}

/**
 * Handles all user-facing exceptions.
 *
 * If the error is fatal, the exception is logged and the player is redirected
 * to an appropriate error page.
 *
 * If the error is just informational (e.g. the user input an invalid value),
 * then the message is displayed on the page without being logged.
 */
function handleException(Throwable $e): void {
	// The real error message may display sensitive information, so we
	// need to catch any exceptions that are thrown while logging the error.
	try {
		if ($e instanceof Smr\Exceptions\UserError) {
			create_error($e->getMessage());
		}
		logException($e);
		$errorType = 'Unexpected Error!';
	} catch (Throwable $e2) {
		error_log('Original exception: ' . $e);
		error_log('Exception during logException: ' . $e2);
		$errorType = 'This error cannot be automatically reported. Please notify an admin!';
	}

	// If this is an ajax update, we don't really have a way to redirect
	// to an error page at this time.
	if (!ENABLE_DEBUG && (!defined('USING_AJAX') || !USING_AJAX)) {
		header('location: /error.php?msg=' . urlencode($errorType));
	}
}

/**
 * Can be used to convert any type of notice into an exception.
 */
function exception_error_handler(int $errno, string $errstr, string $errfile, int $errline): bool {
	if (!(error_reporting() & $errno)) {
		return false; // error is suppressed
	}
	throw new ErrorException($errstr, $errno, E_ERROR, $errfile, $errline);
}

function setupMailer(): \PHPMailer\PHPMailer\PHPMailer {
	$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
	if (!empty(SMTP_HOSTNAME)) {
		$mail->isSMTP();
		$mail->Host = SMTP_HOSTNAME;
	}
	return $mail;
}

function getIpAddress(): string {
	foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key) {
		if (array_key_exists($key, $_SERVER) === true) {
			foreach (explode(',', $_SERVER[$key]) as $ip) {
				if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
					return $ip;
				}
			}
		}
	}
	return 'unknown';
}

/**
 * Wrapper around the floor() builtin for returning an integer type.
 */
function IFloor(float $val): int {
	return (int)floor($val);
}

/**
 * Wrapper around the ceil() builtin for returning an integer type.
 */
function ICeil(float $val): int {
	return (int)ceil($val);
}

/**
 * Wrapper around the round() builtin for returning an integer type.
 */
function IRound(float $val): int {
	return (int)round($val);
}

/**
 * Convert a numeric string to an int with input validation.
 */
function str2int(string $val): int {
	$result = filter_var($val, FILTER_VALIDATE_INT);
	if ($result === false) {
		throw new Exception('Input value is not an integer: ' . $val);
	}
	return $result;
}

/**
 * Generate a cryptographically strong random hexadecimal string.
 * The requested length must be a multiple of 2.
 */
function random_string(int $length): string {
	if ($length % 2 != 0) {
		throw new Exception('Length must be a multiple of 2!');
	}
	return bin2hex(random_bytes($length / 2));
}

/**
 * Generate a (non-cryptographic) random alphabetic string.
 * This is slower for longer strings.
 */
function random_alphabetic_string(int $length): string {
	$result = '';
	for ($i = 0; $i < $length; ++$i) {
		$result .= chr(rand(ord('a'), ord('z')));
	}
	return $result;
}

/**
 * Return the value of a random key from an array.
 */
function array_rand_value(array $arr): mixed {
	if (empty($arr)) {
		throw new Exception('Cannot pick random value from empty array!');
	}
	return $arr[array_rand($arr)];
}

// Defines all constants
require_once('config.php');

// Set up vendor and class autoloaders
require_once(ROOT . 'vendor/autoload.php');
require_once(LIB . 'autoload.inc.php');
spl_autoload_register(get_class_loc(...));

// Load common functions
require_once(LIB . 'Default/smr.inc.php');

// Set up dependency injection container
DiContainer::initialize(getenv('DISABLE_PHPDI_COMPILATION') !== 'true');
