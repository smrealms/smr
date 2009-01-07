<?

// ********************************
// *
// * I n c l u d e s   h e r e
// *
// ********************************

require_once('config.inc');
require_once(ENGINE . 'Default/smr.inc');
require_once(get_file_loc('SmrAccount.class.inc'));


// get this user from db
$login = $_REQUEST['login'];
// creates a new user account object
$account =& SmrAccount::getAccountByName($login);
$email = $_REQUEST['email'];
if ($account==null || $account->email != $email) {

	// unknown user
	header('Location: '.$URL.'/error.php?msg=' . rawurlencode('User does not exist'));
	exit;

}
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	$curr_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
else
	$curr_ip = $_SERVER['REMOTE_ADDR'];

$account->generatePasswordReset();

$resetURL = $URL.'/reset_password.php?login='.$account->login.'&resetcode='.$account->getPasswordReset();
// send email with password to user
mail($email, 'Space Merchant Realms Password',
	 'A user from ' . $curr_ip . ' requested to reset your password!'."\n\r\n\r" .
	 '   Your password reset code is: ' . $account->getPasswordReset()."\n\r" .
	 '   You can use this url: '.$resetURL . "\n\r\n\r" .
	 'The Space Merchant Realms server is on the web at '.$URL.'/',
	 'From: support@smrealms.de');

header('Location: '.$URL.'/reset_password.php');
exit;

?>