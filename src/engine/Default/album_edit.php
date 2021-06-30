<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

$template->assign('PageTopic', 'Edit Photo');

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT * FROM album WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));
if ($dbResult->hasRecord()) {
	$dbRecord = $dbResult->record();
	$day = $dbRecord->getInt('day');
	$month = $dbRecord->getInt('month');
	$year = $dbRecord->getInt('year');
	$albumEntry['Location'] = $dbRecord->getField('location');
	$albumEntry['Email'] = $dbRecord->getField('email');
	$albumEntry['Website'] = $dbRecord->getField('website');
	$albumEntry['Day'] = $day > 0 ? $day : '';
	$albumEntry['Month'] = $month > 0 ? $month : '';
	$albumEntry['Year'] = $year > 0 ? $year : '';
	$albumEntry['Other'] = $dbRecord->getField('other');
	$approved = $dbRecord->getField('approved');

	if ($approved == 'TBC') {
		$albumEntry['Status'] = ('<span style="color:orange;">Waiting approval</span>');
	} elseif ($approved == 'NO') {
		$albumEntry['Status'] = ('<span class="red">Approval denied</span>');
	} elseif ($dbRecord->getBoolean('disabled')) {
		$albumEntry['Status'] = ('<span class="red">Disabled</span>');
	} elseif ($approved == 'YES') {
		$albumEntry['Status'] = ('<a href="album/?nick=' . urlencode($account->getHofName()) . '" class="dgreen">Online</a>');
	}

	if (is_readable(UPLOAD . $account->getAccountID())) {
		$albumEntry['Image'] = '/upload/' . $account->getAccountID();
	}

	$template->assign('AlbumEntry', $albumEntry);
}

$template->assign('AlbumEditHref', Page::create('album_edit_processing.php', '')->href());
$template->assign('AlbumDeleteHref', Page::create('skeleton.php', 'album_delete_confirmation.php')->href());

if (isset($var['SuccessMsg'])) {
	$template->assign('SuccessMsg', $var['SuccessMsg']);
}
