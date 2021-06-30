<?php declare(strict_types=1);
require_once(get_file_loc('messages.inc.php'));

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

Menu::messages();

$db = Smr\Database::getInstance();
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
	$dbResult = $db->read('SELECT count(*) as count
				FROM message ' . $whereClause . '
					AND msg_read = ' . $db->escapeBoolean(false));
	$messageBox['UnreadMessages'] = $dbResult->record()->getInt('count');
}
$dbResult = $db->read('SELECT count(*) as count FROM message ' . $whereClause);
$messageBox['TotalMessages'] = $dbResult->record()->getInt('count');
$messageBox['Type'] = $var['folder_id'];

$page = 0;
if (isset ($var['page'])) {
	$page = $var['page'];
}

$container = Page::copy($var);
$container['page'] = $page - 1;
if ($page > 0) {
	$template->assign('PreviousPageHREF', $container->href());
}
$container['page'] = $page + 1;
if (($page + 1) * MESSAGES_PER_PAGE < $messageBox['TotalMessages']) {
	$template->assign('NextPageHREF', $container->href());
}

// remove entry for this folder from unread msg table
if ($page == 0 && !USING_AJAX) {
	$player->setMessagesRead($messageBox['Type']);
}

$messageBox['Name'] = getMessageTypeNames($var['folder_id']);
$template->assign('PageTopic', 'Viewing ' . $messageBox['Name']);

if ($messageBox['Type'] == MSG_GLOBAL || $messageBox['Type'] == MSG_SCOUT) {
	$container = Page::create('message_preference_processing.php');
	$container->addVar('folder_id');
	$template->assign('PreferencesFormHREF', $container->href());
}

$container = Page::create('message_delete_processing.php');
$container->addVar('folder_id');
$messageBox['DeleteFormHref'] = $container->href();

