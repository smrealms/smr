<?php
try {

	// ********************************
	// *
	// * I n c l u d e s
	// *
	// ********************************

	require_once('config.inc');
	require_once(ENGINE . 'Default/smr.inc');
	require_once(get_file_loc('SmrMySqlDatabase.class.inc'));
	require_once(get_file_loc('SmrAccount.class.inc'));
	require_once(get_file_loc('SmrSession.class.inc'));


	$db = new SmrMySqlDatabase();
	$db2 = new SmrMySqlDatabase();

	// ********************************
	// *
	// * C r e a t e   S e s s i o n
	// *
	// ********************************

	if (SmrSession::$account_id == 0) {
		if(isset($_REQUEST['loginType'])) {
			require_once(LIB.'Login/SocialLogin.class.inc');
			$socialLogin = new SocialLogin($_REQUEST['loginType']);
			if(!$socialLogin->isValid()) {
				$msg = 'Error validating login.';
				header('Location: '.URL.'/login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
				exit;
			}
			$loginType = $socialLogin->getLoginType();
			$authKey = $socialLogin->getUserID();
			$db->query('SELECT account_id,old_account_id FROM account JOIN account_auth USING(account_id)
						WHERE login_type = '.$db->escapeString($loginType).'
						   AND auth_key = '.$db->escapeString($authKey).' LIMIT 1');
			if ($db->nextRecord()) {
				// register session
				SmrSession::$account_id = $db->getInt('account_id');
				SmrSession::$old_account_id = $db->getInt('old_account_id');
			}
			else {
				if($socialLogin->getEmail()!=null) {
					$db->query('SELECT account_id,old_account_id FROM account ' .
					   'WHERE email = '.$db->escapeString($socialLogin->getEmail()).' LIMIT 1');
				}
				if ($socialLogin->getEmail()!=null && $db->nextRecord()) { //Email already has an account so let's link.
					$account =& SmrAccount::getAccount($db->getField('account_id'));
					$account->addAuthMethod($socialLogin->getLoginType(),$socialLogin->getUserID());
					$account->setValidated(true);
					SmrSession::$account_id = $db->getField('account_id');
					SmrSession::$old_account_id = $db->getField('old_account_id');
				}
				else {
					session_start(); //Pass the data in a standard session as we don't want to initialise a normal one.
					$_SESSION['socialLogin'] =& $socialLogin;
					$template->assignByRef('SocialLogin',$socialLogin);
					$template->display('socialRegister.inc');
					exit;
				}
			}
		}
		else {
			$login = (isset($_REQUEST['login']) ? $_REQUEST['login'] : (isset($var['login']) ? $var['login'] : ''));
			$password = (isset($_REQUEST['password']) ? $_REQUEST['password'] : (isset($var['password']) ? $var['password'] : ''));

			// has the user submitted empty fields
			if (empty($login) || empty($password)) {
				$msg = 'Please enter login and password!';
				header('Location: '.URL.'/login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
				exit;
			}

			$db->query('SELECT account_id,old_account_id FROM account ' .
					   'WHERE login = '.$db->escapeString($login).' AND ' .
							 'password = '.$db->escapeString(md5($password)).' LIMIT 1');
			if ($db->nextRecord()) {
				// register session
				SmrSession::$account_id = $db->getField('account_id');
				SmrSession::$old_account_id = $db->getField('old_account_id');
			}
			else if(USE_COMPATIBILITY) {
				if(!SmrAccount::upgradeAccount($login,$password)) {
					$msg = 'Password is incorrect!';
					header('Location: '.URL.'/login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
					exit;
				}
			}
			else {
				$msg = 'Password is incorrect!';
				header('Location: '.URL.'/login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
				exit;
			}
		}
	}

	// ********************************
	// *
	// * G a m e   O p e n
	// *
	// ********************************

	// get this user from db
	$account =& SmrAccount::getAccount(SmrSession::$account_id);

	if(isset($_REQUEST['social'])) {
		require_once(LIB.'Login/SocialLogin.class.inc');
		session_start();
		$socialLogin = isset($_REQUEST['social']);
		if($socialLogin && (!$_SESSION['socialLogin'])) {
			$msg = 'Tried a social login link without having a social session.';
			header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
			exit;
		}
		$account->addAuthMethod($_SESSION['socialLogin']->getLoginType(),$_SESSION['socialLogin']->getUserID());
		session_destroy();
	}

	$db->query('SELECT * FROM game_disable');
	if ($db->nextRecord()) {
		// allow admins to access it
		if (!$account->hasPermission(PERMISSION_GAME_OPEN_CLOSE)) {
			header('Location: '.URL.'/offline.php');
			exit;
		}
	}


	// ********************************
	// *
	// * P e r m i s s i o n
	// *
	// ********************************

	// get reason for disabled user
	if(($disabled = $account->isDisabled())!==false) {
		// save session (incase we forward)
		SmrSession::update();
		if ($disabled['Reason'] == 'Invalid eMail') {
			header('Location: '.URL.'/email.php');
			exit;
		}
		else {
			header('Location: '.URL.'/disabled.php');
			exit;
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
		$db->query('SELECT * FROM multi_checking_cookie WHERE account_id = '.$account->getAccountID());
		if ($db->nextRecord()) {
			//convert to array
			$old = explode('-', $db->getField('array'));
			//get rid of old version cookie since it isn't optimal.
			if ($old[0] != MULTI_CHECKING_COOKIE_VERSION) $old = array();
		} else $old = array();
		$old[0] = MULTI_CHECKING_COOKIE_VERSION;
		if (!in_array($account->getAccountID(), $old)) $old[] = $account->getAccountID();
		if (sizeof($old) <= 2) $use = 'FALSE';
		else $use = 'TRUE';
		//check that each value is legit and add it to db string
		$new = MULTI_CHECKING_COOKIE_VERSION;
		foreach ($old as $accID)
			if (is_numeric($accID)) $new .= '-'.$accID;
		$db->query('REPLACE INTO multi_checking_cookie (account_id, array, `use`) VALUES ('.$db->escapeNumber($account->getAccountID()).', '.$db->escapeString($new).', '.$db->escapeString($use).')');
		//now we update their cookie with the newest info
		setcookie('Session_Info', $new, TIME + 157680000);

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

		$db->query('SELECT * FROM multi_checking_cookie WHERE account_id = '.$account->getAccountID());
		if ($db->nextRecord()) {
			//convert to array
			$old = explode('-', $db->getField('array'));
			if ($old[0] != MULTI_CHECKING_COOKIE_VERSION) $old = array();
		} else $old = array();
		$old[0] = MULTI_CHECKING_COOKIE_VERSION;
		//merge arrays...but keys are all different so we go through each value
		foreach ($cookie as $value)
			if (!in_array($value,$old)) $old[] = $value;

		if (sizeof($old) <= 2) $use = 'FALSE';
		else $use = 'TRUE';
		//check that each value is legit and add it to db string
		$new = MULTI_CHECKING_COOKIE_VERSION;
		foreach ($old as $accID)
			if (is_numeric($accID)) $new .= '-'.$accID;
		$db->query('REPLACE INTO multi_checking_cookie (account_id, array, `use`) VALUES ('.$db->escapeNumber($account->getAccountID()).', '.$db->escapeString($new).', '.$db->escapeString($use).')');
		//update newest cookie
		setcookie('Session_Info', $new, TIME + 157680000);

	}

	$container = array();
	$container['url'] = 'validate_check.php';

	// this sn identifies our container later
	$href = SmrSession::getNewHREF($container,true);
	SmrSession::update();
	//get rid of expired messages
	$db2->query('UPDATE message SET reciever_delete = \'TRUE\', sender_delete = \'TRUE\' WHERE expire_time < '.$db->escapeNumber(TIME).' AND expire_time > 0');
	// Mark message as read if it was sent to self as a mass mail.
	$db2->query('UPDATE message SET msg_read = \'TRUE\' WHERE account_id = sender_id AND message_type_id IN (' . $db->escapeArray(array(MSG_ALLIANCE, MSG_GLOBAL, MSG_POLITICAL)) . ');');
	//check to see if we need to remove player_has_unread
	$db2 = new SmrMySqlDatabase();
	$db2->query('DELETE FROM player_has_unread_messages WHERE account_id = '.$db->escapeNumber($account->getAccountID()));
	$db2->query('SELECT game_id, message_type_id FROM message WHERE account_id = '.$db->escapeNumber($account->getAccountID()).' AND msg_read = \'FALSE\' AND reciever_delete = \'FALSE\'');

	while ($db2->nextRecord())
		$db->query('REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES (' . $db->escapeNumber($db2->getInt('game_id')) . ', '.$db->escapeNumber($account->getAccountID()).', ' . $db->escapeNumber($db2->getInt('message_type_id')) . ')');
	//if (!empty($_POST['return_page'])) {
	//echo 'DAMN';
	//	header('Location: ' . $_POST['return_page']);
	//	exit;
	//
	//}

	header('Location: '.$href);
	exit;
}
catch(Exception $e) {
	handleException($e);
}
?>