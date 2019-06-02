<?php
$template->assign('PageTopic', 'Create Announcement');
$template->assign('AnnouncementCreateFormHref', SmrSession::getNewHREF(create_container('announcement_create_processing.php')));
if (isset($var['preview'])) {
	$template->assign('Preview', $var['preview']);
}
