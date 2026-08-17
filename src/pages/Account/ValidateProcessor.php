<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class ValidateProcessor extends AccountPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionValidate;
	public readonly Submit $actionResend;
	public readonly Submit $actionSkip;

	public function __construct() {
		$this->actionValidate = new Submit(self::ACTION, 'validate');
		$this->actionResend = new Submit(self::ACTION, 'resend');
		$this->actionSkip = new Submit(self::ACTION, 'skip');
	}

	public function build(Account $account): never {
		$action = Request::get(self::ACTION);
		if ($action === $this->actionResend->value) {
			$account->sendValidationEmail();
			$message = '<span class="green">The validation code has been resent to your e-mail address!</span>';
			new Validate($message)->go();
		}

		// Only skip validation check if we explicitly chose to validate later
		if ($action !== $this->actionSkip->value) {
			if ($account->getValidationCode() !== Request::get('validation_code')) {
				$message = '<span class="red">The validation code you entered is incorrect!</span>';
				new Validate($message)->go();
			}

			$account->setValidated(true);
			$account->update();

			// delete the notification (when send)
			$db = Database::getInstance();
			$db->delete('notification', [
				'notification_type' => 'validation_code',
				'account_id' => $account->getAccountID(),
			]);
		}

		$container = new LoginCheckAnnouncementsProcessor();
		$container->go();
	}

}
