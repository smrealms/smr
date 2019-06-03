<?php
try {

	require_once('config.inc');
	require_once(LIB . 'Default/smr.inc');

	$db = new SmrMySqlDatabase();
	$db2 = new SmrMySqlDatabase();

	// ********************************
	// *
	// * C r e a t e   S e s s i o n
	// *
	// ********************************

	if (!SmrSession::hasAccount()) {
		if (isset($_REQUEST['loginType'])) {
			$socialLogin = new SocialLogin($_REQUEST['loginType']);
			if (!$socialLogin->isValid()) {
				$msg = 'Error validating login.';
				header('Location: /login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
				exit;
			}
			$account = SmrAccount::getAccountBySocialLogin($socialLogin);
			if (!is_null($account)) {
				// register session and continue to login
				SmrSession::setAccount($account);
			} else {
				// Let them create an account or link to existing
				if (session_status() === PHP_SESSION_NONE) {
					session_start();
				}
				$_SESSION['socialLogin'] = $socialLogin;
				$template = new Template();
				$template->assign('SocialLogin', $socialLogin);
				// Pre-populate the login field if an account with this email exists.
				// (Also disable creating a new account because they would just get
				// an "Email already registered" error anyway.)
				$account = SmrAccount::getAccountByEmail($socialLogin->getEmail());
				if (!is_null($account)) {
					$template->assign('MatchingLogin', $account->getLogin());
				}
				$template->display('socialRegister.inc');
				exit;
			}
		}
		else {
			$login = (isset($_REQUEST['login']) ? $_REQUEST['login'] : (isset($var['login']) ? $var['login'] : ''));
			$password = (isset($_REQUEST['password']) ? $_REQUEST['password'] : (isset($var['password']) ? $var['password'] : ''));

			// has the user submitted empty fields
			if (empty($login) || empty($password)) {
				$msg = 'Please enter login and password!';
				header('Location: /login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
				exit;
			}

			$account = SmrAccount::getAccountByName($login);
			if (is_object($account) && $account->checkPassword($password)) {
				SmrSession::setAccount($account);
			}
			else {
				$msg = 'Password is incorrect!';
				header('Location: /login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
				exit;
			}
		}
	}

	// this sn identifies our container later
	$href = SmrSession::getNewHREF(create_container('login_check_processing.php'), true);
	SmrSession::update();

	// ********************************
	// *
	// * G a m e   O p e n
	// *
	// ********************************

	// get this user from db
	$account = SmrSession::getAccount();

	// If linking a social login to an existing account
	if (isset($_REQUEST['social'])) {
		session_start();
		if (!isset($_SESSION['socialLogin'])) {
			$msg = 'Tried a social login link without having a social session.';
			header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
			exit;
		}
		$account->addAuthMethod($_SESSION['socialLogin']->getLoginType(),
		                        $_SESSION['socialLogin']->getUserID());
		session_destroy();
	}

	$db->query('SELECT * FROM game_disable');
	if ($db->nextRecord()) {
		// allow admins to access it
		if (!$account->hasPermission(PERMISSION_GAME_OPEN_CLOSE)) {
			header('Location: /offline.php');
			exit;
		}
	}


	// ********************************
	// *
	// * P e r m i s s i o n
	// *
	// ********************************

	// get reason for disabled user
	if (($disabled = $account->isDisabled()) !== false) {
		// save session (incase we forward)
		SmrSession::update();
		if (($disabled['Reason'] != CLOSE_ACCOUNT_INVALID_EMAIL_REASON) &&
		    ($disabled['Reason'] != CLOSE_ACCOUNT_BY_REQUEST_REASON)) {
			forward(create_container('disabled.php'));
		}
	}


	// *********************************
	// *
	// * a u t o   n e w b i e   t u r n
	// *
	// *********************************
	$db->query('SELECT * FROM active_session ' .
			   'WHERE last_accessed > ' . $db->escapeNumber(TIME - TIME_BEFORE_NEWBIE_TIME));
	if ($db->getNumRows() == 0) {
		$db->query('UPDATE player SET newbie_turns = 1
					WHERE newbie_turns = 0 AND
						  land_on_planet = \'FALSE\'');
	}

	// ******************************************
	// *
	// * r e m o v e   e x p i r e d   s t u f f
	// *
	// ******************************************

	$db->query('DELETE FROM player_has_ticker WHERE expires <= ' . $db->escapeNumber(TIME));
	$db->query('DELETE FROM cpl_tag WHERE expires <= ' . $db->escapeNumber(TIME) . ' AND expires > 0');

	// save ip
	$account->updateIP();

	//now we set a cookie that we can use for mult checking
	if (!isset($_COOKIE['Session_Info'])) {
		//we get their info from db if they have any
		$db->query('SELECT * FROM multi_checking_cookie WHERE account_id = ' . $account->getAccountID());
		if ($db->nextRecord()) {
			//convert to array
			$old = explode('-', $db->getField('array'));
			//get rid of old version cookie since it isn't optimal.
			if ($old[0] != MULTI_CHECKING_COOKIE_VERSION) $old = array();
		} else $old = array();
		$old[0] = MULTI_CHECKING_COOKIE_VERSION;
		if (!in_array($account->getAccountID(), $old)) $old[] = $account->getAccountID();
	}
	else {

		//we have a cookie so we see if we add to it etc
		//break cookie into array
		$cookie = explode('-', $_COOKIE['Session_Info']);
		//check for current version
		if ($cookie[0] != MULTI_CHECKING_COOKIE_VERSION) $cookie = array();
		$cookie[0] = MULTI_CHECKING_COOKIE_VERSION;
		//add this acc to the cookie if it isn't there
		if (!in_array($account->getAccountID(), $cookie)) $cookie[] = $account->getAccountID();

		$db->query('SELECT * FROM multi_checking_cookie WHERE account_id = ' . $account->getAccountID());
		if ($db->nextRecord()) {
			//convert to array
			$old = explode('-', $db->getField('array'));
			if ($old[0] != MULTI_CHECKING_COOKIE_VERSION) $old = array();
		} else $old = array();
		$old[0] = MULTI_CHECKING_COOKIE_VERSION;
		//merge arrays...but keys are all different so we go through each value
		foreach ($cookie as $value)
			if (!in_array($value, $old)) $old[] = $value;
	}
	$use = (count($old) <= 2) ? 'FALSE' : 'TRUE';
	//check that each value is legit and add it to db string
	$new = MULTI_CHECKING_COOKIE_VERSION;
	foreach ($old as $accID) {
		if (is_numeric($accID)) $new .= '-' . $accID;
	}
	$db->query('REPLACE INTO multi_checking_cookie (account_id, array, `use`) VALUES (' . $db->escapeNumber($account->getAccountID()) . ', ' . $db->escapeString($new) . ', ' . $db->escapeString($use) . ')');
	//now we update their cookie with the newest info
	setcookie('Session_Info', $new, TIME + 157680000);


	//get rid of expired messages
	$db2->query('UPDATE message SET receiver_delete = \'TRUE\', sender_delete = \'TRUE\', expire_time = 0 WHERE expire_time < ' . $db->escapeNumber(TIME) . ' AND expire_time != 0');
	// Mark message as read if it was sent to self as a mass mail.
	$db2->query('UPDATE message SET msg_read = \'TRUE\' WHERE account_id = ' . $db->escapeNumber($account->getAccountID()) . ' AND account_id = sender_id AND message_type_id IN (' . $db->escapeArray(array(MSG_ALLIANCE, MSG_GLOBAL, MSG_POLITICAL)) . ');');
	//check to see if we need to remove player_has_unread
	$db2 = new SmrMySqlDatabase();
	$db2->query('DELETE FROM player_has_unread_messages WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));
	$db2->query('
		INSERT INTO player_has_unread_messages (game_id, account_id, message_type_id)
		SELECT game_id, account_id, message_type_id FROM message WHERE account_id = ' . $db->escapeNumber($account->getAccountID()) . ' AND msg_read = ' . $db->escapeBoolean(false) . ' AND receiver_delete = ' . $db->escapeBoolean(false)
	);

	header('Location: ' . $href);
	exit;
}
catch (Throwable $e) {
	handleException($e);
}
