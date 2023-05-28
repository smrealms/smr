<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPageProcessor;

class LoginProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		// update last login time
		$account->updateLastLogin();

		$this::getLandingPage()->go();
	}

}
