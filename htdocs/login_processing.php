<?


// ********************************
// *
// * I n c l u d e s
// *
// ********************************

require_once('config.inc');
require_once(LIB . 'global/smr_db.inc');
require_once(ENGINE . 'Default/smr.inc');
require_once(get_file_loc('SmrAccount.class.inc'));
require_once(get_file_loc('SmrSession.class.inc'));


$db = new SMR_DB();
$db2 = new SMR_DB();

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
		header('Location: '.$URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

	$db->query('SELECT * FROM account ' .
			   'WHERE login = '.$db->escapeString($login).' AND ' .
					 'password = '.$db->escape_string(md5($password)));
	if ($db->next_record()) {

		// register session
		SmrSession::$account_id = $db->f('account_id');

	} else {

		$msg = 'Password is incorrect!';
		header('Location: '.$URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;

	}

}

// ********************************
// *
// * G a m e   O p e n
// *
// ********************************

$db->query('SELECT * FROM game_disable');
if ($db->nf()) {

	$db2 = new SMR_DB();

	// allow admins to access it
	$db2->query('SELECT * FROM account_has_permission WHERE account_id = '.SmrSession::$account_id.' AND permission_id = 3');
	if (!$db2->nf()) {

		header('Location: '.$URL.'/offline.php');
		exit;

	}

}


// ********************************
// *
// * P e r m i s s i o n
// *
// ********************************

// get reason for disabled user
$db->query('SELECT reason
			FROM account_is_closed NATURAL JOIN closing_reason
			WHERE account_id = '.SmrSession::$account_id);
if ($db->next_record()) {

	// save session (incase we forward)
	SmrSession::update();

	if ($db->f('reason') == 'Invalid eMail') {

		header('Location: '.$URL.'/email.php');
		exit;

	} else {

		header('Location: '.$URL.'/disabled.php');
		exit;

	}

}


// *********************************
// *
// * a u t o   n e w b i e   t u r n
// *
// *********************************
$db->query('SELECT * FROM active_session ' .
		   'WHERE last_accessed > ' . (time() - 1800));
if ($db->nf() == 0)
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
// get this user from db
$account =& SmrAccount::getAccount(SmrSession::$account_id);

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
$cookieVersion = 'v2';
if (!isset($_COOKIE['Session_Info'])) {

	//we get their info from db if they have any
	$db->query('SELECT * FROM multi_checking_cookie WHERE account_id = '.$account->account_id);
	if ($db->next_record()) {
		//convert to array
		$old = explode('-', $db->f('array'));
		//get rid of old version cookie since it isn't optimal.
		if ($old[0] != $cookieVersion) $old = array();
	} else $old = array();
	$old[0] = $cookieVersion;
	if (!in_array($account->account_id, $old)) $old[] = $account->account_id;
	if (sizeof($old) <= 2) $use = 'FALSE';
	else $use = 'TRUE';
	//check that each value is legit and add it to db string
	$new = $cookieVersion;
	foreach ($old as $accID)
		if (is_numeric($accID)) $new .= '-'.$accID;
	$db->query('REPLACE INTO multi_checking_cookie (account_id, array, `use`) VALUES ('.$account->account_id.', '.$db->escapeString($new).', '.$db->escapeString($use).')');
	//now we update their cookie with the newest info
	setcookie('Session_Info', $new, time() + 157680000);

} else {

	//we have a cookie so we see if we add to it etc
	//break cookie into array
	$cookie = explode('-', $_COOKIE['Session_Info']);
	//check for current version
	if ($cookie[0] != $cookieVersion) $cookie = array();
	$cookie[0] = $cookieVersion;
	//add this acc to the cookie if it isn't there
	if (!in_array($account->account_id, $cookie)) $cookie[] = $account->account_id;

	$db->query('SELECT * FROM multi_checking_cookie WHERE account_id = '.$account->account_id);
	if ($db->next_record()) {
		//convert to array
		$old = explode('-', $db->f('array'));
		if ($old[0] != $cookieVersion) $old = array();
	} else $old = array();
	$old[0] = $cookieVersion;
	//merge arrays...but keys are all different so we go through each value
	foreach ($cookie as $value)
		if (!in_array($value,$old)) $old[] = $value;

	if (sizeof($old) <= 2) $use = 'FALSE';
	else $use = 'TRUE';
	//check that each value is legit and add it to db string
	$new = $cookieVersion;
	foreach ($old as $accID)
		if (is_numeric($accID)) $new .= '-'.$accID;
	$db->query('REPLACE INTO multi_checking_cookie (account_id, array, `use`) VALUES ('.$account->account_id.', '.$db->escapeString($new).', '.$db->escapeString($use).')');
	//update newest cookie
	setcookie('Session_Info', $new, time() + 157680000);

}

$container = array();
$container['url'] = 'validate_check.php';

// this sn identifies our container later
$sn = SmrSession::get_new_sn($container);
SmrSession::update();
//get rid of expired messages
$time = time();
$db2->query('DELETE FROM message WHERE expire_time < '.$time.' AND expire_time > 0');
//check to see if we need to remove player_has_unread
$db2 = new SMR_DB();
$db2->query('DELETE FROM player_has_unread_messages WHERE account_id = '.$account->account_id.' AND message_type_id != 3');
$db2->query('SELECT * FROM message WHERE account_id = '.$account->account_id.' AND msg_read = \'FALSE\'');

while ($db2->next_record())
	$db->query('REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES (' . $db2->f('game_id') . ', '.$account->account_id.', ' . $db2->f('message_type_id') . ')');
if (!empty($_POST['return_page'])) {
echo 'DAMN';
	header('Location: ' . $_POST['return_page']);
	exit;

}

header('Location: '.$URL.'/loader.php?sn='.$sn);
exit;

?>