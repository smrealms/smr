<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Page\Page;
use Smr\Request;

class VoteProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $voteID,
		private readonly Page $targetPage,
	) {}

	public function build(Account $account): never {
		if ($account->getAccountID() === ACCOUNT_ID_NHL) {
			create_error('This account is not allowed to cast a vote!');
		}

		$db = Database::getInstance();
		$db->replace('voting_results', [
			'account_id' => $account->getAccountID(),
			'vote_id' => $this->voteID,
			'option_id' => Request::getInt('vote'),
		]);

		$this->targetPage->go();
	}

}
