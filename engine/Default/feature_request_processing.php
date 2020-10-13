<?php declare(strict_types=1);

$feature = Request::get('feature');
if (empty($feature)) {
	create_error('We need at least a feature description!');
}
if (strlen($feature) > 500) {
	create_error('Feature request longer than 500 characters, please be more concise!');
}

// add this feature to db
$db->query('INSERT INTO feature_request (feature_request_id) VALUES (NULL)');
$featureRequestID = $db->getInsertID();
$db->query('INSERT INTO feature_request_comments (feature_request_id, poster_account_id, posting_time, anonymous, text) ' .
								'VALUES(' . $db->escapeNumber($featureRequestID) . ', ' . $db->escapeNumber($account->getAccountID()) . ',' . $db->escapeNumber(TIME) . ',' . $db->escapeBoolean(Request::has('anon')) . ',' . $db->escapeString(word_filter($feature)) . ')');

// vote for this feature
$db->query('INSERT INTO account_votes_for_feature VALUES(' . $db->escapeNumber($account->getAccountID()) . ', ' . $db->escapeNumber($featureRequestID) . ',\'YES\')');

forward(create_container('skeleton.php', 'feature_request.php'));
