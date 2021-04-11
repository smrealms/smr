<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

$comment = Request::get('comment');
if (empty($comment)) {
	create_error('We need a comment to add!');
}

// add this feature comment
$db = Smr\Database::getInstance();
$db->query('INSERT INTO feature_request_comments (feature_request_id, poster_id, posting_time, anonymous, text)
			VALUES(' . $db->escapeNumber($var['RequestID']) . ', ' . $db->escapeNumber($account->getAccountID()) . ',' . $db->escapeNumber(Smr\Epoch::time()) . ',' . $db->escapeBoolean(Request::has('anon')) . ',' . $db->escapeString(word_filter($comment)) . ')');

$container = Page::copy($var);
$container['url'] = 'skeleton.php';
$container['body'] = 'feature_request_comments.php';
$container->go();
