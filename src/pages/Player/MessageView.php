<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Menu;
use Smr\Messages;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Session;
use Smr\Template;

class MessageView extends PlayerPage {

	use ReusableTrait;

	public string $file = 'message_view.php';

	public function __construct(
		private readonly int $folderID,
		private readonly int $page = 0,
		private readonly bool $showAll = false
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$session = Session::getInstance();

		Menu::messages();

		$folderID = $this->folderID;

		$db = Database::getInstance();
		$whereClause = 'WHERE game_id = ' . $db->escapeNumber($player->getGameID());
		if ($folderID == MSG_SENT) {
			$whereClause .= ' AND sender_id = ' . $db->escapeNumber($player->getAccountID()) . '
							AND message_type_id = ' . $db->escapeNumber(MSG_PLAYER) . '
							AND sender_delete = ' . $db->escapeBoolean(false);
		} else {
			$whereClause .= ' AND account_id = ' . $db->escapeNumber($player->getAccountID()) . '
							AND message_type_id = ' . $db->escapeNumber($folderID) . '
							AND receiver_delete = ' . $db->escapeBoolean(false);
		}

		$messageBox = [];
		if ($folderID == MSG_SENT) {
			$messageBox['UnreadMessages'] = 0;
		} else {
			$dbResult = $db->read('SELECT count(*) as count
						FROM message ' . $whereClause . '
							AND msg_read = ' . $db->escapeBoolean(false));
			$messageBox['UnreadMessages'] = $dbResult->record()->getInt('count');
		}
		$dbResult = $db->read('SELECT count(*) as count FROM message ' . $whereClause);
		$messageBox['TotalMessages'] = $dbResult->record()->getInt('count');
		$messageBox['Type'] = $folderID;

		$page = $this->page;

		if ($page > 0) {
			$container = new self($this->folderID, $page - 1, $this->showAll);
			$template->assign('PreviousPageHREF', $container->href());
		}
		if (($page + 1) * MESSAGES_PER_PAGE < $messageBox['TotalMessages']) {
			$container = new self($this->folderID, $page + 1, $this->showAll);
			$template->assign('NextPageHREF', $container->href());
		}

		$messageBox['Name'] = Messages::getMessageTypeNames($folderID);
		$template->assign('PageTopic', 'Viewing ' . $messageBox['Name']);

		if ($messageBox['Type'] == MSG_GLOBAL || $messageBox['Type'] == MSG_SCOUT) {
			$container = new MessagePreferenceProcessor($folderID);
			$template->assign('PreferencesFormHREF', $container->href());
		}

		$container = new MessageDeleteProcessor($folderID);
		$messageBox['DeleteFormHref'] = $container->href();

		$dbResult = $db->read('SELECT * FROM message ' .
					$whereClause . '
					ORDER BY send_time DESC
					LIMIT ' . ($page * MESSAGES_PER_PAGE) . ', ' . MESSAGES_PER_PAGE);

		$messageBox['NumberMessages'] = $dbResult->getNumRecords();
		$messageBox['Messages'] = [];

		// Group scout messages if they wouldn't fit on a single page
		if ($folderID == MSG_SCOUT && !$this->showAll && $messageBox['TotalMessages'] > $player->getScoutMessageGroupLimit()) {
			// get rid of all old scout messages (>48h)
			$db->write('DELETE FROM message WHERE expire_time < ' . $db->escapeNumber(Epoch::time()) . ' AND message_type_id = ' . $db->escapeNumber(MSG_SCOUT));

			$dispContainer = new self(MSG_SCOUT, showAll: true);
			$messageBox['ShowAllHref'] = $dispContainer->href();

			displayScouts($messageBox, $player);
			$template->unassign('NextPageHREF'); // always displaying all scout messages?
		} else {
			foreach ($dbResult->records() as $dbRecord) {
				$messageBox['Messages'][] = displayMessage($dbRecord->getInt('message_id'), $dbRecord->getInt('account_id'), $dbRecord->getInt('sender_id'), $player->getGameID(), $dbRecord->getString('message_text'), $dbRecord->getInt('send_time'), $dbRecord->getBoolean('msg_read'), $folderID, $player->getAccount());
			}
		}

		// This should really be part of a (pre)processing page
		if ($page == 0 && !$session->ajax) {
			$player->setMessagesRead($folderID);
		}

		$template->assign('MessageBox', $messageBox);
	}

}


/**
 * @param array<string, mixed> $messageBox
 */
function displayScouts(array &$messageBox, AbstractPlayer $player): void {
	// Generate the group messages
	$db = Database::getInstance();
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
		$sender = Player::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), false, $dbRecord);
		$totalUnread = $dbRecord->getInt('total_unread');
		$message = 'Your forces have spotted ' . $sender->getBBLink() . ' passing your forces ' . pluralise($dbRecord->getInt('number'), 'time');
		$message .= ($totalUnread > 0) ? ' (' . $totalUnread . ' unread).' : '.';
		$messageBox['Messages'][] = displayGrouped($sender, $message, $dbRecord->getInt('first'), $dbRecord->getInt('last'), $totalUnread > 0, $player->getAccount());
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
			$groupBox['Messages'][] = displayMessage($dbRecord->getInt('message_id'), $dbRecord->getInt('account_id'), $dbRecord->getInt('sender_id'), $player->getGameID(), $dbRecord->getString('message_text'), $dbRecord->getInt('send_time'), $dbRecord->getBoolean('msg_read'), MSG_SCOUT, $player->getAccount());
		}
	}

	// In the default view (groups), we're always displaying all messages
	$messageBox['NumberMessages'] = $dbResult->getNumRecords();
}

