<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class VoteCreateSelectProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		new VoteCreate(voteID: Request::getInt('vote'))->go();
	}

}
