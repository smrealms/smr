<?php declare(strict_types=1);

namespace Smr\Pages\Player;

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
	public function __construct(
		private readonly int $folderID,
		private readonly int $page = 0,
		private readonly bool $showAll = false,
	) {}

	public function build(Player $player, Template $template): void {
		$session = Session::getInstance();

		Menu::messages();

		$folderID = $this->folderID;

		$db = Database::getInstance();

		$messageBox = [];
		if ($folderID === MSG_SENT) {
			$whereClause = 'game_id = :game_id
							AND sender_id = :sender_id
							AND message_type_id = :message_type_id
							AND sender_delete = :sender_delete';
			$whereParams = [
				'sender_id' => $db->escapeNumber($player->getAccountID()),
				'message_type_id' => $db->escapeNumber(MSG_PLAYER),
				'sender_delete' => $db->escapeBoolean(false),
				'game_id' => $db->escapeNumber($player->getGameID()),
			];
			$messageBox['UnreadMessages'] = 0;
		} else {
			$whereClause = 'game_id = :game_id
							AND account_id = :account_id
							AND message_type_id = :message_type_id
							AND receiver_delete = :receiver_delete';
			$whereParams = [
				'account_id' => $db->escapeNumber($player->getAccountID()),
				'message_type_id' => $db->escapeNumber($folderID),
				'receiver_delete' => $db->escapeBoolean(false),
				'game_id' => $db->escapeNumber($player->getGameID()),
			];
			$numUnread = $db->count('message', [...$whereParams, 'msg_read' => $db->escapeBoolean(false)]);
			$messageBox['UnreadMessages'] = $numUnread;
		}
		$messageBox['TotalMessages'] = $db->count('message', $whereParams);
		$messageBox['Type'] = $folderID;

		$page = $this->page;

		if ($page > 0) {
			$previousPageHREF = new self($this->folderID, $page - 1, $this->showAll)->href();
		} else {
			$previousPageHREF = null;
		}
		if (($page + 1) * MESSAGES_PER_PAGE < $messageBox['TotalMessages']) {
			$nextPageHREF = new self($this->folderID, $page + 1, $this->showAll)->href();
		} else {
			$nextPageHREF = null;
		}

		$messageBox['Name'] = Messages::getMessageTypeNames($folderID);
		$template->pageTopic = 'Viewing ' . $messageBox['Name'];

		$preferencesIgnoreGlobalsPage = null;
		$preferencesScoutGroupPage = null;
		if ($messageBox['Type'] === MSG_GLOBAL) {
			$preferencesIgnoreGlobalsPage = new MessagePreferenceIgnoreGlobalsProcessor($folderID);
		} elseif ($messageBox['Type'] === MSG_SCOUT) {
			$preferencesScoutGroupPage = new MessagePreferenceScoutGroupProcessor($folderID);
		}

		$container = new MessageDeleteProcessor($folderID);
		$messageBox['DeleteFormHref'] = $container->href();

		// Group scout messages if they wouldn't fit on a single page
		if ($folderID === MSG_SCOUT && !$this->showAll && $messageBox['TotalMessages'] > $player->getScoutMessageGroupLimit()) {
			// get rid of all old scout messages (>48h)
			$db->write('DELETE FROM message WHERE expire_time < :now AND message_type_id = :message_type_id', [
				'now' => $db->escapeNumber(Epoch::time()),
				'message_type_id' => $db->escapeNumber(MSG_SCOUT),
			]);

			$dispContainer = new self(MSG_SCOUT, showAll: true);
			$messageBox['ShowAllHref'] = $dispContainer->href();

			[$messages, $numMessages] = displayGroupedScouts($player);
			$messageBox['NumberMessages'] = $numMessages;
			$nextPageHREF = null; // always displaying all scout messages?
		} else {
			// Normal ungrouped messages
			$messages = [];
			$dbResult = $db->read('SELECT * FROM message WHERE '
					. $whereClause . '
					ORDER BY send_time DESC
					LIMIT :limit_offset, :limit_count', [
				...$whereParams,
				'limit_offset' => $page * MESSAGES_PER_PAGE,
				'limit_count' => MESSAGES_PER_PAGE,
			]);
			foreach ($dbResult->records() as $dbRecord) {
				$messages[] = displayMessage($dbRecord->getInt('message_id'), $dbRecord->getInt('account_id'), $dbRecord->getInt('sender_id'), $player->getGameID(), $dbRecord->getString('message_text'), $dbRecord->getInt('send_time'), $dbRecord->getBoolean('msg_read'), $folderID, $player->getAccount());
			}
			$messageBox['NumberMessages'] = $dbResult->getNumRecords();
		}
		$messageBox['Messages'] = $messages;

		// This should really be part of a (pre)processing page
		if ($page === 0 && !$session->ajax) {
			$player->setMessagesRead($folderID);
		}

		$template->pageRenderer = fn() => MessageViewRenderer::render(
			PreviousPageHREF: $previousPageHREF,
			NextPageHREF: $nextPageHREF,
			PreferencesIgnoreGlobalsPage: $preferencesIgnoreGlobalsPage,
			PreferencesScoutGroupPage: $preferencesScoutGroupPage,
			MessageBox: $messageBox,
			ThisPlayer: $player,
		);
	}

}

