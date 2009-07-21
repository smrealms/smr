<?php
$template->assign('PageTopic','Delete Album Entry - Confirmation');
$template->assign('ConfirmAlbumDeleteHref',SmrSession::get_new_href(create_container('album_delete_processing.php', '')));
?>