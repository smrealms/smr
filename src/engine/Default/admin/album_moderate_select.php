<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Moderate Photo Album');

require_once(LIB . 'Album/album_functions.php');

$moderateHREF = Page::create('skeleton.php', 'admin/album_moderate.php')->href();
$template->assign('ModerateHREF', $moderateHREF);

// Get all accounts that are eligible for moderation
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT account_id FROM album WHERE Approved = \'YES\'');
$approved = [];
foreach ($dbResult->records() as $dbRecord) {
	$accountId = $dbRecord->getInt('account_id');
	$approved[$accountId] = get_album_nick($accountId);
}
$template->assign('Approved', $approved);
