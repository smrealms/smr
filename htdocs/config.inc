<?php
function logException(Throwable $e) {
	global $account,$var,$player;
	$errorType = 'Unexpected Game Error!';
	$message='';
	$currMySQLError='';

	if(is_object($account)) {
		$message .= 'Login: '.$account->getLogin().EOL.EOL.'-----------'.EOL.EOL.
			'Account ID: '.$account->getAccountID().EOL.EOL.'-----------'.EOL.EOL.
			'E-Mail: '.$account->getEmail().EOL.EOL.'-----------'.EOL.EOL;
	}
	$message .= 'Error Message: '. $e .EOL.EOL.'-----------'.EOL.EOL;

	// Only check database if it was already loaded (do not try to autoload)
	if (class_exists('SmrMySqlDatabase', false)) {
		$db = new SmrMySqlDatabase();
		if($currMySQLError = $db->getError()) {
			$errorType = 'Database Error';
			$message .= 'MySQL Error MSG: '.$db->getError().EOL.EOL.'-----------'.EOL.EOL;
		}
	}

	$message .=
		'$var: '.var_export($var,true).EOL.EOL.'-----------'.EOL.EOL.
		'USING_AJAX: '.(defined('USING_AJAX')?var_export(USING_AJAX,true):'undefined');

	try {
		if(function_exists('release_lock'))
			release_lock(); //Try to release lock so they can carry on normally
	}
	catch(Throwable $ee) {
		$message .= EOL.EOL.'-----------'.EOL.EOL.
					'Releasing Lock Failed' .EOL.
					'Message: ' . $ee .EOL.EOL;
		if($currMySQLError!=$db->getError()) {
			$message .= 'MySQL Error MSG: '.$db->getError().EOL.EOL;
		}
	}

	if (defined('NPC_SCRIPT')) {
		$message = 'Script: '.SCRIPT_ID.EOL.EOL.'-----------'.EOL.EOL.$message;
		var_dump($message);
		return;
	}

	// Unconditionally send error message to the log
	error_log($message);

	if(ENABLE_DEBUG) {
		// Display error message on the page and then exit
		echo nl2br($message);
		exit;
	}

	// Send error message to the in-game auto bugs mailbox
	if (is_object($player) && method_exists($player,'sendMessageToBox')) {
		$player->sendMessageToBox(BOX_BUGS_AUTO, $message);
	} elseif (is_object($account) && method_exists($account,'sendMessageToBox')) {
		// Will be logged without a game_id
		$account->sendMessageToBox(BOX_BUGS_AUTO, $message);
	} else {
		// Will be logged without a game_id or sender_id
		SmrAccount::doMessageSendingToBox(0, BOX_BUGS_AUTO, $message, 0);
	}

	// Send error message to e-mail so that we have a permanent record
	if (!empty(BUG_REPORT_TO_ADDRESSES)) {
		$mail = setupMailer();
		$mail->Subject = 'Automatic Bug Report';
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
		$errorType = 'This error cannot be automatically reported. Please notify an admin!';
	}

	// If this is an ajax update, we don't really have a way to redirect
	// to an error page at this time, so we just quit.
	if(!defined('USING_AJAX')||!USING_AJAX)
		header('location: /error.php?msg='.urlencode($errorType));
	exit;
}

function dumpMemDiff($msg) {
	static $memory;
	@ob_end_clean();
	var_dump($msg);
	var_dump(($memory2 = memory_get_usage()) - $memory);
	$memory = $memory2;
	ob_start();
}

function microtimeDiff($m1,$m2) {
	$t1 = explode(' ', $m1);
	$t2 = explode(' ', $m2);
	return $t1[1] + $t1[0] - ($t2[1] + $t2[0]);
}

function microtimeSec($microtime) {
	if (strlen($microtime) == 21) {
		return substr($microtime,11);
	} else {
		throw new Exception('Not a microtime! ('.$microtime.')');
	}
}

function microtimeMSec($microtime) {
	if (strlen($microtime) == 21) {
		// Do not cast to int, because the substring can begin with '0'
		return substr($microtime,2,6);
	} else {
		throw new Exception('Not a microtime! ('.$microtime.')');
	}
}

// Defines all constants
require_once('config.php');

// Set up vendor and class autoloaders
require_once(ROOT . 'vendor/autoload.php');
require_once(LIB . 'autoload.inc');
spl_autoload_register('get_class_loc');

// Initialize the template
$template = new Template();
$GLOBALS['template'] =& $template;

$template->assign('URL', URL);
$template->assign('CSSLink', DEFAULT_CSS);
$template->assign('CSSColourLink', DEFAULT_CSS_COLOUR);
$template->assign('AJAX_ENABLE_REFRESH', AJAX_DEFAULT_REFRESH_TIME);

// Change the browser title based on the server config
$prefix = '';
if (ENABLE_DEBUG) {
	$prefix = 'DEV: ';
} elseif (ENABLE_BETA) {
	$prefix = 'BETA: ';
}
$template->assign('Title', $prefix . 'Space Merchant Realms');
