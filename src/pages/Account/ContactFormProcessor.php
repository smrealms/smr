<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class ContactFormProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$receiver = Request::get('receiver');
		$subject = Request::get('subject');
		$msg = Request::get('msg');

		$mail = setupMailer();
		$mail->Subject = PAGE_PREFIX . $subject;
		$mail->setFrom('contact@smrealms.de');
		$mail->addReplyTo($account->getEmail(), $account->getHofName());
		$mail->Body =
			'Login:' . EOL . '------' . EOL . $account->getLogin() . EOL . EOL .
			'Account ID:' . EOL . '-----------' . EOL . $account->getAccountID() . EOL . EOL .
			'Message:' . EOL . '------------' . EOL . $msg;
		$mail->addAddress($receiver);
		$mail->send();

		$message = 'Your message has been successfully submitted!';
		$this::getLandingPage($message)->go();
	}

}
