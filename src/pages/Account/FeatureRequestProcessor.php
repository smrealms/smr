<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;

class FeatureRequestProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		$feature = Request::get('feature');
		if (empty($feature)) {
			create_error('We need at least a feature description!');
		}
		if (strlen($feature) > 500) {
			create_error('Feature request longer than 500 characters, please be more concise!');
		}

		// add this feature to db
		$db = Database::getInstance();
		$featureRequestID = $db->insert('feature_request', []);
		$db->insert('feature_request_comments', [
			'feature_request_id' => $db->escapeNumber($featureRequestID),
			'poster_id' => $db->escapeNumber($account->getAccountID()),
			'posting_time' => $db->escapeNumber(Epoch::time()),
			'anonymous' => $db->escapeBoolean(Request::has('anon')),
			'text' => $db->escapeString(word_filter($feature)),
		]);

		// vote for this feature
		$db->insert('account_votes_for_feature', [
			'account_id' => $db->escapeNumber($account->getAccountID()),
			'feature_request_id' => $db->escapeNumber($featureRequestID),
			'vote_type' => $db->escapeString('YES'),
		]);

		(new FeatureRequest())->go();
	}

}
