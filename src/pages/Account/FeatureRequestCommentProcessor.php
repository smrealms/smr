<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;

class FeatureRequestCommentProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $featureRequestID,
		private readonly FeatureRequestComments $previousPage
	) {}

	public function build(SmrAccount $account): never {
		$comment = Request::get('comment');
		if (empty($comment)) {
			create_error('We need a comment to add!');
		}

		// add this feature comment
		$db = Database::getInstance();
		$db->insert('feature_request_comments', [
			'feature_request_id' => $db->escapeNumber($this->featureRequestID),
			'poster_id' => $db->escapeNumber($account->getAccountID()),
			'posting_time' => $db->escapeNumber(Epoch::time()),
			'anonymous' => $db->escapeBoolean(Request::has('anon')),
			'text' => $db->escapeString(word_filter($comment)),
		]);

		$this->previousPage->go();
	}

}
