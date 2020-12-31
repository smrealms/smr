<?php declare(strict_types=1);
$template->assign('PageTopic', 'Create Announcement');
$template->assign('AnnouncementCreateFormHref', SmrSession::getNewHREF(create_container('announcement_create_processing.php')));
if (isset($var['preview'])) {
	$template->assign('Preview', htmlentities($var['preview']));
}
