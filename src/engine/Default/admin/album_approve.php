<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;

function get_album_nick(int $album_id): string {
	if ($album_id == 0) {
		return 'System';
	}

	return SmrAccount::getAccount($album_id)->getHofDisplayName();
}

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Approve Album Entries');

$db = Database::getInstance();
$dbResult = $db->read('SELECT *
			FROM album
			WHERE approved = \'TBC\'
			ORDER BY last_changed
			LIMIT 1');

if ($dbResult->hasRecord()) {
	$dbRecord = $dbResult->record();
	$album_id = $dbRecord->getInt('account_id');
	$location = $dbRecord->getNullableString('location');
	$email = $dbRecord->getNullableString('email');
	$website = $dbRecord->getNullableString('website');
	$day = $dbRecord->getInt('day');
	$month = $dbRecord->getInt('month');
	$year = $dbRecord->getInt('year');
	$other = nl2br($dbRecord->getString('other'));
	$last_changed = $dbRecord->getInt('last_changed');
	$disabled = $dbRecord->getBoolean('disabled');

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
		$imgSrc = 'upload/' . $album_id;
	}
	$template->assign('ImgSrc', $imgSrc);

	// get this user's nick
	$nick = get_album_nick($album_id);
	$template->assign('Nick', $nick);

	if (!empty($day) && !empty($month) && !empty($year)) {
		$birthdate = $month . ' / ' . $day . ' / ' . $year;
	}
	if (empty($birthdate) && !empty($year)) {
		$birthdate = 'Year ' . $year;
	}
	if (empty($birthdate)) {
		$birthdate = 'N/A';
	}
	$template->assign('Birthdate', $birthdate);

	// get the time that passed since the entry was last changed
	$time_passed = Epoch::time() - $last_changed;
	$template->assign('TimePassed', $time_passed);

	$container = Page::create('admin/album_approve_processing.php');
	$container['album_id'] = $album_id;
	$container['approved'] = true;
	$template->assign('ApproveHREF', $container->href());
	$container['approved'] = false;
	$template->assign('RejectHREF', $container->href());
}
