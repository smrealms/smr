<?php

if (empty($_REQUEST['comment']))
	create_error('We need a comment to add!');

// add this feature comment
$db->query('INSERT INTO feature_request_comments (feature_request_id, poster_id, posting_time, anonymous, text)
			VALUES(' . $db->escapeNumber($var['RequestID']) . ', ' . $db->escapeNumber($account->getAccountID()) . ',' . $db->escapeNumber(TIME) . ',' . $db->escapeBoolean(isset($_REQUEST['anon'])) . ',' . $db->escapeString(word_filter($_REQUEST['comment'])).')');

$container = $var;
$container['url'] = 'skeleton.php';
$container['body'] = 'feature_request_comments.php';
forward($container);
