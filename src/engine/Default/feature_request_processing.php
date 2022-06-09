<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

$feature = Smr\Request::get('feature');
if (empty($feature)) {
	create_error('We need at least a feature description!');
}
if (strlen($feature) > 500) {
	create_error('Feature request longer than 500 characters, please be more concise!');
}

// add this feature to db
$db = Smr\Database::getInstance();
$featureRequestID = $db->insert('feature_request', []);
$db->insert('feature_request_comments', [
	'feature_request_id' => $db->escapeNumber($featureRequestID),
	'poster_id' => $db->escapeNumber($account->getAccountID()),
	'posting_time' => $db->escapeNumber(Smr\Epoch::time()),
	'anonymous' => $db->escapeBoolean(Smr\Request::has('anon')),
	'text' => $db->escapeString(word_filter($feature)),
]);

// vote for this feature
$db->insert('account_votes_for_feature', [
	'account_id' => $db->escapeNumber($account->getAccountID()),
	'feature_request_id' => $db->escapeNumber($featureRequestID),
	'vote_type' => $db->escapeString('YES'),
]);

Page::create('feature_request.php')->go();
