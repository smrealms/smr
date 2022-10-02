<?php declare(strict_types=1);

use Smr\Database;
use Smr\Request;

$var = Smr\Session::getInstance()->getCurrentVar();

$container = Page::create('message_view.php');
$container->addVar('folder_id');

if (Request::get('action') == 'No') {
	$container->go();
}

if (empty($var['message_id'])) {
	create_error('Please click the small yellow icon to report a message!');
}

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

// get next id
$db = Database::getInstance();
$dbResult = $db->read('SELECT IFNULL(max(notify_id)+1, 0) as next_notify_id FROM message_notify WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY notify_id DESC');
$notify_id = $dbResult->record()->getInt('next_notify_id');

// get message form db
$dbResult = $db->read('SELECT account_id, sender_id, message_text
			FROM message
			WHERE message_id = ' . $var['message_id'] . ' AND receiver_delete = \'FALSE\'');
if (!$dbResult->hasRecord()) {
	create_error('Could not find the message you selected!');
}
$dbRecord = $dbResult->record();

// insert
$db->insert('message_notify', [
	'notify_id' => $db->escapeNumber($notify_id),
	'game_id' => $db->escapeNumber($player->getGameID()),
	'from_id' => $dbRecord->getInt('sender_id'),
	'to_id' => $dbRecord->getInt('account_id'),
	'text' => $db->escapeString($dbRecord->getString('message_text')),
	'sent_time' => $db->escapeNumber($var['sent_time']),
	'notify_time' => $db->escapeNumber($var['notified_time']),
]);

$container->go();
