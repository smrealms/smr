<?php declare(strict_types=1);

function create_error_offline(string $msg) : void {
	header('Location: /error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
	exit;
}

try {
	require_once('../../bootstrap.php');
	require_once(LIB . 'Album/album_functions.php');

	$session = Smr\Session::getInstance();

	if (!$session->hasAccount()) {
		create_error_offline('You need to logged in to post comments!');
	}

	if (!isset($_GET['album_id']) || empty($_GET['album_id'])) {
		create_error_offline('Which picture do you want comment?');
	}
	$album_id = $_GET['album_id'];

	if (!is_numeric($album_id)) {
		create_error_offline('Picture ID has to be numeric!');
	}

	if ($album_id < 1) {
		create_error_offline('Picture ID has to be positive!');
	}

	$account = $session->getAccount();

	if (isset($_GET['action']) && $_GET['action'] == 'Moderate') {
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

	if (!isset($_GET['comment']) || empty($_GET['comment'])) {
		create_error_offline('Please enter a comment.');
	}
	$comment = $_GET['comment'];

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
	exit;
} catch (Throwable $e) {
	handleException($e);
}
