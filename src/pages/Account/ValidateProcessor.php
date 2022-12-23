<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;

class ValidateProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		if (Request::get('action') == 'resend') {
			$account->sendValidationEmail();
			$message = '<span class="green">The validation code has been resent to your e-mail address!</span>';
			(new Validate($message))->go();
		}

		// Only skip validation check if we explicitly chose to validate later
		if (Request::get('action') != 'skip') {
			if ($account->getValidationCode() != Request::get('validation_code')) {
				$message = '<span class="red">The validation code you entered is incorrect!</span>';
				(new Validate($message))->go();
			}

			$account->setValidated(true);
			$account->update();

			// delete the notification (when send)
			$db = Database::getInstance();
			$db->write('DELETE FROM notification
						WHERE account_id = ' . $db->escapeNumber($account->getAccountID()) . '
						AND notification_type = \'validation_code\'');
		}

		$container = new LoginCheckAnnouncementsProcessor();
		$container->go();
	}

}
