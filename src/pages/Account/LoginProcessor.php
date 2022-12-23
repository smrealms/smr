<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Page\AccountPageProcessor;
use SmrAccount;

class LoginProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		// update last login time
		$account->updateLastLogin();

		(new GamePlay())->go();
	}

}
