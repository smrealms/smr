<?php declare(strict_types=1);

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Exceptions\AccountNotFound;
use Smr\Login\Redirect;
use Smr\Pages\Account\LoginCheckValidatedProcessor;
use Smr\Request;
use Smr\SocialLogin\SocialLogin;

try {

	require_once('../bootstrap.php');

	// ********************************
	// *
	// * C r e a t e   S e s s i o n
	// *
	// ********************************

	$session = Smr\Session::getInstance();
	if (!$session->hasAccount()) {
		if (Request::has('loginType')) {
			$socialLogin = SocialLogin::get(Request::get('loginType'))->login();
			if (!$socialLogin->isValid()) {
				$msg = 'Error validating ' . $socialLogin->getLoginType() . ' login. Please try logging in again.';
				header('Location: /login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
				exit;
			}

			try {
				$account = Account::getAccountBySocialLogin($socialLogin);
			} catch (AccountNotFound) {
				// Let them create an account or link to existing
				if (session_status() === PHP_SESSION_NONE) {
					session_start();
				}
				$_SESSION['socialLogin'] = $socialLogin;
				header('Location: /login_social_create.php');
				exit;
			}
		} else {
			// Defaults allow redirect to login.php when this page is directly accessed
			$login = Request::get('login', '');
			$password = Request::get('password', '');

			// has the user submitted empty fields
			if (empty($login) || empty($password)) {
				$msg = 'Please enter a login and password!';
				header('Location: /login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
				exit;
			}

			try {
				// Throw an exception if account isn't found or password is wrong
				$account = Account::getAccountByLogin($login);
				if (!$account->checkPassword($password)) {
					throw new AccountNotFound('Wrong password');
				}
			} catch (AccountNotFound) {
				$msg = 'Password is incorrect!';
				header('Location: /login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
				exit;
			}
		}
		// register session and continue to login
		$session->setAccount($account);
	}

	// this sn identifies our container later
	$href = (new LoginCheckValidatedProcessor())->href(true);
	$session->update();

	// get this user from db
	$account = $session->getAccount();

	// If linking a social login to an existing account
	if (Request::has('social')) {
		session_start();
		if (!isset($_SESSION['socialLogin'])) {
			create_error('Tried a social login link without having a social session.');
		}
		$account->addAuthMethod(
			$_SESSION['socialLogin']->getLoginType(),
			$_SESSION['socialLogin']->getUserID()
		);
		session_destroy();
	}

	// ********************************
	// *
	// * P e r m i s s i o n
	// *
	// ********************************

	Redirect::redirectIfDisabled($account);
	Redirect::redirectIfOffline($account);

	// *********************************
	// *
	// * a u t o   n e w b i e   t u r n
	// *
	// *********************************
	$db = Database::getInstance();
	$dbResult = $db->read('SELECT * FROM active_session ' .
			   'WHERE last_accessed > ' . $db->escapeNumber(Epoch::time() - TIME_BEFORE_NEWBIE_TIME));
	if (!$dbResult->hasRecord()) {
		$db->write('UPDATE player SET newbie_turns = 1
					WHERE newbie_turns = 0 AND
						  land_on_planet = \'FALSE\'');
	}

	// ******************************************
	// *
	// * r e m o v e   e x p i r e d   s t u f f
	// *
	// ******************************************

	$db->write('DELETE FROM player_has_ticker WHERE expires <= ' . $db->escapeNumber(Epoch::time()));

	// save ip
	$account->updateIP();

	//now we set a cookie that we can use for mult checking
	if (!isset($_COOKIE['Session_Info'])) {
		//we get their info from db if they have any
		$dbResult = $db->read('SELECT * FROM multi_checking_cookie WHERE account_id = ' . $account->getAccountID());
		if ($dbResult->hasRecord()) {
			//convert to array
			$old = explode('-', $dbResult->record()->getString('array'));
			//get rid of old version cookie since it isn't optimal.
			if ($old[0] != MULTI_CHECKING_COOKIE_VERSION) {
				$old = [];
			}
		} else {
			$old = [];
		}
		$old[0] = MULTI_CHECKING_COOKIE_VERSION;
		if (!in_array($account->getAccountID(), $old)) {
			$old[] = $account->getAccountID();
		}
	} else {

		//we have a cookie so we see if we add to it etc
		//break cookie into array
		$cookie = explode('-', $_COOKIE['Session_Info']);
		//check for current version
		if ($cookie[0] != MULTI_CHECKING_COOKIE_VERSION) {
			$cookie = [];
		}
		$cookie[0] = MULTI_CHECKING_COOKIE_VERSION;
		//add this acc to the cookie if it isn't there
		if (!in_array($account->getAccountID(), $cookie)) {
			$cookie[] = $account->getAccountID();
		}

		$dbResult = $db->read('SELECT * FROM multi_checking_cookie WHERE account_id = ' . $account->getAccountID());
		if ($dbResult->hasRecord()) {
			//convert to array
			$old = explode('-', $dbResult->record()->getString('array'));
			if ($old[0] != MULTI_CHECKING_COOKIE_VERSION) {
				$old = [];
			}
		} else {
			$old = [];
		}
		$old[0] = MULTI_CHECKING_COOKIE_VERSION;
		//merge arrays...but keys are all different so we go through each value
		foreach ($cookie as $value) {
			if (!in_array($value, $old)) {
				$old[] = $value;
			}
		}
	}
	$use = (count($old) <= 2) ? 'FALSE' : 'TRUE';
	//check that each value is legit and add it to db string
	$new = MULTI_CHECKING_COOKIE_VERSION;
	foreach ($old as $accID) {
		if (is_numeric($accID)) {
			$new .= '-' . $accID;
		}
	}
	$db->replace('multi_checking_cookie', [
		'account_id' => $db->escapeNumber($account->getAccountID()),
		'array' => $db->escapeString($new),
		'`use`' => $db->escapeString($use),
	]);
	//now we update their cookie with the newest info
	setcookie('Session_Info', $new, Epoch::time() + 157680000);


	//get rid of expired messages
	$db->write('UPDATE message SET receiver_delete = \'TRUE\', sender_delete = \'TRUE\', expire_time = 0 WHERE expire_time < ' . $db->escapeNumber(Epoch::time()) . ' AND expire_time != 0');
	// Mark message as read if it was sent to self as a mass mail.
	$db->write('UPDATE message SET msg_read = \'TRUE\' WHERE account_id = ' . $db->escapeNumber($account->getAccountID()) . ' AND account_id = sender_id AND message_type_id IN (' . $db->escapeArray([MSG_ALLIANCE, MSG_GLOBAL, MSG_POLITICAL]) . ');');
	//check to see if we need to remove player_has_unread
	$db->write('DELETE FROM player_has_unread_messages WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));
	$db->write('
		INSERT INTO player_has_unread_messages (game_id, account_id, message_type_id)
		SELECT game_id, account_id, message_type_id FROM message WHERE account_id = ' . $db->escapeNumber($account->getAccountID()) . ' AND msg_read = ' . $db->escapeBoolean(false) . ' AND receiver_delete = ' . $db->escapeBoolean(false));

	header('Location: ' . $href);
	exit;
} catch (Throwable $e) {
	handleException($e);
}