/**
 * @return array<string, mixed>
 */
function displayGrouped(AbstractPlayer $sender, string $message_text, int $first, int $last, bool $star, Account $displayAccount): array {
	// Define a unique array so we can delete grouped messages
	$array = [
		$sender->getAccountID(),
		$first,
		$last,
	];

	$message = [];
	$message['ID'] = base64_encode(serialize($array));
	$message['Unread'] = $star;
	$message['SenderID'] = $sender->getAccountID();
	$message['SenderDisplayName'] = $sender->getLinkedDisplayName(false);
	$message['SendTime'] = date($displayAccount->getDateTimeFormat(), $first) . ' - ' . date($displayAccount->getDateTimeFormat(), $last);
	$message['Text'] = $message_text;
	return $message;
}

/**
 * @return array<string, mixed>
 */
function displayMessage(int $message_id, int $receiver_id, int $sender_id, int $game_id, string $message_text, int $send_time, bool $msg_read, int $type, Account $displayAccount): array {
	$message = [];
	$message['ID'] = $message_id;
	$message['Text'] = $message_text;
	$message['Unread'] = !$msg_read;
	$message['SendTime'] = date($displayAccount->getDateTimeFormat(), $send_time);

	// Display the sender (except for scout messages)
	if ($type != MSG_SCOUT) {
		$sender = Messages::getMessagePlayer($sender_id, $game_id, $type);
		if ($sender instanceof AbstractPlayer) {
			$message['Sender'] = $sender;
			$container = new SearchForTraderResult($sender->getPlayerID());
			$message['SenderDisplayName'] = create_link($container, $sender->getDisplayName());

			// Add actions that we can take on messages sent by other players.
			if ($type != MSG_SENT) {
				$container = new MessageReportConfirm($type, $message_id);
				$message['ReportHref'] = $container->href();

				$container = new MessageBlacklistAddProcessor($sender_id);
				$message['BlacklistHref'] = $container->href();

				$container = new MessageSend($sender->getAccountID());
				$message['ReplyHref'] = $container->href();
			}
		} else {
			$message['SenderDisplayName'] = $sender;
		}
	}

	if ($type == MSG_SENT) {
		$receiver = Player::getPlayer($receiver_id, $game_id);
		$container = new SearchForTraderResult($receiver->getPlayerID());
		$message['ReceiverDisplayName'] = create_link($container, $receiver->getDisplayName());
	}

	return $message;
}
