<?php declare(strict_types=1);

use Smr\Login\Redirect;
use Smr\Pages\Account\InvalidEmail;
use Smr\Pages\Account\InvalidEmailProcessor;
use Smr\Pages\Account\ReopenAccount;
use Smr\Pages\Account\ReopenAccountProcessor;
use Smr\Session;

try {
	require_once('../bootstrap.php');

	header('Cache-Control: no-cache, must-revalidate');
	//A date in the past
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

	//xdebug_start_profiling();

	//ob_start();

	// ********************************
	// *
	// * c h e c k   S e s s i o n
	// *
	// ********************************

	// do we have a session?
	$session = Session::getInstance();
	if (!$session->hasAccount()) {
		header('Location: /login.php');
		exit;
	}

	// check if we got a sn number with our url
	if ($session->getSN() === '') {
		create_error('Your browser lost the SN. Try to reload the page!');
	}

	// do we have such a container object in the db?
	if ($session->hasCurrentVar() === false) {
		if ($session->ajax) {
			exit;
		} else {
			create_error('This page is not available after using the back button!');
		}
	}
	$var = $session->getCurrentVar();

	// If SN changes during an ajax update, it is either a) an internal error,
	// b) the user hit the back button, or c) the user is requesting a page that
	// is allowed to be executed in an ajax call. Only c) should continue.
	if ($session->ajax && $session->hasChangedSN() && !$var->allowAjax) {
		exit;
	}

	$account = $session->getAccount();
	// get reason for disabled user
	$disabled = Redirect::redirectIfDisabled($account);
	if ($disabled !== false) {
		// save session (incase we forward)
		$session->update();
		if ($disabled['Reason'] === CLOSE_ACCOUNT_INVALID_EMAIL_REASON) {
			if (!($var instanceof InvalidEmailProcessor)) {
				(new InvalidEmail())->go();
			}
			// The user has attempted to re-validate their e-mail
			// so let this page process normally.
		} elseif ($disabled['Reason'] === CLOSE_ACCOUNT_BY_REQUEST_REASON) {
			if (!($var instanceof ReopenAccountProcessor)) {
				(new ReopenAccount())->go();
			}
			// The user has requested to reopen their account
			// so let this page process normally.
		} else {
			throw new Exception('Unexpected disabled reason');
		}
	}

	do_voodoo();
} catch (Throwable $e) {
	handleException($e);
}
