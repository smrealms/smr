<?php declare(strict_types=1);
$template->assign('PageTopic', 'Create Announcement');
$template->assign('AnnouncementCreateFormHref', Page::create('announcement_create_processing.php')->href());
if (isset($var['preview'])) {
	$template->assign('Preview', htmlentities($var['preview']));
}
