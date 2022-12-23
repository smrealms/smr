<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Page\AccountPageProcessor;
use SmrAccount;

class LoginCheckValidatedProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		// is account validated?
		if (!$account->isValidated()) {
			(new Validate())->go();
		}

		(new LoginCheckAnnouncementsProcessor())->go();
	}

}
