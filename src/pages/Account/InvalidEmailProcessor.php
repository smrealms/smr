<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Exception;
use Smr\Account;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class InvalidEmailProcessor extends AccountPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionResend;
	public readonly Submit $actionChange;

	public function __construct() {
		$this->actionResend = new Submit(self::ACTION, 'Resend Validation Code');
		$this->actionChange = new Submit(self::ACTION, 'Change E-mail Address');
	}

	public function build(Account $account): never {
		$action = Request::get(self::ACTION);
		if ($action === $this->actionResend->value) {
			$account->changeEmail($account->getEmail());
		} elseif ($action === $this->actionChange->value) {
			$account->changeEmail(Request::get('email'));
		} else {
			throw new Exception('Unknown action: ' . $action);
		}
		$account->update();
		(new Validate())->go();
	}

}
