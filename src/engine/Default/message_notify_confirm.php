<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

if (!isset($var['notified_time'])) {
	$session->updateVar('notified_time', Smr\Epoch::time());
}

if (empty($var['message_id'])) {
	create_error('Please click the small yellow icon to report a message!');
}

// get message form db
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT message_text
			FROM message
			WHERE message_id = ' . $db->escapeNumber($var['message_id']));
if (!$dbResult->hasRecord()) {
	create_error('Could not find the message you selected!');
}

$template->assign('MessageText', $dbResult->record()->getField('message_text'));

$container = Page::create('message_notify_processing.php', '');
$container->addVar('folder_id');
$container->addVar('message_id');
$container->addVar('sent_time');
$container->addVar('notified_time');
$template->assign('ProcessingHREF', $container->href());

$template->assign('PageTopic', 'Report a Message');
Menu::messages();
