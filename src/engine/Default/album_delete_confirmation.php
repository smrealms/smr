<?php declare(strict_types=1);
$template->assign('PageTopic', 'Delete Album Entry - Confirmation');
$template->assign('ConfirmAlbumDeleteHref', Page::create('album_delete_processing.php', '')->href());
