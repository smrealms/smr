<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;
use Smr\Pages\Admin\AlbumModerate;
use Smr\Request;

try {
	require_once('../../bootstrap.php');
	require_once(LIB . 'Album/album_functions.php');

	$session = Smr\Session::getInstance();

	if (!$session->hasAccount()) {
		create_error('You need to be logged in to post comments!');
	}

	$album_id = Request::getInt('album_id', 0);
	if ($album_id <= 0) {
		create_error('Whose album do you want to comment on?');
	}

	$account = $session->getAccount();

	$action = Request::get('action');
	if ($action == 'Moderate') {
		if (!$account->hasPermission(PERMISSION_MODERATE_PHOTO_ALBUM)) {
			create_error('You do not have permission to do that!');
		}
		$container = new AlbumModerate($album_id);

		$href = $container->href(true);
		$session->update();

		header('Location: ' . $href);
		exit;
	}

	$db = Database::getInstance();

	$comment = Request::get('comment');
	if (empty($comment)) {
		create_error('Please enter a comment.');
	}

	// get current time
	$curr_time = Epoch::time();

	$comment = word_filter($comment);
	$account->sendMessageToBox(BOX_ALBUM_COMMENTS, $comment);

	// check if we have comments for this album already
	$db->lockTable('album_has_comments');

	$dbResult = $db->read('SELECT IFNULL(MAX(comment_id)+1, 0) AS next_comment_id FROM album_has_comments WHERE album_id = ' . $db->escapeNumber($album_id));
	$comment_id = $dbResult->record()->getInt('next_comment_id');

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
