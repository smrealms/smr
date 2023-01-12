<?php declare(strict_types=1);

namespace Smr\Login;

use Smr\Account;
use Smr\Database;
use Smr\Session;

/**
 * Collection of functions to help with login redirection.
 */
class Redirect {

	/**
	 * @return array<string, mixed>|false
	 */
	public static function redirectIfDisabled(Account $account): array|false {
		// We skip the redirect for specific disabled reasons, because they are
		// handled elsewhere.
		$skipReasons = [
			CLOSE_ACCOUNT_INVALID_EMAIL_REASON,
			CLOSE_ACCOUNT_BY_REQUEST_REASON,
		];

		$disabled = $account->isDisabled();
		if ($disabled === false || in_array($disabled['Reason'], $skipReasons)) {
			return $disabled;
		}

		// Otherwise, we redirect to the login page with a message
		$msg = '<span class="red">Your account is disabled!</span><br />Reason: ' . $disabled['Reason'] . '<br /><br />It is set to ';
		if ($disabled['Time'] > 0) {
			$msg .= 'reopen on ' . date($account->getDateTimeFormat(), $disabled['Time']);
		} else {
			$msg .= 'never reopen';
		}
		$msg .= '.<br />Please contact an admin for further information.';

		// Destroy the Smr\Session, since there is no way to "log off" from the login page
		Session::getInstance()->destroy();

		// Store the message in a session to avoid URL length restrictions
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		$_SESSION['login_msg'] = $msg;

		header('location: /login.php?status=disabled');
		exit;
	}

	public static function redirectIfOffline(Account $account): void {
		// Check if the game is offline
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT reason FROM game_disable');
		$offline = $dbResult->hasRecord();

		// Skip redirect if we're not offline or if account has admin permission
		if ($offline === false || $account->hasPermission(PERMISSION_GAME_OPEN_CLOSE)) {
			return;
		}

		// We need to destroy the session so that the login page doesn't
		// redirect to the in-game loader (bypassing the server closure).
		Session::getInstance()->destroy();

		// Store the message in a session to avoid URL length restrictions
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		$_SESSION['login_msg'] = '<span class="red">Space Merchant Realms is temporarily offline.<br />' . $dbResult->record()->getString('reason') . '</span>';

		header('location: /login.php?status=offline');
		exit;
	}

}
