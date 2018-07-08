<?php

function get_album_nick($album_id) {
	if ($album_id == 0)
		return 'System';

	return SmrAccount::getAccount($album_id)->getHofName();
}

$template->assign('PageTopic','Approve Album Entries');

$db->query('SELECT *
			FROM album
			WHERE approved = \'TBC\'
			ORDER BY last_changed
			LIMIT 1');

if ($db->nextRecord()) {

	$album_id = $db->getInt('account_id');
	$location = stripslashes($db->getField('location'));
	$email = stripslashes($db->getField('email'));
	$website = stripslashes($db->getField('website'));
	$day = $db->getField('day');
	$month = $db->getField('month');
	$year = $db->getField('year');
	$other = nl2br(stripslashes($db->getField('other')));
	$last_changed = $db->getField('last_changed');
	$disabled = $db->getBoolean('disabled');

	if (empty($location)) {
		$location = 'N/A';
	}
	$template->assign('Location', $location);

	if (empty($email)) {
		$email = 'N/A';
	}
	$template->assign('Email', $email);

	if (empty($website)) {
		$website = 'N/A';
	}
	$template->assign('Website', $website);

	if (empty($other)) {
		$other = 'N/A';
	}
	$template->assign('Other', $other);

	if ($disabled) {
		$imgSrc = 'upload/0';
	} else {
		$imgSrc = 'upload/'.$album_id;
	}
	$template->assign('ImgSrc', $imgSrc);

	// get this user's nick
	$nick = get_album_nick($album_id);
	$template->assign('Nick', $nick);

	if (!empty($day) && !empty($month) && !empty($year))
		$birthdate = $month.' / '.$day.' / '.$year;
	if (empty($birthdate) && !empty($year))
		$birthdate = 'Year '.$year;
	if (empty($birthdate))
		$birthdate = 'N/A';
	$template->assign('Birthdate', $birthdate);

	// get the time that passed since the entry was last changed
	$time_passed = TIME - $last_changed;
	$template->assign('TimePassed', $time_passed);

	$container = create_container('album_approve_processing.php', '');
	$container['album_id'] = $album_id;
	$container['approved'] = true;
	$template->assign('ApproveHREF', SmrSession::getNewHREF($container));
	$container['approved'] = false;
	$template->assign('RejectHREF', SmrSession::getNewHREF($container));
}
