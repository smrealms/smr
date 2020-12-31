<?php declare(strict_types=1);
if (!Request::has('notify_id')) {
	create_error('You must choose the messages you want to delete.');
}

$db->query('DELETE FROM message_notify WHERE notify_id IN (' . $db->escapeArray(Request::getIntArray('notify_id')) . ')');

forward(create_container('skeleton.php', 'notify_view.php'));