$dbResult = $db->read('SELECT * FROM message ' .
			$whereClause . '
			ORDER BY send_time DESC
			LIMIT ' . ($page * MESSAGES_PER_PAGE) . ', ' . MESSAGES_PER_PAGE);

$messageBox['NumberMessages'] = $dbResult->getNumRecords();
$messageBox['Messages'] = array();

// Group scout messages if they wouldn't fit on a single page
if ($var['folder_id'] == MSG_SCOUT && !isset($var['show_all']) && $messageBox['TotalMessages'] > $player->getScoutMessageGroupLimit()) {
	// get rid of all old scout messages (>48h)
	$db->write('DELETE FROM message WHERE expire_time < ' . $db->escapeNumber(Smr\Epoch::time()) . ' AND message_type_id = ' . $db->escapeNumber(MSG_SCOUT));

	$dispContainer = Page::create('skeleton.php', 'message_view.php');
	$dispContainer['folder_id'] = MSG_SCOUT;
	$dispContainer['show_all'] = true;
	$messageBox['ShowAllHref'] = $dispContainer->href();

	displayScouts($messageBox, $player);
	$template->unassign('NextPageHREF'); // always displaying all scout messages?
} else {
	foreach ($dbResult->records() as $dbRecord) {
		displayMessage($messageBox, $dbRecord->getInt('message_id'), $dbRecord->getInt('account_id'), $dbRecord->getInt('sender_id'), $player->getGameID(), $dbRecord->getString('message_text'), $dbRecord->getInt('send_time'), $dbRecord->getBoolean('msg_read'), $var['folder_id'], $player->getAccount());
	}
}
if (!USING_AJAX) {
	$db->write('UPDATE message SET msg_read = \'TRUE\'
				WHERE message_type_id = ' . $db->escapeNumber($var['folder_id']) . ' AND ' . $player->getSQL());
}
$template->assign('MessageBox', $messageBox);


function displayScouts(array &$messageBox, SmrPlayer $player) : void {
	// Generate the group messages
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT player.*, count( message_id ) AS number, min( send_time ) as first, max( send_time) as last, sum(msg_read=\'FALSE\') as total_unread
					FROM message
					JOIN player ON player.account_id = message.sender_id AND message.game_id = player.game_id
					WHERE message.account_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND player.game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber(MSG_SCOUT) . '
					AND receiver_delete = ' . $db->escapeBoolean(false) . '
					GROUP BY sender_id
					ORDER BY last DESC');

	foreach ($dbResult->records() as $dbRecord) {
		$sender = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), false, $dbRecord);
		$totalUnread = $dbRecord->getInt('total_unread');
		$message = 'Your forces have spotted ' . $sender->getBBLink() . ' passing your forces ' . $dbRecord->getInt('number') . ' ' . pluralise('time', $dbRecord->getInt('number'));
		$message .= ($totalUnread > 0) ? ' (' . $totalUnread . ' unread).' : '.';
		displayGrouped($messageBox, $sender, $message, $dbRecord->getInt('first'), $dbRecord->getInt('last'), $totalUnread > 0, $player->getAccount());
	}

	// Now display individual messages in each group
	// Perform a single query to minimize query overhead
	$dbResult = $db->read('SELECT message_id, account_id, sender_id, message_text, send_time, msg_read
					FROM message
					WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND message_type_id = ' . $db->escapeNumber(MSG_SCOUT) . '
					AND receiver_delete = ' . $db->escapeBoolean(false) . '
					ORDER BY send_time DESC');
	foreach ($dbResult->records() as $dbRecord) {
		$groupBox =& $messageBox['GroupedMessages'][$dbRecord->getInt('sender_id')];
		// Limit the number of messages in each group
		if (!isset($groupBox['Messages']) || count($groupBox['Messages']) < MESSAGE_SCOUT_GROUP_LIMIT) {
			displayMessage($groupBox, $dbRecord->getInt('message_id'), $dbRecord->getInt('account_id'), $dbRecord->getInt('sender_id'), $player->getGameID(), stripslashes($dbRecord->getString('message_text')), $dbRecord->getInt('send_time'), $dbRecord->getBoolean('msg_read'), MSG_SCOUT, $player->getAccount());
		}
	}

	// In the default view (groups), we're always displaying all messages
	$messageBox['NumberMessages'] = $dbResult->getNumRecords();
}

function displayGrouped(array &$messageBox, SmrPlayer $sender, string $message_text, int $first, int $last, bool $star, SmrAccount $displayAccount) : void {
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
	$message['SendTime'] = date($displayAccount->getDateTimeFormat(), $first) . " - " . date($displayAccount->getDateTimeFormat(), $last);
	$message['Text'] = $message_text;
	$messageBox['Messages'][] = $message;
}

function displayMessage(array &$messageBox, int $message_id, int $receiver_id, int $sender_id, int $game_id, string $message_text, int $send_time, bool $msg_read, int $type, SmrAccount $displayAccount) : void {
	$message = array();
	$message['ID'] = $message_id;
	$message['Text'] = $message_text;
	$message['Unread'] = !$msg_read;
	$message['SendTime'] = date($displayAccount->getDateTimeFormat(), $send_time);

	// Display the sender (except for scout messages)
	if ($type != MSG_SCOUT) {
		$sender = getMessagePlayer($sender_id, $game_id, $type);
		if ($sender instanceof SmrPlayer) {
			$message['Sender'] = $sender;
			$container = Page::create('skeleton.php', 'trader_search_result.php');
			$container['player_id'] = $sender->getPlayerID();
			$message['SenderDisplayName'] = create_link($container, $sender->getDisplayName());

			// Add actions that we can take on messages sent by other players.
			if ($type != MSG_SENT) {
				$container = Page::create('skeleton.php', 'message_notify_confirm.php');
				$container['message_id'] = $message_id;
				$container['sent_time'] = $send_time;
				$container['folder_id'] = $type;
				$message['ReportHref'] = $container->href();

				$container = Page::create('skeleton.php', 'message_blacklist_add.php');
				$container['account_id'] = $sender_id;
				$message['BlacklistHref'] = $container->href();

				$container = Page::create('skeleton.php', 'message_send.php');
				$container['receiver'] = $sender->getAccountID();
				$message['ReplyHref'] = $container->href();
			}
		} else {
			$message['SenderDisplayName'] = $sender;
		}
	}

	if ($type == MSG_SENT) {
		$receiver = SmrPlayer::getPlayer($receiver_id, $game_id);
		$container = Page::create('skeleton.php', 'trader_search_result.php');
		$container['player_id'] = $receiver->getPlayerID();
		$message['ReceiverDisplayName'] = create_link($container, $receiver->getDisplayName());
	}

	// Append the message to this box
	$messageBox['Messages'][] = $message;
}
