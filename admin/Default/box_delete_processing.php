<?php
$action = $_REQUEST['action'];
if ($action == 'Marked Messages') {
	if (!isset($_REQUEST['message_id']))
		create_error('You must choose the messages you want to delete.');

	foreach ($_REQUEST['message_id'] as $id)
		$db->query('DELETE FROM message_boxes WHERE message_id = '.$db->escapeNumber($id));
}
else if ($action == 'All Messages') {
	if (!isset($var['box_type_id']))
		create_error('No box selected.');
	$db->query('DELETE FROM message_boxes WHERE box_type_id = '.$db->escapeNumber($var['box_type_id']));
}
forward(create_container('skeleton.php', 'box_view.php'));
