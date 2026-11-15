<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\Page\Page;
use Smr\VoteLink;
use Smr\VoteSite;

class VoteLinkProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly VoteSite $voteSite,
	) {}

	public function build(Account $account): never {
		$voteLink = new VoteLink($this->voteSite, $account->getAccountID());
		$voteLink->setClicked();
		$voting = '<b><span class="red">v</span>o<span class="blue">t</span><span class="red">i</span>n<span class="blue">g</span></b>';
		$message = "Thank you for $voting! You will receive bonus turns for all of your traders in active games once your vote is processed.";

		Page::getLandingPage($message)->go();
	}

}
