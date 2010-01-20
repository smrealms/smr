<?php
try
{
	
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
	
	$login = (isset($_REQUEST['login']) ? $_REQUEST['login'] : (isset($var['login']) ? $var['login'] : ''));
	$password = (isset($_REQUEST['password']) ? $_REQUEST['password'] : (isset($var['password']) ? $var['password'] : ''));
	if (SmrSession::$account_id == 0) {
	
		// does the user submitted empty fields
		if (empty($login) || empty($password)) {
	
			$msg = 'Please enter login and password!';
			header('Location: '.URL.'/login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
			exit;
		}
	
		$db->query('SELECT account_id,old_account_id FROM account ' .
				   'WHERE login = '.$db->escapeString($login).' AND ' .
						 'password = '.$db->escapeString(md5($password)));
		if ($db->nextRecord())
		{
			// register session
			SmrSession::$account_id = $db->getField('account_id');
			SmrSession::$old_account_id = $db->getField('old_account_id');
			if(USE_COMPATIBILITY)
				if(SmrSession::$old_account_id==0)
				{
					$account =& SmrAccount::getAccount(SmrSession::$account_id);
					require_once(get_file_loc('Smr12MySqlDatabase.class.inc'));
					$db2 = new Smr12MySqlDatabase();
					$db2->query('SELECT account_id FROM account ' .
							   'WHERE login = '.$db->escapeString($login).' AND ' .
									 'password = '.$db->escapeString($password));
					if ($db2->nextRecord())
					{
						$account->setOldAccountID($db2->getField('account_id'));
						SmrSession::$old_account_id = $db2->getField('account_id');
					}
					$db = new SmrMySqlDatabase();
				}
		}
		else if(USE_COMPATIBILITY)
		{
			require_once(get_file_loc('Smr12MySqlDatabase.class.inc'));
			$db2 = new Smr12MySqlDatabase();
			$db2->query('SELECT * FROM account ' .
					   'WHERE login = '.$db->escapeString($login).' AND ' .
							 'password = '.$db->escapeString($password));
			if ($db2->nextRecord())
			{
				// register session
				SmrSession::$old_account_id = $db2->getField('account_id');
			
				$db3 = new Smr12MySqlDatabase();
				$db3->query('SELECT * FROM account_is_closed JOIN closing_reason USING(reason_id) ' .
					'WHERE account_id = '.SmrSession::$old_account_id.' LIMIT 1');
				if ($db3->nextRecord())
				{
					$expire_time = $db3->getField('expires');
					if(!($expire_time > 0 && $expire_time < TIME))
					{
						// save session (incase we forward)
						SmrSession::update();
						if ($db3->getField('reason') == 'Invalid eMail')
						{
							header('Location: '.URL.'/email.php');
							exit;
						}
						else
						{
							header('Location: '.URL.'/disabled.php');
							exit;
						}
					}
				}
				
				$db = new SmrMySqlDatabase();
				$db->query('SELECT account_id FROM account ' .
				   'WHERE login = '.$db->escapeString($login));
				if($db->nextRecord())
				{
					$msg = 'Your account could not be automatically upgraded as the login is already taken, please login to your 1.6 account and enter the details for your old account under preferences.';
					header('Location: '.URL.'/login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
					exit;
				}
				$db->query('SELECT account_id FROM account WHERE old_account_id='.SmrSession::$old_account_id);
				if($db->nextRecord())
				{
					SmrSession::$account_id = $db->getField('account_id');
				}
				else
				{
					$db->query('INSERT INTO account (login, password, email, first_name, last_name, address, city, postal_code, country_code, icq, validation_code, veteran, last_login, logging, offset,images,fontsize,referral_id,hof_name,validated) VALUES(' .
								$db->escape_string($db2->getField('login')) . ', ' . $db->escape_string(md5($db2->getField('password'))) . ', ' . $db->escape_string($db2->getField('email')) . ', ' .
								$db->escape_string($db2->getField('first_name')) . ', ' . $db->escape_string($db2->getField('last_name')) . ', ' .
								$db->escape_string($db2->getField('address')) . ', ' . $db->escape_string($db2->getField('city')) . ', ' . $db->escape_string($db2->getField('postal_code')) . ', ' .
								$db->escape_string($db2->getField('country_code')) . ', ' . $db->escape_string($db2->getField('icq')) . ', ' . $db->escape_string($db2->getField('validation_code')) . ',' .
								$db->escape_string($db2->getField('veteran')) . ',' . $db->escape_string($db2->getField('logging')) . ',' . TIME . ',' .$db->escapeNumber($db2->getField('offset')).',' .
								$db->escapeString($db2->getField('images')).','.$db->escapeString($db2->getField('fontsize')).','.$db->escapeNumber(0).','.$db->escapeString($db2->getField('login')).','.$db->escapeString($db2->getField('validated')).')');
					
					// creates a new user account object
					$account =& SmrAccount::getAccountByName($db2->getField('login'));
					$account->increaseSmrRewardCredits(2); // Give 2 "reward" credits for joining.
					$account->setOldAccountID(SmrSession::$old_account_id);
									
					// insert into the account stats table
					$db->query('INSERT INTO account_has_stats (account_id, HoF_name) VALUES('.$account->getAccountID().', ' . $db->escape_string($account->login) . ')');
					SmrSession::$account_id = $account->getAccountID();
				}
			}
			else
			{
				$msg = 'Password is incorrect!';
				header('Location: '.URL.'/login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
				exit;
			}
		}
		else
		{
			$msg = 'Password is incorrect!';
			header('Location: '.URL.'/login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
			exit;
		}
	}
	
	// ********************************
	// *
	// * G a m e   O p e n
	// *
	// ********************************
	
	// get this user from db
	$account =& SmrAccount::getAccount(SmrSession::$account_id);
	
	$db->query('SELECT * FROM game_disable');
	if ($db->nextRecord())
	{
		// allow admins to access it
		if (!$account->hasPermission(PERMISSION_GAME_OPEN_CLOSE))
		{
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
	if(($reason = $account->is_disabled())!==false)
	{
		// save session (incase we forward)
		SmrSession::update();
		if ($reason == 'Invalid eMail')
		{
			header('Location: '.URL.'/email.php');
			exit;
		}
		else
		{
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
			   'WHERE last_accessed > ' . (TIME - TIME_BEFORE_NEWBIE_TIME));
	if ($db->getNumRows() == 0)
		$db->query('UPDATE player SET newbie_turns = 1
					WHERE newbie_turns = 0 AND
						  land_on_planet = \'FALSE\'');
	
	// ******************************************
	// *
	// * r e m o v e   e x p i r e d   s t u f f
	// *
	// ******************************************
	
	$db->query('DELETE FROM player_has_ticker WHERE expires <= ' . TIME);
	$db->query('DELETE FROM cpl_tag WHERE expires <= ' . TIME . ' AND expires > 0');
	
	// save ip
	$account->update_ip();
	
	// try to get a real ip first
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		$curr_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else
		$curr_ip = $_SERVER['REMOTE_ADDR'];
	
	// log?
	$account->log(1, 'logged in from '.$curr_ip);
	//now we set a cookie that we can use for mult checking
	if (!isset($_COOKIE['Session_Info'])) {
	
		//we get their info from db if they have any
		$db->query('SELECT * FROM multi_checking_cookie WHERE account_id = '.$account->account_id);
		if ($db->nextRecord()) {
			//convert to array
			$old = explode('-', $db->getField('array'));
			//get rid of old version cookie since it isn't optimal.
			if ($old[0] != MULTI_CHECKING_COOKIE_VERSION) $old = array();
		} else $old = array();
		$old[0] = MULTI_CHECKING_COOKIE_VERSION;
		if (!in_array($account->account_id, $old)) $old[] = $account->account_id;
		if (sizeof($old) <= 2) $use = 'FALSE';
		else $use = 'TRUE';
		//check that each value is legit and add it to db string
		$new = MULTI_CHECKING_COOKIE_VERSION;
		foreach ($old as $accID)
			if (is_numeric($accID)) $new .= '-'.$accID;
		$db->query('REPLACE INTO multi_checking_cookie (account_id, array, `use`) VALUES ('.$account->account_id.', '.$db->escapeString($new).', '.$db->escapeString($use).')');
		//now we update their cookie with the newest info
		setcookie('Session_Info', $new, TIME + 157680000);
	
	} else {
	
		//we have a cookie so we see if we add to it etc
		//break cookie into array
		$cookie = explode('-', $_COOKIE['Session_Info']);
		//check for current version
		if ($cookie[0] != MULTI_CHECKING_COOKIE_VERSION) $cookie = array();
		$cookie[0] = MULTI_CHECKING_COOKIE_VERSION;
		//add this acc to the cookie if it isn't there
		if (!in_array($account->account_id, $cookie)) $cookie[] = $account->account_id;
	
		$db->query('SELECT * FROM multi_checking_cookie WHERE account_id = '.$account->account_id);
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
		$db->query('REPLACE INTO multi_checking_cookie (account_id, array, `use`) VALUES ('.$account->account_id.', '.$db->escapeString($new).', '.$db->escapeString($use).')');
		//update newest cookie
		setcookie('Session_Info', $new, TIME + 157680000);
	
	}
	
	$container = array();
	$container['url'] = 'validate_check.php';
	
	// this sn identifies our container later
	$href = SmrSession::get_new_href($container,true);
	SmrSession::update();
	//get rid of expired messages
	$time = TIME;
	$db2->query('UPDATE message SET reciever_delete = \'TRUE\', sender_delete = \'TRUE\' WHERE (reciever_delete = \'FALSE\' OR sender_delete = \'FALSE\') AND expire_time < '.$time.' AND expire_time > 0');
	//check to see if we need to remove player_has_unread
	$db2 = new SmrMySqlDatabase();
	$db2->query('DELETE FROM player_has_unread_messages WHERE account_id = '.$account->account_id.' AND message_type_id != 3');
	$db2->query('SELECT * FROM message WHERE account_id = '.$account->account_id.' AND msg_read = \'FALSE\' AND reciever_delete = \'FALSE\'');
	
	while ($db2->nextRecord())
		$db->query('REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES (' . $db2->getField('game_id') . ', '.$account->account_id.', ' . $db2->getField('message_type_id') . ')');
	//if (!empty($_POST['return_page'])) {
	//echo 'DAMN';
	//	header('Location: ' . $_POST['return_page']);
	//	exit;
	//
	//}
	
	header('Location: '.$href);
	exit;
}
catch(Exception $e)
{
	handleException($e);
}
?>