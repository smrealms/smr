<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

$location = Smr\Request::get('location');
$email = Smr\Request::get('email');

// get website (and validate it)
$website = Smr\Request::get('website');
if ($website != '') {
	// add http:// if missing
	if (!preg_match('=://=', $website)) {
		$website = 'http://' . $website;
	}

	// validate
	$status = floor(php_link_check($website) / 100);

	if ($status != 2 && $status != 3) {
		create_error('The website you entered is invalid!');
	}
}

$other = Smr\Request::get('other');

$day = Smr\Request::getInt('day');
$month = Smr\Request::getInt('month');
$year = Smr\Request::getInt('year');

// check if we have an image
$noPicture = true;
if ($_FILES['photo']['error'] == UPLOAD_ERR_OK) {
	$noPicture = false;
	// get dimensions
	$size = getimagesize($_FILES['photo']['tmp_name']);
	if ($size === false) {
		create_error('Uploaded file must be an image!');
	}

	$allowed_types = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG];
	if (!in_array($size[2], $allowed_types)) {
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
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT 1 FROM album WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));
if ($dbResult->hasRecord()) {
	if (!$noPicture) {
		$comment = '<span class="green">*** Picture changed</span>';
	}

	// change album entry
	$db->write('UPDATE album
				SET location = ' . $db->escapeString($location) . ',
					email = ' . $db->escapeString($email) . ',
					website= ' . $db->escapeString($website) . ',
					day = ' . $db->escapeNumber($day) . ',
					month = ' . $db->escapeNumber($month) . ',
					year = ' . $db->escapeNumber($year) . ',
					other = ' . $db->escapeString($other) . ',
					last_changed = ' . $db->escapeNumber(Smr\Epoch::time()) . ',
					approved = \'TBC\',
					disabled = \'FALSE\'
				WHERE account_id = ' . $db->escapeNumber($account->getAccountID()) . ' LIMIT 1');
} else {
	// if he didn't upload a picture before
	// we kick him out here
	if ($noPicture) {
		create_error('What is it worth if you don\'t upload an image?');
	}

	$comment = '<span class="green">*** Picture added</span>';

	// add album entry
	$db->insert('album', [
		'account_id' => $db->escapeNumber($account->getAccountID()),
		'location' => $db->escapeString($location),
		'email' => $db->escapeString($email),
		'website' => $db->escapeString($website),
		'day' => $db->escapeNumber($day),
		'month' => $db->escapeNumber($month),
		'year' => $db->escapeNumber($year),
		'other' => $db->escapeString($other),
		'created' => $db->escapeNumber(Smr\Epoch::time()),
		'last_changed' => $db->escapeNumber(Smr\Epoch::time()),
		'approved' => $db->escapeString('TBC'),
	]);
}

if (!empty($comment)) {
	// check if we have comments for this album already
	$db->lockTable('album_has_comments');

	$dbResult = $db->read('SELECT MAX(comment_id) FROM album_has_comments WHERE album_id = ' . $db->escapeNumber($account->getAccountID()));
	if ($dbResult->hasRecord()) {
		$comment_id = $dbResult->record()->getInt('MAX(comment_id)') + 1;
	} else {
		$comment_id = 1;
	}

	$db->insert('album_has_comments', [
		'album_id' => $db->escapeNumber($account->getAccountID()),
		'comment_id' => $db->escapeNumber($comment_id),
		'time' => $db->escapeNumber(Smr\Epoch::time()),
		'post_id' => 0,
		'msg' => $db->escapeString($comment),
	]);
	$db->unlock();
}

$container = Page::create('skeleton.php', 'album_edit.php');
$container['SuccessMsg'] = 'SUCCESS: Your information has been updated!';
$container->go();

function php_link_check(string $url): string|false {
	/*	Purpose: Check HTTP Links
	*	Usage:	$var = phpLinkCheck(absoluteURI)
	*					$var['Status-Code'] will return the HTTP status code
	*					(e.g. 200 or 404). In case of a 3xx code (redirection)
	*					$var['Location-Status-Code'] will contain the status
	*					code of the new loaction.
	*					See echo_r($var) for the complete result
	*
	*	Author:	Johannes Froemter <j-f@gmx.net>
	*	Date:		2001-04-14
	*	Version: 0.1 (currently requires PHP4)
	*/
	$url = trim($url);
	if (!preg_match('=://=', $url)) {
		$url = 'http://' . $url;
	}
	$url = parse_url($url);
	if (!in_array(strtolower($url['scheme']), ['http', 'https'])) {
		return false;
	}

	if (!isset($url['port'])) {
		$url['port'] = 80;
	}
	if (!isset($url['path'])) {
		$url['path'] = '/';
	}

	if (!checkdnsrr($url['host'], 'A')) {
		return false;
	}

	$fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);
	if ($fp === false) {
		return false;
	}

	$head = '';
	$httpRequest = 'HEAD ' . $url['path'] . ' HTTP/1.1' . EOL
							. 'Host: ' . $url['host'] . EOL
							. 'Connection: close' . EOL . EOL;
	fwrite($fp, $httpRequest);
	while (!feof($fp)) {
		$head .= fgets($fp, 1024);
	}
	fclose($fp);

	preg_match('=^(HTTP/\d+\.\d+) (\d{3}) ([^\r\n]*)=', $head, $matches);
	$http = [
		'Status-Line' => $matches[0],
		'HTTP-Version' => $matches[1],
		'Status-Code' => $matches[2],
		'Reason-Phrase' => $matches[3],
	];

	return $http['Status-Code'];
}
