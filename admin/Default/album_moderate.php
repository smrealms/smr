<?php declare(strict_types=1);

$template->assign('PageTopic', 'Moderate Photo Album');

require_once(LIB . 'Album/album_functions.php');

$account_id = SmrSession::getRequestVarInt('account_id');

// check if the given account really has an entry
$db->query('SELECT * FROM album WHERE account_id = ' . $db->escapeNumber($account_id) . ' AND Approved = \'YES\'');
if ($db->nextRecord()) {
	$disabled = $db->getBoolean('disabled');
	$location = stripslashes($db->getField('location'));
	$email = stripslashes($db->getField('email'));
	$website = stripslashes($db->getField('website'));
	$day = $db->getField('day');
	$month = $db->getField('month');
	$year = $db->getField('year');
	$other = nl2br(stripslashes($db->getField('other')));
} else {
	create_error('This User doesn\'t have an album entry or it needs to be approved first!');
}

if (!empty($day) && !empty($month) && !empty($year)) {
	$birthdate = $month . ' / ' . $day . ' / ' . $year;
}
if (empty($birthdate) && !empty($year)) {
	$birthdate = 'Year ' . $year;
}
if (empty($birthdate)) {
	$birthdate = 'N/A';
}

$entry = [
	'disabled' => $disabled,
	'location' => empty($location) ? 'N/A' : $location,
	'email' => empty($email) ? 'N/A' : $email,
	'website' => empty($website) ? 'N/A' : '<a href="' . $website . '" target="_new">' . $website . '</a>',
	'birthdate' => $birthdate,
	'other' => empty($other) ? 'N/A' : $other,
	'nickname' => get_album_nick($account_id),
	'upload' => 'upload/' . $account_id,
];
$template->assign('Entry', $entry);

$template->assign('BackHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'album_moderate_select.php')));

$container = create_container('album_moderate_processing.php');
$container['account_id'] = $account_id;

$container['task'] = 'reset_image';
$template->assign('ResetImageHREF', SmrSession::getNewHREF($container));
$container['task'] = 'reset_location';
$template->assign('ResetLocationHREF', SmrSession::getNewHREF($container));
$container['task'] = 'reset_email';
$template->assign('ResetEmailHREF', SmrSession::getNewHREF($container));
$container['task'] = 'reset_website';
$template->assign('ResetWebsiteHREF', SmrSession::getNewHREF($container));
$container['task'] = 'reset_birthdate';
$template->assign('ResetBirthdateHREF', SmrSession::getNewHREF($container));
$container['task'] = 'reset_other';
$template->assign('ResetOtherHREF', SmrSession::getNewHREF($container));
$container['task'] = 'delete_comment';
$template->assign('DeleteCommentHREF', SmrSession::getNewHREF($container));

$default_email = 'Dear Photo Album User,' . EOL . EOL .
				 'You have received this email as notification that the picture you submitted to the Space Merchant Realms Photo Album has been temporarily disabled due to a Photo Album Rules violation.' . EOL .
				 'Please visit ' . URL . '/album.php or log into the SMR site to upload a new picture.' . EOL .
				 'Reply to this email when you have uploaded a new picture so we may re-enable your pic.' . EOL .
				 'Note: Please allow up to 48 hours for changes to occur.' . EOL .
				 'Thanks,' . EOL . EOL .
				 'Admin Team';
$template->assign('DisableEmail', $default_email);

$db->query('SELECT *
			FROM album_has_comments
			WHERE album_id = '.$db->escapeNumber($account_id));
$comments = array();
while ($db->nextRecord()) {
	$comments[] = [
		'id' => $db->getInt('comment_id'),
		'date' => date(DATE_FULL_SHORT, $db->getInt('time')),
		'postee' => get_album_nick($db->getInt('post_id')),
		'msg' => stripslashes($db->getField('msg')),
	];
}
$template->assign('Comments', $comments);
