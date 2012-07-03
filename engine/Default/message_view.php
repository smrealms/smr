<?php
require_once(get_file_loc('menu.inc'));
create_message_menu();

if (!isset ($var['folder_id'])) {
	$template->assign('PageTopic', 'View Messages');

	$db2 = new SmrMySqlDatabase();

	require_once(get_file_loc('council.inc'));

	$db->query('SELECT 1 FROM message
				WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND message_type_id = ' . $db->escapeNumber(MSG_POLITICAL) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND receiver_delete = ' . $db->escapeBoolean(false) . '
					AND receiver_delete = ' . $db->escapeBoolean(false) . '
				LIMIT 1');
	if ($db->getNumRows() || $player->isOnCouncil()) {
		$db->query('SELECT * FROM message_type
					ORDER BY message_type_id');
	}
	else {
		$db->query('SELECT * FROM message_type
					WHERE message_type_id != 5
					ORDER BY message_type_id');
	}
	$messageBoxes = array ();
	while ($db->nextRecord()) {
		$message_type_id = $db->getField('message_type_id');
		$messageBox['Name'] = $db->getField('message_type_name');

		// do we have unread msges in that folder?
		$db2->query('SELECT 1 FROM message
					WHERE account_id = ' . $db2->escapeNumber($player->getAccountID()) . '
						AND game_id = ' . $db2->escapeNumber($player->getGameID()) . '
						AND message_type_id = ' . $db2->escapeNumber($message_type_id) . '
						AND msg_read = ' . $db2->escapeBoolean(false) . '
						AND receiver_delete = ' . $db2->escapeBoolean(false) . ' LIMIT 1');
		$messageBox['HasUnread'] = $db2->getNumRows() != 0;

		$messageBox['MessageCount'] = 0;
		// get number of msges
		$db2->query('SELECT count(message_id) as message_count FROM message
					WHERE account_id = ' . $db2->escapeNumber($player->getAccountID()) . '
						AND game_id = ' . $db2->escapeNumber($player->getGameID()) . '
						AND message_type_id = ' . $db2->escapeNumber($message_type_id) . '
						AND receiver_delete = ' . $db2->escapeBoolean(false));
		if ($db2->nextRecord()) {
			$messageBox['MessageCount'] = $db2->getField('message_count');
		}

		$container = create_container('skeleton.php', 'message_view.php');
		$container['folder_id'] = $message_type_id;
		$messageBox['ViewHref'] = SmrSession::getNewHREF($container);

		$container = create_container('message_delete_processing.php');
		$container['folder_id'] = $message_type_id;
		$messageBox['DeleteHref'] = SmrSession::getNewHREF($container);
		$messageBoxes[] = $messageBox;
	}

	$messageBox = array ();
	$messageBox['MessageCount'] = 0;
	$db->query('SELECT count(message_id) as count FROM message
				WHERE sender_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber(MSG_PLAYER) . '
					AND sender_delete = ' . $db->escapeBoolean(false));
	if ($db->nextRecord()) {
		$messageBox['MessageCount'] = $db->getField('count');
	}
	$messageBox['Name'] = 'Sent Messages';
	$messageBox['HasUnread'] = false;
	$container = create_container('skeleton.php', 'message_view.php');
	$container['folder_id'] = MSG_SENT;
	$messageBox['ViewHref'] = SmrSession::getNewHREF($container);

	$container = create_container('message_delete_processing.php');
	$container['folder_id'] = MSG_SENT;
	$messageBox['DeleteHref'] = SmrSession::getNewHREF($container);
	$messageBoxes[] = $messageBox;

	$template->assignByRef('MessageBoxes', $messageBoxes);

	$container = create_container('skeleton.php','message_blacklist.php');
	$container['folder_id'] = $message_type_id;
	$template->assignByRef('ManageBlacklistLink', SmrSession::getNewHREF($container));
}
else {
	$whereClause = 'WHERE game_id = ' . $db->escapeNumber($player->getGameID());
	if ($var['folder_id'] == MSG_SENT) {
		$whereClause .= ' AND sender_id = ' . $db->escapeNumber($player->getAccountID()) . '
						AND message_type_id = ' . $db->escapeNumber(MSG_PLAYER) . '
						AND sender_delete = ' . $db->escapeBoolean(false);
	}
	else {
		$whereClause .= ' AND account_id = ' . $db->escapeNumber($player->getAccountID()) . '
						AND message_type_id = ' . $db->escapeNumber($var['folder_id']) . '
						AND receiver_delete = ' . $db->escapeBoolean(false);
	}

	if ($var['folder_id'] == MSG_SENT) {
		$messageBox['UnreadMessages'] = 0;
	}
	else {
		$db->query('SELECT count(*) as count
					FROM message ' . $whereClause . '
						AND msg_read = ' . $db->escapeBoolean(false));
		$db->nextRecord();
		$messageBox['UnreadMessages'] = $db->getField('count');
	}
	$db->query('SELECT count(*) as count FROM message ' . $whereClause);
	$db->nextRecord();
	$messageBox['TotalMessages'] = $db->getField('count');
	$messageBox['Type'] = $var['folder_id'];

	$page = 0;
	if (isset ($var['page'])) {
		$page = $var['page'];
	}

	$container = $var;
	$container['page'] = $page -1;
	if ($page > 0) {
		$template->assign('PreviousPageHREF', SmrSession::getNewHREF($container));
	}
	$container['page'] = $page +1;
	if (($page +1) * MESSAGES_PER_PAGE < $messageBox['TotalMessages']) {
		$template->assign('NextPageHREF', SmrSession::getNewHREF($container));
	}

	// remove entry for this folder from unread msg table
	if ($page == 0 && !USING_AJAX) {
		$player->setMessagesRead($messageBox['Type']);
	}

	if ($var['folder_id'] == MSG_SENT) {
		$messageBox['Name'] = 'Sent Messages';
	}
	else {
		$db->query('SELECT * FROM message_type
					WHERE message_type_id = ' . $var['folder_id']);
		if ($db->nextRecord()) {
			$messageBox['Name'] = $db->getField('message_type_name');
		}
	}
	$template->assign('PageTopic', 'Viewing ' . $messageBox['Name']);

	if ($messageBox['Type'] == MSG_GLOBAL) {
		$template->assign('IgnoreGlobalsFormHref', SmrSession::getNewHREF(create_container('message_global_ignore.php')));
	}

	$container = create_container('message_delete_processing.php');
	transfer('folder_id');
	$messageBox['DeleteFormHref'] = SmrSession::getNewHREF($container);

	$db->query('SELECT * FROM message ' .
				$whereClause . '
				ORDER BY send_time DESC
				LIMIT ' . ($page * MESSAGES_PER_PAGE) . ', ' . MESSAGES_PER_PAGE);

	$messageBox['NumberMessages'] = $db->getNumRows();
	$messageBox['Messages'] = array ();

	if ($var['folder_id'] == MSG_SCOUT && !isset ($var['show_all'])) {
		// get rid of all old scout messages (>48h)
		$db->query('DELETE FROM message WHERE expire_time < ' . $db->escapeNumber(TIME) . ' AND message_type_id = ' . $db->escapeNumber(MSG_SCOUT));

		if ($messageBox['UnreadMessages'] > MESSAGE_SCOUT_GROUP_LIMIT || $messageBox['NumberMessages'] - $messageBox['UnreadMessages'] > MESSAGE_SCOUT_GROUP_LIMIT) {
			$dispContainer = create_container('skeleton.php', 'message_view.php');
			$dispContainer['folder_id'] = MSG_SCOUT;
			$dispContainer['show_all'] = true;
			$messageBox['ShowAllHref'] = SmrSession::getNewHREF($dispContainer);
		}
		$db2 = new SmrMySqlDatabase();
		displayScouts($db2, $messageBox, $player, false, $messageBox['UnreadMessages'] > MESSAGE_SCOUT_GROUP_LIMIT);
		displayScouts($db2, $messageBox, $player, true, $messageBox['NumberMessages'] - $messageBox['UnreadMessages'] > MESSAGE_SCOUT_GROUP_LIMIT);
	}
	else {
		while ($db->nextRecord()) {
			displayMessage($messageBox, $db->getField('message_id'), $db->getField('account_id'), $db->getField('sender_id'), $db->getField('message_text'), $db->getField('send_time'), $db->getField('msg_read'), $var['folder_id'], $var['folder_id'] == 0);
		}
	}
	if (!USING_AJAX) {
		$db->query('UPDATE message SET msg_read = \'TRUE\'
					WHERE message_type_id = ' . $db->escapeNumber($var['folder_id']) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id = ' . $db->escapeNumber($player->getAccountID()));
	}
	$template->assignByRef('MessageBox', $messageBox);
}

function displayScouts(&$db, &$messageBox, &$player, $read, $group) {
	if ($group) {
		//here we group new messages
		$query = 'SELECT alignment, player_id, sender_id, player_name AS sender, count( message_id ) AS number, min( send_time ) as first, max( send_time) as last, msg_read
					FROM message
					JOIN player ON player.account_id = message.sender_id AND message.game_id = player.game_id
					WHERE message.account_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND player.game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber(MSG_SCOUT) . '
					AND receiver_delete = ' . $db->escapeBoolean(false) . '
					AND msg_read = ' . $db->escapeBoolean($read) . '
					GROUP BY sender_id, msg_read
					ORDER BY send_time DESC';

		$db->query($query);
		while ($db->nextRecord()) {
			//display grouped stuff (allow for deletion)
			$playerName = get_colored_text($db->getField('alignment'), stripslashes($db->getField('sender')) . ' (' . $db->getField('player_id') . ')');
			$message = 'Your forces have spotted ' . $playerName . ' passing your forces ' . $db->getField('number') . ' times.';
			displayGrouped($messageBox, $playerName, $db->getField('player_id'), $db->getField('sender_id'), $message, $db->getField('first'), $db->getField('last'), $db->getField('msg_read') == 'FALSE');
		}
	}
	else {
		//not enough to group, display separately
		$query = 'SELECT message_id, account_id, sender_id, message_text, send_time, msg_read
					FROM message
					WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber(MSG_SCOUT) . '
					AND receiver_delete = ' . $db->escapeBoolean(false) . '
					AND msg_read = ' . $db->escapeBoolean($read) . '
					ORDER BY send_time DESC';
		$db->query($query);
		while ($db->nextRecord()) {
			displayMessage($messageBox, $db->getField('message_id'), $db->getField('account_id'), $db->getField('sender_id'), stripslashes($db->getField('message_text')), $db->getField('send_time'), $db->getField('msg_read'), MSG_SCOUT);
		}
	}
}

function displayGrouped(&$messageBox, $playerName, $player_id, $sender_id, $message_text, $first, $last, $star) {
	$array = array (
		$sender_id,
		$first,
		$last
	);

	$message = array ();
	$message['ID'] = base64_encode(serialize($array));
	$message['Unread'] = $star;
	$container = create_container('skeleton.php', 'trader_search_result.php');
	$container['player_id'] = $player_id;
	$message['SenderDisplayName'] = create_link($container, $playerName);
	$message['FirstSendTime'] = $first;
	$message['LastSendTime'] = $last;
	$message['Text'] = $message_text;
	$messageBox['GroupedMessages'][] = $message;
}
function displayMessage(&$messageBox, $message_id, $receiver_id, $sender_id, $message_text, $send_time, $msg_read, $type, $sentMessage = false) {
	require_once(get_file_loc('message.functions.inc'));
	global $player, $account;

	$message = array ();

	$sender = false;
	$senderName =& getMessagePlayer($sender_id,$player->getGameID(),$type);
	if ($senderName instanceof SmrPlayer) {
		$sender =& $senderName;
		unset($senderName);
		$replace = explode('?', $message_text);
		foreach ($replace as $key => $timea) {
			if ($sender_id > 0 && $timea != '' && ($final = strtotime($timea)) !== false) { //WARNING: Expects PHP 5.1.0 or later
				$send_acc = & $sender->getAccount();
				$final += ($account->getOffset() * 3600 - $send_acc->getOffset() * 3600);
				$message_text = str_replace('?' . $timea . '?', date(DATE_FULL_SHORT, $final), $message_text);
			}
		}
		$container = create_container('skeleton.php', 'trader_search_result.php');
		$container['player_id'] = $sender->getPlayerID();
		$senderName =& create_link($container, $sender->getDisplayName());
	}

	$container = create_container('skeleton.php', 'message_notify_confirm.php');
	$container['message_id'] = $message_id;
	$container['sent_time'] = $send_time;
	$message['ReportHref'] = SmrSession::getNewHREF($container);
	if (is_object($sender)) {
		$container = create_container('skeleton.php', 'message_blacklist_add.php');
		$container['account_id'] = $sender_id;
		$message['BlacklistHref'] = SmrSession::getNewHREF($container);

		$container = create_container('skeleton.php', 'message_send.php');
		$container['receiver'] = $sender->getAccountID();
		$message['ReplyHref'] = SmrSession::getNewHREF($container);

		$message['Sender'] = & $sender;
	}

	$message['ID'] = $message_id;
	$message['Text'] = $message_text;
	$message['SenderDisplayName'] = $senderName;

	$receiver = & SmrPlayer::getPlayer($receiver_id, $player->getGameID());
	if ($sentMessage && is_object($receiver)) {
		$container = create_container('skeleton.php', 'trader_search_result.php');
		$container['player_id'] = $receiver->getPlayerID();
		$message['ReceiverDisplayName'] = create_link($container, $receiver->getDisplayName());
	}

	$message['Unread'] = $msg_read == 'FALSE';
	$message['SendTime'] = $send_time;
	$messageBox['Messages'][] = & $message;
}
?>