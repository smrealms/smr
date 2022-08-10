<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;
use Smr\Request;

		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$account = $session->getAccount();

		$comment = Request::get('comment');
		if (empty($comment)) {
			create_error('We need a comment to add!');
		}

		// add this feature comment
		$db = Database::getInstance();
		$db->insert('feature_request_comments', [
			'feature_request_id' => $db->escapeNumber($var['RequestID']),
			'poster_id' => $db->escapeNumber($account->getAccountID()),
			'posting_time' => $db->escapeNumber(Epoch::time()),
			'anonymous' => $db->escapeBoolean(Request::has('anon')),
			'text' => $db->escapeString(word_filter($comment)),
		]);

		$container = Page::create('feature_request_comments.php', $var);
		$container->go();
