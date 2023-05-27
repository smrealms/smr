<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class FeatureRequestProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$feature = Request::get('feature');
		if ($feature === '') {
			create_error('We need at least a feature description!');
		}
		if (strlen($feature) > 500) {
			create_error('Feature request longer than 500 characters, please be more concise!');
		}

		// add this feature to db
		$db = Database::getInstance();
		$featureRequestID = $db->insert('feature_request', []);
		$db->insert('feature_request_comments', [
			'feature_request_id' => $featureRequestID,
			'poster_id' => $account->getAccountID(),
			'posting_time' => Epoch::time(),
			'anonymous' => $db->escapeBoolean(Request::has('anon')),
			'text' => word_filter($feature),
		]);

		// vote for this feature
		$db->insert('account_votes_for_feature', [
			'account_id' => $account->getAccountID(),
			'feature_request_id' => $featureRequestID,
			'vote_type' => 'YES',
		]);

		(new FeatureRequest())->go();
	}

}
