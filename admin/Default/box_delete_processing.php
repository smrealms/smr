<?php declare(strict_types=1);
$action = Request::get('action');
if ($action == 'Marked Messages') {
	if (!Request::has('message_id')) {
		create_error('You must choose the messages you want to delete.');
	}

	foreach (Request::getIntArray('message_id') as $id) {
		$db->query('DELETE FROM message_boxes WHERE message_id = ' . $db->escapeNumber($id));
	}
} elseif ($action == 'All Messages') {
	if (!isset($var['box_type_id'])) {
		create_error('No box selected.');
	}
	$db->query('DELETE FROM message_boxes WHERE box_type_id = ' . $db->escapeNumber($var['box_type_id']));
}
forward(create_container('skeleton.php', 'box_view.php'));
