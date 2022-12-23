<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Exception;
use Smr\Page\AccountPage;
use Smr\Template;
use SmrAccount;

class InvalidEmail extends AccountPage {

	public string $file = 'invalid_email.php';

	public function build(SmrAccount $account, Template $template): void {
		$template->assign('PageTopic', 'Invalid E-mail Address');

		// This page should only be accessed by players whose accounts
		// have been closed due to an invalid e-mail.
		$disabled = $account->isDisabled();
		if (!$disabled || $disabled['Reason'] != CLOSE_ACCOUNT_INVALID_EMAIL_REASON) {
			throw new Exception('Account not disabled for invalid email');
		}

		$container = new InvalidEmailProcessor();
		$template->assign('ReopenLink', $container->href());
	}

}
