<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class FeatureRequestCommentProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $featureRequestID,
		private readonly FeatureRequestComments $previousPage
	) {}

	public function build(Account $account): never {
		$comment = Request::get('comment');
		if ($comment === '') {
			create_error('We need a comment to add!');
		}

		// add this feature comment
		$db = Database::getInstance();
		$db->insert('feature_request_comments', [
			'feature_request_id' => $this->featureRequestID,
			'poster_id' => $account->getAccountID(),
			'posting_time' => Epoch::time(),
			'anonymous' => $db->escapeBoolean(Request::has('anon')),
			'text' => word_filter($comment),
		]);

		$this->previousPage->go();
	}

}
