<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPageProcessor;

class ReopenAccountProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		// The user has requested to reopen their account
		$account->unbanAccount($account);

		(new LoginCheckValidatedProcessor())->go();
	}

}
