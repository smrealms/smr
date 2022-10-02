<?php declare(strict_types=1);

use Smr\Database;
use Smr\Messages;

$template = Smr\Template::getInstance();
$db = Database::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

Menu::messages();

$template->assign('PageTopic', 'View Messages');

$messageBoxes = [];
foreach (Messages::getMessageTypeNames() as $message_type_id => $message_type_name) {
	$messageBox = [];
	$messageBox['Name'] = $message_type_name;

	// do we have unread msges in that folder?
	if ($message_type_id == MSG_SENT) {
		$messageBox['HasUnread'] = false;
	} else {
		$dbResult = $db->read('SELECT 1 FROM message
				WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber($message_type_id) . '
					AND msg_read = ' . $db->escapeBoolean(false) . '
					AND receiver_delete = ' . $db->escapeBoolean(false) . ' LIMIT 1');
		$messageBox['HasUnread'] = $dbResult->hasRecord();
	}

	// get number of msges
	if ($message_type_id == MSG_SENT) {
		$dbResult = $db->read('SELECT count(message_id) as message_count FROM message
				WHERE sender_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber(MSG_PLAYER) . '
					AND sender_delete = ' . $db->escapeBoolean(false));
	} else {
		$dbResult = $db->read('SELECT count(message_id) as message_count FROM message
				WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber($message_type_id) . '
					AND receiver_delete = ' . $db->escapeBoolean(false));
	}
	$messageBox['MessageCount'] = $dbResult->record()->getInt('message_count');

	$container = Page::create('message_view.php');
	$container['folder_id'] = $message_type_id;
	$messageBox['ViewHref'] = $container->href();

	$container = Page::create('message_box_delete_processing.php');
	$container['folder_id'] = $message_type_id;
	$messageBox['DeleteHref'] = $container->href();
	$messageBoxes[] = $messageBox;
}

$template->assign('MessageBoxes', $messageBoxes);