/**
 * @return array{0: array<PlayerMessage>, 1: int}
 */
function displayGroupedScouts(Player $player): array {
	// Now display individual messages in each group
	// Perform a single query to minimize query overhead
	$db = Database::getInstance();
	$dbResult = $db->select(
		'message',
		[
			...$player->SQLID,
			'message_type_id' => MSG_SCOUT,
			'receiver_delete' => $db->escapeBoolean(false),
		],
		orderBy: ['send_time'],
		order: ['DESC'],
	);
	$groupedMessages = [];
	foreach ($dbResult->records() as $dbRecord) {
		$senderID = $dbRecord->getInt('sender_id');
		// Limit the number of messages in each group
		if (!isset($groupedMessages[$senderID]) || count($groupedMessages[$senderID]) < MESSAGE_SCOUT_GROUP_LIMIT) {
			$groupedMessages[$senderID][] = displayMessage($dbRecord->getInt('message_id'), $dbRecord->getInt('account_id'), $dbRecord->getInt('sender_id'), $player->getGameID(), $dbRecord->getString('message_text'), $dbRecord->getInt('send_time'), $dbRecord->getBoolean('msg_read'), MSG_SCOUT, $player->getAccount());
		}
	}

	// In the default view (groups), we're always displaying all messages
	$numMessages = $dbResult->getNumRecords();

	// Generate the group messages
	$dbResult = $db->read('SELECT player.*, count( message_id ) AS number, min( send_time ) as first, max( send_time) as last, sum(msg_read=\'FALSE\') as total_unread
					FROM message
					JOIN player ON player.account_id = message.sender_id AND message.game_id = player.game_id
					WHERE message.account_id = :account_id
					AND player.game_id = :game_id
					AND message_type_id = :message_type_id
					AND receiver_delete = :receiver_delete
					GROUP BY sender_id
					ORDER BY last DESC', [
		'account_id' => $db->escapeNumber($player->getAccountID()),
		'game_id' => $db->escapeNumber($player->getGameID()),
		'message_type_id' => $db->escapeNumber(MSG_SCOUT),
		'receiver_delete' => $db->escapeBoolean(false),
	]);

	$messages = [];
	foreach ($dbResult->records() as $dbRecord) {
		$senderID = $dbRecord->getInt('account_id');
		$sender = Player::getPlayer($senderID, $player->getGameID(), false, $dbRecord);
		$totalUnread = $dbRecord->getInt('total_unread');
		$message = 'Your forces have spotted ' . $sender->getBBLink() . ' passing your forces ' . pluralise($dbRecord->getInt('number'), 'time');
		$message .= ($totalUnread > 0) ? ' (' . $totalUnread . ' unread).' : '.';

		// Define a unique array so we can delete grouped messages
		$first = $dbRecord->getInt('first');
		$last = $dbRecord->getInt('last');
		$groupID = [$senderID, $first, $last];

		$dateFormat = $player->getAccount()->getDateTimeFormat();
		$messages[] = [
			'ID' => base64_encode(serialize($groupID)),
			'Text' => $message,
			'Unread' => $totalUnread > 0,
			'SendTime' => date($dateFormat, $first) . ' - ' . date($dateFormat, $last),
			'GroupedMessages' => $groupedMessages[$senderID],
		];
	}

	return [$messages, $numMessages];
}

/**
 * @return PlayerMessageNoGroups
 */
function displayMessage(int $message_id, int $receiver_id, int $sender_id, int $game_id, string $message_text, int $send_time, bool $msg_read, int $type, Account $displayAccount): array {
	$message = [];
	$message['ID'] = $message_id;
	$message['Text'] = $message_text;
	$message['Unread'] = !$msg_read;
	$message['SendTime'] = date($displayAccount->getDateTimeFormat(), $send_time);

	// Display the sender (except for scout messages)
	if ($type !== MSG_SCOUT) {
		$sender = Messages::getMessagePlayer($sender_id, $game_id, $type);
		if ($sender instanceof Player) {
			$message['Sender'] = $sender;
			$container = new SearchForTraderResult($sender->getPlayerID());
			$message['SenderDisplayName'] = create_link($container, $sender->getDisplayName());

			// Add actions that we can take on messages sent by other players.
			if ($type !== MSG_SENT) {
				$message['Actions'] = [
					'ReportHref' => new MessageReportConfirm($type, $message_id)->href(),
					'BlacklistHref' => new MessageBlacklistAddProcessor($sender_id)->href(),
					'ReplyHref' => new MessageSend($sender->getAccountID())->href(),
				];
			}
		} else {
			$message['SenderDisplayName'] = $sender;
		}
	}

	if ($type === MSG_SENT) {
		$receiver = Player::getPlayer($receiver_id, $game_id);
		$container = new SearchForTraderResult($receiver->getPlayerID());
		$message['ReceiverDisplayName'] = create_link($container, $receiver->getDisplayName());
	}

	return $message;
}
