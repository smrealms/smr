<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

/**
 * Determine whether a URL is reachable based on HTTP status code class.
 */
function isUrlReachable(string $url): bool {
	$ch = curl_init($url);
	if ($ch === false) {
		throw new Exception('Failed to initialize curl');
	}
	curl_setopt_array($ch, [
		CURLOPT_HEADER => true,
		CURLOPT_NOBODY => true, // headers only
		CURLOPT_RETURNTRANSFER => true, // don't print output
		CURLOPT_TIMEOUT => 5, // in seconds
	]);
	curl_exec($ch);
	$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	$statusClass = IFloor($statusCode / 100);
	return $statusClass === 2 || $statusClass === 3;
}

class AlbumEditProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$location = Request::get('location');
		$email = Request::get('email');

		// get website (and validate it)
		$website = Request::get('website');
		if ($website !== '') {
			// add http:// if missing
			if (!preg_match('=://=', $website)) {
				$website = 'http://' . $website;
			}

			// validate
			if (!isUrlReachable($website)) {
				create_error('The website you entered is invalid!');
			}
		}

		$other = Request::get('other');

		$day = Request::getInt('day');
		$month = Request::getInt('month');
		$year = Request::getInt('year');

		// check if we have an image
		$noPicture = true;
		if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
			$noPicture = false;
			// get dimensions
			$size = getimagesize($_FILES['photo']['tmp_name']);
			if ($size === false) {
				create_error('Uploaded file must be an image!');
			}

			$allowed_types = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG];
			if (!in_array($size[2], $allowed_types, true)) {
				create_error('Only gif, jpg or png-image allowed!');
			}

			// check if width > 500
			if ($size[0] > 500) {
				create_error('Image is wider than 500 pixels!');
			}

			// check if height > 500
			if ($size[1] > 500) {
				create_error('Image is higher than 500 pixels!');
			}

			if (!move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD . $account->getAccountID())) {
				create_error('Failed to upload image!');
			}
		}

		// check if we had a album entry so far
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM album WHERE account_id = :account_id', [
			'account_id' => $db->escapeNumber($account->getAccountID()),
		]);
		if ($dbResult->hasRecord()) {
			if (!$noPicture) {
				$comment = '<span class="green">*** Picture changed</span>';
			}

			// change album entry
			$db->update(
				'album',
				[
					'approved' => 'TBC',
					'disabled' => 'FALSE',
					'location' => $location,
					'email' => $email,
					'website' => $website,
					'day' => $day,
					'month' => $month,
					'year' => $year,
					'other' => $other,
					'last_changed' => Epoch::time(),
				],
				['account_id' => $account->getAccountID()],
			);
		} else {
			// if he didn't upload a picture before
			// we kick him out here
			if ($noPicture) {
				create_error('What is it worth if you don\'t upload an image?');
			}

			$comment = '<span class="green">*** Picture added</span>';

			// add album entry
			$db->insert('album', [
				'account_id' => $account->getAccountID(),
				'location' => $location,
				'email' => $email,
				'website' => $website,
				'day' => $day,
				'month' => $month,
				'year' => $year,
				'other' => $other,
				'created' => Epoch::time(),
				'last_changed' => Epoch::time(),
				'approved' => 'TBC',
			]);
		}

		if (!empty($comment)) {
			// check if we have comments for this album already
			$db->lockTable('album_has_comments');

			$dbResult = $db->read('SELECT IFNULL(MAX(comment_id)+1, 0) AS next_comment_id FROM album_has_comments WHERE album_id = :album_id', [
				'album_id' => $db->escapeNumber($account->getAccountID()),
			]);
			$comment_id = $dbResult->record()->getInt('next_comment_id');

			$db->insert('album_has_comments', [
				'album_id' => $account->getAccountID(),
				'comment_id' => $comment_id,
				'time' => Epoch::time(),
				'post_id' => 0,
				'msg' => $comment,
			]);
			$db->unlock();
		}

		$successMsg = 'SUCCESS: Your information has been updated!';
		$container = new AlbumEdit($successMsg);
		$container->go();
	}

}
