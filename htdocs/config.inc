<?php
function logException(Throwable $e) {
	global $account, $var, $player, $db;
	$errorType = 'Unexpected Game Error!';
	$message = '';
	$currMySQLError = '';
	$delim = "\n\n-----------\n\n";

	if (is_object($account)) {
		$message .= 'Login: ' . $account->getLogin() . "\n" .
			'Account ID: ' . $account->getAccountID() . "\n" .
			'E-Mail: ' . $account->getEmail() . $delim;
	}
	$message .= 'Error Message: ' . $e . $delim;

	// Only check database if it was already loaded (do not try to autoload)
	if (class_exists('MySqlDatabase', false) && ($db instanceof MySqlDatabase)) {
		if ($currMySQLError = $db->getError()) {
			$errorType = 'Database Error';
			$message .= 'MySQL Error MSG: ' . $db->getError() . $delim;
		}
	}

	$message .=
		'$var: ' . var_export($var, true) . $delim .
		'USING_AJAX: ' . (defined('USING_AJAX') ? var_export(USING_AJAX, true) : 'undefined') . "\n" .
		'URL: ' . (defined('URL') ? var_export(URL, true) : 'undefined');

	try {
		if (function_exists('release_lock')) {
			release_lock(); //Try to release lock so they can carry on normally
		}
	} catch (Throwable $ee) {
		$message .= $delim .
					'Releasing Lock Failed' . "\n" .
					'Message: ' . $ee . "\n";
		if ($currMySQLError != $db->getError()) {
			$message .= 'MySQL Error MSG: ' . $db->getError() . "\n\n";
		}
	}

	if (defined('NPC_SCRIPT')) {
		$message = 'Script: ' . SCRIPT_ID . $delim . $message;
		var_dump($message);
		return;
	}

	// Unconditionally send error message to the log
	error_log($message);

	if (ENABLE_DEBUG) {
		// Display error message on the page and then exit
		echo nl2br($message);
		exit;
	}

	// Send error message to the in-game auto bugs mailbox
	if (is_object($player) && method_exists($player, 'sendMessageToBox')) {
		$player->sendMessageToBox(BOX_BUGS_AUTO, $message);
	} elseif (is_object($account) && method_exists($account, 'sendMessageToBox')) {
		// Will be logged without a game_id
		$account->sendMessageToBox(BOX_BUGS_AUTO, $message);
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

	return $errorType;
}

function handleException(Throwable $e) {
	// The real error message may display sensitive information, so we
	// need to catch any exceptions that are thrown while logging the error.
	try {
		$errorType = logException($e);
	} catch (Throwable $e) {
		error_log($e);
		$errorType = 'This error cannot be automatically reported. Please notify an admin!';
	}

	// If this is an ajax update, we don't really have a way to redirect
	// to an error page at this time, so we just quit.
	if (!defined('USING_AJAX') || !USING_AJAX) {
		header('location: /error.php?msg=' . urlencode($errorType));
	}
	exit;
}

/**
 * Can be used to convert any type of notice into an exception.
 */
function exception_error_handler($errno, $errstr, $errfile, $errline) {
	throw new ErrorException($errstr, $errno, E_ERROR, $errfile, $errline);
}

function setupMailer() {
	$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
	if (!empty(SMTP_HOSTNAME)) {
		$mail->isSMTP();
		$mail->Host = SMTP_HOSTNAME;
	}
	return $mail;
}

function dumpMemDiff($msg) {
	static $memory;
	@ob_end_clean();
	var_dump($msg);
	var_dump(($memory2 = memory_get_usage()) - $memory);
	$memory = $memory2;
	ob_start();
}

// Defines all constants
require_once('config.php');

// Set up vendor and class autoloaders
require_once(ROOT . 'vendor/autoload.php');
require_once(LIB . 'autoload.inc');
spl_autoload_register('get_class_loc');
