<?php declare(strict_types=1);
require_once(get_file_loc('message.functions.inc'));
Menu::messages();

$whereClause = 'WHERE game_id = ' . $db->escapeNumber($player->getGameID());
if ($var['folder_id'] == MSG_SENT) {
	$whereClause .= ' AND sender_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND message_type_id = ' . $db->escapeNumber(MSG_PLAYER) . '
					AND sender_delete = ' . $db->escapeBoolean(false);
} else {
	$whereClause .= ' AND account_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND message_type_id = ' . $db->escapeNumber($var['folder_id']) . '
					AND receiver_delete = ' . $db->escapeBoolean(false);
}

if ($var['folder_id'] == MSG_SENT) {
	$messageBox['UnreadMessages'] = 0;
} else {
	$db->query('SELECT count(*) as count
				FROM message ' . $whereClause . '
					AND msg_read = ' . $db->escapeBoolean(false));
	$db->requireRecord();
	$messageBox['UnreadMessages'] = $db->getInt('count');
}
$db->query('SELECT count(*) as count FROM message ' . $whereClause);
$db->requireRecord();
$messageBox['TotalMessages'] = $db->getInt('count');
$messageBox['Type'] = $var['folder_id'];

$page = 0;
if (isset ($var['page'])) {
	$page = $var['page'];
}

$container = $var;
$container['page'] = $page - 1;
if ($page > 0) {
	$template->assign('PreviousPageHREF', SmrSession::getNewHREF($container));
}
$container['page'] = $page + 1;
if (($page + 1) * MESSAGES_PER_PAGE < $messageBox['TotalMessages']) {
	$template->assign('NextPageHREF', SmrSession::getNewHREF($container));
}

// remove entry for this folder from unread msg table
if ($page == 0 && !USING_AJAX) {
	$player->setMessagesRead($messageBox['Type']);
}

$messageBox['Name'] = getMessageTypeNames($var['folder_id']);
$template->assign('PageTopic', 'Viewing ' . $messageBox['Name']);

if ($messageBox['Type'] == MSG_GLOBAL || $messageBox['Type'] == MSG_SCOUT) {
	$container = create_container('message_preference_processing.php');
	transfer('folder_id');
	$template->assign('PreferencesFormHREF', SmrSession::getNewHREF($container));
}

$container = create_container('message_delete_processing.php');
transfer('folder_id');
$messageBox['DeleteFormHref'] = SmrSession::getNewHREF($container);

$db->query('SELECT * FROM message ' .
			$whereClause . '
			ORDER BY send_time DESC
			LIMIT ' . ($page * MESSAGES_PER_PAGE) . ', ' . MESSAGES_PER_PAGE);

$messageBox['NumberMessages'] = $db->getNumRows();
$messageBox['Messages'] = array();

// Group scout messages if they wouldn't fit on a single page
if ($var['folder_id'] == MSG_SCOUT && !isset($var['show_all']) && $messageBox['TotalMessages'] > $player->getScoutMessageGroupLimit()) {
	// get rid of all old scout messages (>48h)
	$db->query('DELETE FROM message WHERE expire_time < ' . $db->escapeNumber(TIME) . ' AND message_type_id = ' . $db->escapeNumber(MSG_SCOUT));

	$dispContainer = create_container('skeleton.php', 'message_view.php');
	$dispContainer['folder_id'] = MSG_SCOUT;
	$dispContainer['show_all'] = true;
	$messageBox['ShowAllHref'] = SmrSession::getNewHREF($dispContainer);

	displayScouts($messageBox, $player);
} else {
	while ($db->nextRecord()) {
		displayMessage($messageBox, $db->getInt('message_id'), $db->getInt('account_id'), $db->getInt('sender_id'), $db->getField('message_text'), $db->getInt('send_time'), $db->getBoolean('msg_read'), $var['folder_id']);
	}
}
if (!USING_AJAX) {
	$db->query('UPDATE message SET msg_read = \'TRUE\'
				WHERE message_type_id = ' . $db->escapeNumber($var['folder_id']) . ' AND ' . $player->getSQL());
}
$template->assign('MessageBox', $messageBox);


function displayScouts(&$messageBox, $player) {
	// Generate the group messages
	$db = new SmrMySqlDatabase();
	$db->query('SELECT player.*, count( message_id ) AS number, min( send_time ) as first, max( send_time) as last, sum(msg_read=\'FALSE\') as total_unread
					FROM message
					JOIN player ON player.account_id = message.sender_id AND message.game_id = player.game_id
					WHERE message.account_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND player.game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber(MSG_SCOUT) . '
					AND receiver_delete = ' . $db->escapeBoolean(false) . '
					GROUP BY sender_id
					ORDER BY last DESC');

	while ($db->nextRecord()) {
		$sender = SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID(), false, $db);
		$totalUnread = $db->getInt('total_unread');
		$message = 'Your forces have spotted ' . $sender->getBBLink() . ' passing your forces ' . $db->getInt('number') . ' ' . pluralise('time', $db->getInt('number'));
		$message .= ($totalUnread > 0) ? ' (' . $totalUnread . ' unread).' : '.';
		displayGrouped($messageBox, $sender, $message, $db->getInt('first'), $db->getInt('last'), $totalUnread > 0);
	}

	// Now display individual messages in each group
	// Perform a single query to minimize query overhead
	$db->query('SELECT message_id, account_id, sender_id, message_text, send_time, msg_read
					FROM message
					WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber(MSG_SCOUT) . '
					AND receiver_delete = ' . $db->escapeBoolean(false) . '
					ORDER BY send_time DESC');
	while ($db->nextRecord()) {
		$groupBox =& $messageBox['GroupedMessages'][$db->getInt('sender_id')];
		// Limit the number of messages in each group
		if (!isset($groupBox['Messages']) || count($groupBox['Messages']) < MESSAGE_SCOUT_GROUP_LIMIT) {
			displayMessage($groupBox, $db->getInt('message_id'), $db->getInt('account_id'), $db->getInt('sender_id'), stripslashes($db->getField('message_text')), $db->getInt('send_time'), $db->getBoolean('msg_read'), MSG_SCOUT);
		}
	}

	// In the default view (groups), we're always displaying all messages
	$messageBox['NumberMessages'] = $db->getNumRows();
	global $template;
	$template->unassign('NextPageHREF');
}

function displayGrouped(&$messageBox, SmrPlayer $sender, $message_text, $first, $last, $star) {
	// Define a unique array so we can delete grouped messages
	$array = array(
		$sender->getAccountID(),
		$first,
		$last
	);

	$message = array();
	$message['ID'] = base64_encode(serialize($array));
	$message['Unread'] = $star;
	$message['SenderID'] = $sender->getAccountID();
	$message['SenderDisplayName'] = $sender->getLinkedDisplayName(false);
	$message['SendTime'] = date(DATE_FULL_SHORT, $first) . " - " . date(DATE_FULL_SHORT, $last);
	$message['Text'] = $message_text;
	$messageBox['Messages'][] = $message;
}

function displayMessage(&$messageBox, $message_id, $receiver_id, $sender_id, $message_text, $send_time, $msg_read, $type) {
	global $player;

	$message = array();
	$message['ID'] = $message_id;
	$message['Text'] = $message_text;
	$message['Unread'] = !$msg_read;
	$message['SendTime'] = date(DATE_FULL_SHORT, $send_time);

	// Display the sender (except for scout messages)
	if ($type != MSG_SCOUT) {
		$sender = getMessagePlayer($sender_id, $player->getGameID(), $type);
		if ($sender instanceof SmrPlayer) {
			$message['Sender'] = $sender;
			$container = create_container('skeleton.php', 'trader_search_result.php');
			$container['player_id'] = $sender->getPlayerID();
			$message['SenderDisplayName'] = create_link($container, $sender->getDisplayName());

			// Add actions that we can take on messages sent by other players.
			if ($type != MSG_SENT) {
				$container = create_container('skeleton.php', 'message_notify_confirm.php');
				$container['message_id'] = $message_id;
				$container['sent_time'] = $send_time;
				$container['folder_id'] = $type;
				$message['ReportHref'] = SmrSession::getNewHREF($container);

				$container = create_container('skeleton.php', 'message_blacklist_add.php');
				$container['account_id'] = $sender_id;
				$message['BlacklistHref'] = SmrSession::getNewHREF($container);

				$container = create_container('skeleton.php', 'message_send.php');
				$container['receiver'] = $sender->getAccountID();
				$message['ReplyHref'] = SmrSession::getNewHREF($container);
			}
		} else {
			$message['SenderDisplayName'] = $sender;
		}
	}

	if ($type == MSG_SENT) {
		$receiver = SmrPlayer::getPlayer($receiver_id, $player->getGameID());
		$container = create_container('skeleton.php', 'trader_search_result.php');
		$container['player_id'] = $receiver->getPlayerID();
		$message['ReceiverDisplayName'] = create_link($container, $receiver->getDisplayName());
	}

	// Append the message to this box
	$messageBox['Messages'][] = $message;
}
