<?php declare(strict_types=1);

use Smr\Album;
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

	Album::addComment(
		albumAccountID: $album_id,
		commentAccountID: $account->getAccountID(),
		comment: $comment,
	);

	$nick = Request::get('album_nick');
	header('Location: /album/?nick=' . urlencode($nick));
} catch (Throwable $e) {
	handleException($e);
}
