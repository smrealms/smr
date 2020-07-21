<?php declare(strict_types=1);

require_once(get_file_loc('message.functions.inc'));
Menu::messages();

$template->assign('PageTopic', 'View Messages');

$messageBoxes = array();
foreach (getMessageTypeNames() as $message_type_id => $message_type_name) {
	$messageBox = [];
	$messageBox['Name'] = $message_type_name;

	// do we have unread msges in that folder?
	if ($message_type_id == MSG_SENT) {
		$messageBox['HasUnread'] = false;
	} else {
		$db->query('SELECT 1 FROM message
				WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber($message_type_id) . '
					AND msg_read = ' . $db->escapeBoolean(false) . '
					AND receiver_delete = ' . $db->escapeBoolean(false) . ' LIMIT 1');
		$messageBox['HasUnread'] = $db->getNumRows() != 0;
	}

	// get number of msges
	if ($message_type_id == MSG_SENT) {
		$db->query('SELECT count(message_id) as message_count FROM message
				WHERE sender_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber(MSG_PLAYER) . '
					AND sender_delete = ' . $db->escapeBoolean(false));
	} else {
		$db->query('SELECT count(message_id) as message_count FROM message
				WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber($message_type_id) . '
					AND receiver_delete = ' . $db->escapeBoolean(false));
	}
	$db->requireRecord();
	$messageBox['MessageCount'] = $db->getInt('message_count');

	$container = create_container('skeleton.php', 'message_view.php');
	$container['folder_id'] = $message_type_id;
	$messageBox['ViewHref'] = SmrSession::getNewHREF($container);

	$container = create_container('message_box_delete_processing.php');
	$container['folder_id'] = $message_type_id;
	$messageBox['DeleteHref'] = SmrSession::getNewHREF($container);
	$messageBoxes[] = $messageBox;
}

$template->assign('MessageBoxes', $messageBoxes);
