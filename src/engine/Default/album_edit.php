<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

$template->assign('PageTopic', 'Edit Photo');

$db = Smr\Database::getInstance();
$db->query('SELECT * FROM album WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));
if ($db->nextRecord()) {
	$day = $db->getInt('day');
	$month = $db->getInt('month');
	$year = $db->getInt('year');
	$albumEntry['Location'] = stripslashes($db->getField('location'));
	$albumEntry['Email'] = stripslashes($db->getField('email'));
	$albumEntry['Website'] = stripslashes($db->getField('website'));
	$albumEntry['Day'] = $day > 0 ? $day : '';
	$albumEntry['Month'] = $month > 0 ? $month : '';
	$albumEntry['Year'] = $year > 0 ? $year : '';
	$albumEntry['Other'] = stripslashes($db->getField('other'));
	$approved = $db->getField('approved');

	if ($approved == 'TBC') {
		$albumEntry['Status'] = ('<span style="color:orange;">Waiting approval</span>');
	} elseif ($approved == 'NO') {
		$albumEntry['Status'] = ('<span class="red">Approval denied</span>');
	} elseif ($db->getBoolean('disabled')) {
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
