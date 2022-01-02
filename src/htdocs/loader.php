<?php declare(strict_types=1);
try {
	require_once('../bootstrap.php');

	header('Cache-Control: no-cache, must-revalidate');
	//A date in the past
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

	//xdebug_start_profiling();

	//ob_start();

	// ********************************
	// *
	// * Define Globals
	// *
	// ********************************

	// We want these to be already defined as globals
	$lock = false;

	// ********************************
	// *
	// * c h e c k   S e s s i o n
	// *
	// ********************************

	//echo '<pre>';echo_r($session);echo'</pre>';
	//exit;
	// do we have a session?
	$session = Smr\Session::getInstance();
	if (!$session->hasAccount()) {
		header('Location: /login.php');
		exit;
	}

	// check if we got a sn number with our url
	if (empty($session->getSN())) {
		if (!USING_AJAX) {
			create_error('Your browser lost the SN. Try to reload the page!');
		} else {
			exit;
		}
	}

	// do we have such a container object in the db?
	if ($session->hasCurrentVar() === false) {
		if (!USING_AJAX) {
			create_error('Please avoid using the back button!');
		} else {
			exit;
		}
	}
	$var = $session->getCurrentVar();

	// Determine where to load game scripts from (in case we need a special
	// game script from outside the current Smr\Session game).
	// Must not call `get_file_loc` until after we have set $overrideGameID.
	$overrideGameID = 0;
	if (isset($var['game_id']) && is_numeric($var['game_id'])) {
		$overrideGameID = $var['game_id'];
	}
	if ($overrideGameID == 0 && isset($var['GameID']) && is_numeric($var['GameID'])) {
		$overrideGameID = $var['GameID'];
	}
	if ($overrideGameID == 0) {
		$overrideGameID = $session->getGameID();
	}

	$account = $session->getAccount();
	// get reason for disabled user
	if (($disabled = Smr\Login\Redirect::redirectIfDisabled($account)) !== false) {
		// save session (incase we forward)
		$session->update();
		if ($disabled['Reason'] == CLOSE_ACCOUNT_INVALID_EMAIL_REASON) {
			if (isset($var['do_reopen_account'])) {
				// The user has attempted to re-validate their e-mail
				Page::create('invalid_email_processing.php')->go();
			} else {
				Page::create('skeleton.php', 'invalid_email.php')->go();
			}
		} elseif ($disabled['Reason'] == CLOSE_ACCOUNT_BY_REQUEST_REASON) {
			if (isset($var['do_reopen_account'])) {
				// The user has requested to reopen their account
				$account->unbanAccount($account);
			} else {
				Page::create('skeleton.php', 'reopen_account.php')->go();
			}
		} else {
			throw new Exception('Unexpected disabled reason');
		}
	}

	do_voodoo();
} catch (Throwable $e) {
	handleException($e);
}
