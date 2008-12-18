<?

// ********************************
// *
// * I n c l u d e s   h e r e
// *
// ********************************

include('config.inc');
require_once(ENGINE . 'Old_School/smr.inc');
require_once(get_file_loc('SmrAccount.class.inc'));


// get this user from db
$login = $_REQUEST['login'];
// creates a new user account object
$account =& SmrAccount::getAccountByName($login);
$email = $_REQUEST['email'];
if ($account->email != $email) {

	// unknown user
	header('Location: '.$URL.'/error.php?msg=' . rawurlencode('User does not exist'));
	exit;

}
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	$curr_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
else
	$curr_ip = $_SERVER['REMOTE_ADDR'];

// send email with password to user
mail($email, 'Space Merchant Realms Password',
	 'A user from ' . $curr_ip . ' requested your password!\n\r\n\r' .
	 '   Your password is: ' . $account->password . '\n\r\n\r' .
	 'The Space Merchant Realms server is on the web at '.$URL.'/',
	 'From: support@smrealms.de');

header('Location: '.$URL.'/login.php');
exit;

?>