<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;

class InvalidEmailProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		if (Request::get('action') == 'Resend Validation Code') {
			$account->changeEmail($account->getEmail());
		} else {
			$account->changeEmail(Request::get('email'));
		}
		$account->update();
		(new Validate())->go();
	}

}
