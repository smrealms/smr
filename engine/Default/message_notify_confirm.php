<?php declare(strict_types=1);

if (!isset($var['notified_time'])) {
	SmrSession::updateVar('notified_time', TIME);
}

if (empty($var['message_id'])) {
	create_error('Please click the small yellow icon to report a message!');
}

// get message form db
$db->query('SELECT message_text
			FROM message
			WHERE message_id = ' . $db->escapeNumber($var['message_id']));
if (!$db->nextRecord()) {
	create_error('Could not find the message you selected!');
}

$template->assign('MessageText', $db->getField('message_text'));

$container = create_container('message_notify_processing.php', '');
transfer('message_id');
transfer('sent_time');
transfer('notified_time');
$template->assign('ProcessingHREF', SmrSession::getNewHREF($container));

$template->assign('PageTopic', 'Report a Message');
Menu::messages();
