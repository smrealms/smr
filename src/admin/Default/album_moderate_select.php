<?php declare(strict_types=1);

$template->assign('PageTopic', 'Moderate Photo Album');

require_once(LIB . 'Album/album_functions.php');

$moderateHREF = SmrSession::getNewHREF(create_container('skeleton.php', 'album_moderate.php'));
$template->assign('ModerateHREF', $moderateHREF);

// Get all accounts that are eligible for moderation
$db->query('SELECT account_id FROM album WHERE Approved = \'YES\'');
$approved = array();
while ($db->nextRecord()) {
	$accountId = $db->getInt('account_id');
	$approved[$accountId] = get_album_nick($accountId);
}
$template->assign('Approved', $approved);
