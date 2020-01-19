<?php declare(strict_types=1);
$template->assign('PageTopic', 'Edit Photo');

$db->query('SELECT * FROM album WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));
if ($db->nextRecord()) {
	$albumEntry['Location'] = stripslashes($db->getField('location'));
	$albumEntry['Email'] = stripslashes($db->getField('email'));
	$albumEntry['Website'] = stripslashes($db->getField('website'));
	$albumEntry['Day'] = $db->getInt('day');
	$albumEntry['Month'] = $db->getInt('month');
	$albumEntry['Year'] = $db->getInt('year');
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

$template->assign('AlbumEditHref', SmrSession::getNewHREF(create_container('album_edit_processing.php', '')));
$template->assign('AlbumDeleteHref', SmrSession::getNewHREF(create_container('skeleton.php', 'album_delete_confirmation.php')));

if (isset($var['SuccessMsg'])) {
	$template->assign('SuccessMsg', $var['SuccessMsg']);
}
