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
$db->write('INSERT INTO feature_request (feature_request_id) VALUES (NULL)');
$featureRequestID = $db->getInsertID();
$db->write('INSERT INTO feature_request_comments (feature_request_id, poster_id, posting_time, anonymous, text) ' .
								'VALUES(' . $db->escapeNumber($featureRequestID) . ', ' . $db->escapeNumber($account->getAccountID()) . ',' . $db->escapeNumber(Smr\Epoch::time()) . ',' . $db->escapeBoolean(Smr\Request::has('anon')) . ',' . $db->escapeString(word_filter($feature)) . ')');

// vote for this feature
$db->write('INSERT INTO account_votes_for_feature VALUES(' . $db->escapeNumber($account->getAccountID()) . ', ' . $db->escapeNumber($featureRequestID) . ',\'YES\')');

Page::create('skeleton.php', 'feature_request.php')->go();
