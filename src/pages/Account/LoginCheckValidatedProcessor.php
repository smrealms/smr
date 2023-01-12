<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPageProcessor;

class LoginCheckValidatedProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		// is account validated?
		if (!$account->isValidated()) {
			(new Validate())->go();
		}

		(new LoginCheckAnnouncementsProcessor())->go();
	}

}
