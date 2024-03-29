<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;
use Smr\Pages\Admin\AlbumModerate;
use Smr\Request;
use Smr\Session;

try {
	require_once('../../bootstrap.php');

	$session = Session::getInstance();

	if (!$session->hasAccount()) {
		create_error('You need to be logged in to post comments!');
	}

	$album_id = Request::getInt('album_id', 0);
	if ($album_id <= 0) {
		create_error('Whose album do you want to comment on?');
	}

	$account = $session->getAccount();

	$action = Request::get('action');
	if ($action === 'Moderate') {
		if (!$account->hasPermission(PERMISSION_MODERATE_PHOTO_ALBUM)) {
			create_error('You do not have permission to do that!');
		}
		$container = new AlbumModerate($album_id);

		$href = $container->href(true);
		$session->update();

		header('Location: ' . $href);
		exit;
	}

	$comment = Request::get('comment');
	if ($comment === '') {
		create_error('Please enter a comment.');
	}

	$comment = word_filter($comment);
	$account->sendMessageToBox(BOX_ALBUM_COMMENTS, $comment);

	// check if we have comments for this album already
	$db = Database::getInstance();
	$db->lockTable('album_has_comments');
	try {
		$dbResult = $db->read('SELECT IFNULL(MAX(comment_id)+1, 0) AS next_comment_id FROM album_has_comments WHERE album_id = :album_id', [
			'album_id' => $db->escapeNumber($album_id),
		]);
		$comment_id = $dbResult->record()->getInt('next_comment_id');

		$db->insert('album_has_comments', [
			'album_id' => $album_id,
			'comment_id' => $comment_id,
			'time' => Epoch::time(),
			'post_id' => $account->getAccountID(),
			'msg' => $comment,
		]);
	} finally {
		$db->unlock();
	}

	$nick = Request::get('album_nick');
	header('Location: /album/?nick=' . urlencode($nick));
} catch (Throwable $e) {
	handleException($e);
}
