<?php declare(strict_types=1);
if (!Smr\Request::has('notify_id')) {
	create_error('You must choose the messages you want to delete.');
}

$db = Smr\Database::getInstance();
$db->write('DELETE FROM message_notify WHERE notify_id IN (' . $db->escapeArray(Smr\Request::getIntArray('notify_id')) . ')');

Page::create('skeleton.php', 'notify_view.php')->go();
