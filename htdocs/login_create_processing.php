<?php
try
{

	// ********************************
	// *
	// * I n c l u d e s   h e r e
	// *
	// ********************************
	
	require_once('config.inc');
	
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
	require_once(ENGINE . 'Default/smr.inc');
	require_once(get_file_loc('SmrSession.class.inc'));
	require_once(get_file_loc('SmrAccount.class.inc'));
	
	if (SmrSession::$account_id > 0)
	{
		$msg = 'You already logged in! Creating multis is against the rules!';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	// db object
	$db = new SmrMySqlDatabase();
	$login = trim($_REQUEST['login']);
	$password = trim($_REQUEST['password']);
	if (strstr($login, '\'') || strstr($password, '\'')) {
	
		$msg = 'Illegal character in login or password detected! Don\'t use the apostrophe.';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	
	}
	if (isset($_REQUEST['agreement']) && empty($_REQUEST['agreement'])) {
	
		$msg = 'You must accept the agreement!';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	
	}
	
	if (empty($login)) {
	
		$msg = 'Login name is missing!';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	if (empty($password)) {
	
		$msg = 'Password is missing!';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	$email = trim($_REQUEST['email']);
	if (empty($email)) {
	
		$msg = 'Email address is missing!';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	if (strstr($email, ' ')) {
	
		$msg = 'The email is invalid! It cannot contain any spaces.';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	
	}
	
	$first_name = $_REQUEST['first_name'];
	if (empty($first_name)) {
	
		$msg = 'First name is missing!';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	$last_name = $_REQUEST['last_name'];
	if (empty($last_name)) {
	
		$msg = 'Last name is missing!';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	$address = $_REQUEST['address'];
	//if (empty($address)) {
	//
	//	$msg = 'Address is missing!';
	//	header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
	//	exit;
	//}
	
	$city = $_REQUEST['city'];
	//if (empty($city)) {
	//
	//	$msg = 'City is missing!';
	//	header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
	//	exit;
	//}
	
	$postal_code = $_REQUEST['postal_code'];
	//if (empty($postal_code)) {
	//
	//	$msg = 'Postal code is missing!';
	//	header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
	//	exit;
	//}
	
	$country_code = $_REQUEST['country_code'];
	//if (empty($country_code)) {
	//
	//	$msg = 'Please choose a country!';
	//	header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
	//	exit;
	//}
	
	$pass_verify = $_REQUEST['pass_verify'];
	if ($password != $pass_verify) {
	
		$msg = 'The passwords you entered do not match.';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	$email_verify = $_REQUEST['email_verify'];
	if ($email != $email_verify) {
	
		$msg = 'The eMail addresses you entered do not match.';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	if ($login == $password)
	{
	
		$msg = 'Your chosen password is invalid!';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	// get user and host for the provided address
	list($user, $host) = explode('@', $email);
	
	// check if the host got a MX or at least an A entry
	if (!checkdnsrr($host, 'MX') && !checkdnsrr($host, 'A')) {
	
		$msg = 'This is not a valid email address! The domain '.$host.' does not exist.';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	$db->query('SELECT * FROM account WHERE login = '.$db->escapeString($login));
	if ($db->getNumRows() > 0) {
	
		$msg = 'This user name is already registered.';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	$db->query('SELECT * FROM account WHERE email = '.$db->escapeString($email));
	if ($db->getNumRows() > 0) {
	
		$msg = 'This eMail address is already registered.';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	$referral = !empty($_REQUEST['referral_id']) ? $_REQUEST['referral_id'] : 0;
	
	if (!is_numeric($referral))
	{
		$msg = 'Referral ID must be a number if entered!';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}
	
	//BETA
	//$betaKey = $_REQUEST['beta_key'];
	//$db->query('SELECT used FROM beta_key WHERE code = '.$db->escapeString($betaKey).' LIMIT 1');
	//if (!$db->nextRecord() || $db->getField('used') == 'TRUE')
	//{
	//	$msg = 'Invalid or used beta key.';
	//	header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
	//	exit;
	//}
	//$db->query('UPDATE beta_key SET used = '.$db->escapeString('TRUE').' WHERE code = '.$db->escapeString($betaKey).' LIMIT 1');
	
	$icq = $_REQUEST['icq'];
	// create account
	$timez = $_REQUEST['timez'];
	
	// creates a new user account object
	try
	{
		$account =& SmrAccount::createAccount($login,$password,$email,$first_name,$last_name,$address,$city,$postal_code,$country_code,$icq,$timez,$referral);
	}
	catch(Exception $e)
	{
		$msg = 'Invalid referral id!';
		header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
	}
	$account->increaseSmrRewardCredits(2); // Give 2 "reward" credits for joining.
	
	// register session
	SmrSession::$account_id = $account->getAccountID();
	
	// save ip
	$account->update_ip();
	
	// send email with validation code to user
	mail($email, 'New Space Merchant Realms User',
				 'Your validation code is: '.$account->getValidationCode().EOL.'The Space Merchant Realms server is on the web at '.URL.'/'.EOL .
				 'Please verify within the next 7 days or your account will be automatically deleted.',
				 'From: support@smrealms.de');
	
	// remember when we sent validation code
	$db->query('INSERT INTO notification (notification_type, account_id, time) ' .
								  'VALUES(\'validation_code\', '.SmrSession::$account_id.', ' . TIME . ')');
	
	// insert into the account stats table
	$db->query('INSERT INTO account_has_stats (account_id, HoF_name) VALUES('.SmrSession::$account_id.', ' . $db->escape_string($account->login) . ')');
	
	$container = array();
	$container['login'] = $login;
	$container['password'] = $password;
	$container['url'] = 'login_processing.php';
	forwardURL($container);

}
catch(Exception $e)
{
	handleException($e);
}
?>