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
			$whereClause = 'sender_player_id = :sender_player_id
							AND message_type_id = :message_type_id
							AND sender_delete = :sender_delete';
			$whereParams = [
				'sender_player_id' => $db->escapeNumber($player->getPlayerID()),
				'message_type_id' => $db->escapeNumber(MSG_PLAYER),
				'sender_delete' => $db->escapeBoolean(false),
			];
			$messageBox['UnreadMessages'] = 0;
		} else {
			$whereClause = 'player_id = :player_id
							AND message_type_id = :message_type_id
							AND receiver_delete = :receiver_delete';
			$whereParams = [
				'player_id' => $db->escapeNumber($player->getPlayerID()),
				'message_type_id' => $db->escapeNumber($folderID),
				'receiver_delete' => $db->escapeBoolean(false),
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
				$messages[] = displayMessage(
					message_id: $dbRecord->getInt('message_id'),
					receiverPlayerID: $dbRecord->getInt('player_id'),
					senderPlayerID: $dbRecord->getInt('sender_player_id'),
					message_text: $dbRecord->getString('message_text'),
					send_time: $dbRecord->getInt('send_time'),
					msg_read: $dbRecord->getBoolean('msg_read'),
					type: $folderID,
					displayAccount: $player->getAccount(),
				);
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
		$senderPlayerID = $dbRecord->getInt('sender_player_id');
		// Limit the number of messages in each group
		if (!isset($groupedMessages[$senderPlayerID]) || count($groupedMessages[$senderPlayerID]) < MESSAGE_SCOUT_GROUP_LIMIT) {
			$groupedMessages[$senderPlayerID][] = displayMessage(
				message_id: $dbRecord->getInt('message_id'),
				receiverPlayerID: $dbRecord->getInt('player_id'),
				senderPlayerID: $dbRecord->getInt('sender_player_id'),
				message_text: $dbRecord->getString('message_text'),
				send_time: $dbRecord->getInt('send_time'),
				msg_read: $dbRecord->getBoolean('msg_read'),
				type: MSG_SCOUT,
				displayAccount: $player->getAccount(),
			);
		}
	}

	// In the default view (groups), we're always displaying all messages
	$numMessages = $dbResult->getNumRecords();

	// Generate the group messages
	$dbResult = $db->read('SELECT player.*, count( message_id ) AS number, min( send_time ) as first, max( send_time) as last, sum(msg_read=\'FALSE\') as total_unread
					FROM message
					JOIN player ON player.player_id = message.sender_player_id
					WHERE message.player_id = :player_id
					AND message_type_id = :message_type_id
					AND receiver_delete = :receiver_delete
					GROUP BY sender_player_id
					ORDER BY last DESC', [
		'player_id' => $db->escapeNumber($player->getPlayerID()),
		'message_type_id' => $db->escapeNumber(MSG_SCOUT),
		'receiver_delete' => $db->escapeBoolean(false),
	]);

	$messages = [];
	foreach ($dbResult->records() as $dbRecord) {
		$senderPlayerID = $dbRecord->getInt('player_id');
		$sender = Player::getPlayer(
			playerID: $senderPlayerID,
			dbRecord: $dbRecord,
		);
		$totalUnread = $dbRecord->getInt('total_unread');
		$message = 'Your forces have spotted ' . $sender->getBBLink() . ' passing your forces ' . pluralise($dbRecord->getInt('number'), 'time');
		$message .= ($totalUnread > 0) ? ' (' . $totalUnread . ' unread).' : '.';

		// Define a unique array so we can delete grouped messages
		$first = $dbRecord->getInt('first');
		$last = $dbRecord->getInt('last');
		$groupID = [$senderPlayerID, $first, $last];

		$dateFormat = $player->getAccount()->getDateTimeFormat();
		$messages[] = [
			'ID' => base64_encode(serialize($groupID)),
			'Text' => $message,
			'Unread' => $totalUnread > 0,
			'SendTime' => date($dateFormat, $first) . ' - ' . date($dateFormat, $last),
			'GroupedMessages' => $groupedMessages[$senderPlayerID],
		];
	}

	return [$messages, $numMessages];
}

/**
 * @return PlayerMessageNoGroups
 */
function displayMessage(int $message_id, int $receiverPlayerID, int $senderPlayerID, string $message_text, int $send_time, bool $msg_read, int $type, Account $displayAccount): array {
	$message = [];
	$message['ID'] = $message_id;
	$message['Text'] = $message_text;
	$message['Unread'] = !$msg_read;
	$message['SendTime'] = date($displayAccount->getDateTimeFormat(), $send_time);

	// Display the sender (except for scout messages)
	if ($type !== MSG_SCOUT) {
		$sender = Messages::getMessagePlayer($senderPlayerID, $type);
		if ($sender instanceof Player) {
			$message['Sender'] = $sender;
			$container = new SearchForTraderResult($sender->getPlayerNumber());
			$message['SenderDisplayName'] = create_link($container, $sender->getDisplayName());

			// Add actions that we can take on messages sent by other players.
			if ($type !== MSG_SENT) {
				$message['Actions'] = [
					'ReportHref' => new MessageReportConfirm($type, $message_id)->href(),
					'BlacklistHref' => new MessageBlacklistAddProcessor($senderPlayerID)->href(),
					'ReplyHref' => new MessageSend($sender->getPlayerID())->href(),
				];
			}
		} else {
			$message['SenderDisplayName'] = $sender;
		}
	}

	if ($type === MSG_SENT) {
		$receiver = Player::getPlayer($receiverPlayerID);
		$container = new SearchForTraderResult($receiver->getPlayerNumber());
		$message['ReceiverDisplayName'] = create_link($container, $receiver->getDisplayName());
	}

	return $message;
}
