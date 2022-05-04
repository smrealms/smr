<?php declare(strict_types=1);

function create_error_offline(string $msg): void {
	header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
	exit;
}

try {
	require_once('../../bootstrap.php');
	require_once(LIB . 'Album/album_functions.php');

	$session = Smr\Session::getInstance();

	if (!$session->hasAccount()) {
		create_error_offline('You need to be logged in to post comments!');
	}

	$album_id = Smr\Request::getInt('album_id', 0);
	if ($album_id <= 0) {
		create_error_offline('Whose album do you want to comment on?');
	}

	$account = $session->getAccount();

	$action = Smr\Request::get('action');
	if ($action == 'Moderate') {
		if (!$account->hasPermission(PERMISSION_MODERATE_PHOTO_ALBUM)) {
			create_error_offline('You do not have permission to do that!');
		}
		$container = Page::create('skeleton.php', 'album_moderate.php');
		$container['account_id'] = $album_id;

		$href = $container->href(true);
		$session->update();

		header('Location: ' . $href);
		exit;
	}

	$db = Smr\Database::getInstance();

	$comment = Smr\Request::get('comment');
	if (empty($comment)) {
		create_error_offline('Please enter a comment.');
	}

	// get current time
	$curr_time = Smr\Epoch::time();

	$comment = word_filter($comment);
	$account->sendMessageToBox(BOX_ALBUM_COMMENTS, $comment);

	// check if we have comments for this album already
	$db->lockTable('album_has_comments');

	$dbResult = $db->read('SELECT MAX(comment_id) FROM album_has_comments WHERE album_id = ' . $db->escapeNumber($album_id));
	if ($dbResult->hasRecord()) {
		$comment_id = $dbResult->record()->getInt('MAX(comment_id)') + 1;
	} else {
		$comment_id = 1;
	}

	$db->insert('album_has_comments', [
		'album_id' => $db->escapeNumber($album_id),
		'comment_id' => $db->escapeNumber($comment_id),
		'time' => $db->escapeNumber($curr_time),
		'post_id' => $db->escapeNumber($account->getAccountID()),
		'msg' => $db->escapeString($comment),
	]);
	$db->unlock();

	header('Location: /album/?nick=' . urlencode(get_album_nick($album_id)));
} catch (Throwable $e) {
	handleException($e);
}
