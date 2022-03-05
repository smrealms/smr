<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

$comment = Smr\Request::get('comment');
if (empty($comment)) {
	create_error('We need a comment to add!');
}

// add this feature comment
$db = Smr\Database::getInstance();
$db->insert('feature_request_comments', [
	'feature_request_id' => $db->escapeNumber($var['RequestID']),
	'poster_id' => $db->escapeNumber($account->getAccountID()),
	'posting_time' => $db->escapeNumber(Smr\Epoch::time()),
	'anonymous' => $db->escapeBoolean(Smr\Request::has('anon')),
	'text' => $db->escapeString(word_filter($comment)),
]);

$container = Page::copy($var);
$container['url'] = 'skeleton.php';
$container['body'] = 'feature_request_comments.php';
$container->go();
