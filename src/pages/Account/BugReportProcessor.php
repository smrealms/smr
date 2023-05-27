<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\Pages\Player\CurrentSector;
use Smr\Request;
use Smr\Session;

class BugReportProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$session = Session::getInstance();

		$steps = Request::get('steps');
		$subject = Request::get('subject');
		$error_msg = Request::get('error_msg');
		$description = Request::get('description');

		$delim = EOL . EOL . '-----------' . EOL . EOL;
		$message = 'Login: ' . $account->getLogin() . EOL .
			'Account ID: ' . $account->getAccountID() . $delim .
			'Subject: ' . $subject . $delim .
			'Description: ' . $description . $delim .
			'Steps to repeat: ' . $steps . $delim .
			'Error Message: ' . $error_msg;

		if ($session->hasGame()) {
			$player = $session->getPlayer();
			$player->sendMessageToBox(BOX_BUGS_REPORTED, $message);
		} else {
			$account->sendMessageToBox(BOX_BUGS_REPORTED, $message);
		}

		// Send report to e-mail so that we have a permanent record
		if (count(BUG_REPORT_TO_ADDRESSES) > 0) {
			$mail = setupMailer();
			$mail->Subject = PAGE_PREFIX . 'Player Bug Report';
			$mail->setFrom('bugs@smrealms.de');
			$mail->Body = $message;
			foreach (BUG_REPORT_TO_ADDRESSES as $toAddress) {
				$mail->addAddress($toAddress);
			}
			$mail->send();
		}

		$message = '<span class="admin">ADMIN</span>: Bug report submitted. Thank you for helping to improve the game!';
		if ($session->hasGame()) {
			$container = new CurrentSector(message: $message);
		} else {
			$container = new GamePlay(message: $message);
		}
		$container->go();
	}

}
