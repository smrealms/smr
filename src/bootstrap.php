<?php declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use Smr\Account;
use Smr\Container\DiContainer;
use Smr\Exceptions\UserError;
use Smr\SectorLock;
use Smr\Session;

function logException(Throwable $err): void {
	$message = '';
	$delim = "\n\n-----------\n\n";

	$message .= 'Error Message: ' . $err . $delim;

	if (DiContainer::initialized(Session::class)) {
		$session = Session::getInstance();

		if ($session->hasAccount()) {
			$account = $session->getAccount();
			$message .= 'Login: ' . $account->getLogin() . "\n" .
				'E-Mail: ' . $account->getEmail() . "\n" .
				'Account ID: ' . $account->getAccountID();
			if ($session->hasGame()) {
				$message .= "\n" .
					'Game ID: ' . $session->getGameID() . "\n" .
					'Sector ID: ' . $session->getPlayer()->getSectorID();
			}
			$message .= $delim;
		}

		$message .= 'ajax: ' . var_export($session->ajax, true) . "\n";

		$var = $session->hasCurrentVar() ? $session->getCurrentVar() : null;
		$message .= '$var: ' . print_r($var, true) . $delim;
	}

	// Don't display passwords input by users in the log message!
	$sensitiveRequestFields = [
		'password',
		'pass_verify',
		'old_password',
		'new_password',
		'retype_password',
	];
	foreach ($sensitiveRequestFields as $field) {
		if (isset($_REQUEST[$field])) {
			$_REQUEST[$field] = '*****';
		}
	}
	$message .= '$_REQUEST: ' . var_export($_REQUEST, true);
	$message .= $delim;

	$message .=
		'User IP: ' . getIpAddress() . "\n" .
		'User Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'undefined') . "\n" .
		'URL: ' . (defined('URL') ? URL : 'undefined');

	// Try to release lock so they can carry on normally
	if (DiContainer::initialized(SectorLock::class)) {
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
			echo '<pre>' . htmlentities($message) . '</pre>';
		}
		// Skip remaining log methods (too disruptive during development)
		return;
	}

	// Send error message to the in-game auto bugs mailbox
	if (isset($session) && $session->hasGame()) {
		$session->getPlayer()->sendMessageToBox(BOX_BUGS_AUTO, $message);
	} elseif (isset($session) && $session->hasAccount()) {
		// Will be logged without a game_id
		$session->getAccount()->sendMessageToBox(BOX_BUGS_AUTO, $message);
	} else {
		// Will be logged without a game_id or sender_id
		Account::doMessageSendingToBox(0, BOX_BUGS_AUTO, $message, 0);
	}

	// Send error message to e-mail so that we have a permanent record
	if (count(BUG_REPORT_TO_ADDRESSES) > 0) {
		$mail = setupMailer();
		$mail->Subject = (defined('PAGE_PREFIX') ? PAGE_PREFIX : '??? ') .
		                 'Automatic Bug Report: ' . $err->getMessage();
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
		if ($e instanceof UserError) {
			handleUserError($e->getMessage());
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
	if (!ENABLE_DEBUG) {
		header('location: /error.php?msg=' . urlencode($errorType));
	}
}

/**
 * Can be used to convert any type of notice into an exception.
 */
function exception_error_handler(int $errno, string $errstr, string $errfile, int $errline): bool {
	if ((error_reporting() & $errno) === 0) {
		return false; // error is suppressed
	}
	throw new ErrorException($errstr, $errno, E_ERROR, $errfile, $errline);
}

function setupMailer(): PHPMailer {
	$mail = new PHPMailer(true);
	if (SMTP_HOSTNAME !== '') {
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
 * The requested length must be an even number >= 2.
 */
function random_string(int $length): string {
	$numBytes = (int)($length / 2);
	if ($numBytes < 1 || $length % 2 !== 0) {
		throw new Exception('Length must be an even number >= 2!');
	}
	return bin2hex(random_bytes($numBytes));
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
 *
 * @template T
 * @param array<T> $arr
 * @return T
 */
function array_rand_value(array $arr): mixed {
	if (count($arr) === 0) {
		throw new Exception('Cannot pick random value from empty array!');
	}
	return $arr[array_rand($arr)];
}

/**
 * Remove an element from an array by value.
 *
 * @template T
 * @param array<T> $arr
 * @param T $valueToRemove
 */
function array_remove_value(array &$arr, mixed $valueToRemove): void {
	foreach ($arr as $key => $value) {
		if ($value === $valueToRemove) {
			unset($arr[$key]);
		}
	}
}

/**
 * Check if two objects are strictly equal, without requiring that they are
 * same object (or any of their properties are the same object). This fills
 * the gap between == (loose equality of all object properties) and ===
 * (reference the same object).
 */
function objects_equal(object $obj1, object $obj2): bool {
	// Return early if the objects are different classes, to avoid the expense
	// of serialization.
	return $obj1::class === $obj2::class && serialize($obj1) === serialize($obj2);
}

// Defines all constants
require_once('config.php');

// Set up vendor and class autoloaders
require_once(ROOT . 'vendor/autoload.php');

// Load common functions
require_once(LIB . 'Default/smr.inc.php');

// Set up dependency injection container
DiContainer::initialize(getenv('DISABLE_PHPDI_COMPILATION') !== 'true');
