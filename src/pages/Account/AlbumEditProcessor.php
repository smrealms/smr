<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use Uri\WhatWg\Url;

class AlbumEditProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$location = Request::get('location');
		$email = Request::get('email');

		// Get the website and validate its format without fetching it.
		$website = Request::get('website');
		if ($website !== '') {
			$urlErrors = [];
			$url = Url::parse($website, errors: $urlErrors);
			if (
				$url === null
				|| $urlErrors !== []
				|| $url->getScheme() !== 'https'
				|| $url->getAsciiHost() === null
				|| $url->getUsername() !== null
				|| $url->getPassword() !== null
				|| $url->getPort() !== null
			) {
				create_error('Please enter a valid HTTPS website URL.');
			}
			$website = $url->toAsciiString();
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
		$dbResult = $db->select('album', [
			'account_id' => $account->getAccountID(),
		]);
		$comment = null;
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

		if ($comment !== null) {
			// check if we have comments for this album already
			$db->lockTable('album_has_comments');
			try {
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
			} finally {
				$db->unlock();
			}
		}

		$successMsg = 'SUCCESS: Your information has been updated!';
		$container = new AlbumEdit($successMsg);
		$container->go();
	}

}
