<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Page\AccountPageProcessor;
use SmrAccount;

class ReopenAccountProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		// The user has requested to reopen their account
		$account->unbanAccount($account);

		(new LoginCheckValidatedProcessor())->go();
	}

}
