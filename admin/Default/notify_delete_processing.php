<?php declare(strict_types=1);
$notify_id = $_REQUEST['notify_id'];
if (!isset($notify_id)) {
	create_error('You must choose the messages you want to delete.');
}

$db->query('DELETE FROM message_notify WHERE notify_id IN (' . $db->escapeArray($notify_id) . ')');

forward(create_container('skeleton.php', 'notify_view.php'));
