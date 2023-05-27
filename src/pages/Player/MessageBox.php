<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Menu;
use Smr\Messages;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class MessageBox extends PlayerPage {

	use ReusableTrait;

	public string $file = 'message_box.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$db = Database::getInstance();

		Menu::messages();

		$template->assign('PageTopic', 'View Messages');

		$messageBoxes = [];
		foreach (Messages::getMessageTypeNames() as $message_type_id => $message_type_name) {
			$messageBox = [];
			$messageBox['Name'] = $message_type_name;

			// do we have unread msges in that folder?
			if ($message_type_id === MSG_SENT) {
				$messageBox['HasUnread'] = false;
			} else {
				$dbResult = $db->read('SELECT 1 FROM message
						WHERE account_id = :account_id
							AND game_id = :game_id
							AND message_type_id = :message_type_id
							AND msg_read = :msg_read
							AND receiver_delete = :receiver_delete LIMIT 1', [
					'account_id' => $db->escapeNumber($player->getAccountID()),
					'game_id' => $db->escapeNumber($player->getGameID()),
					'message_type_id' => $db->escapeNumber($message_type_id),
					'msg_read' => $db->escapeBoolean(false),
					'receiver_delete' => $db->escapeBoolean(false),
				]);
				$messageBox['HasUnread'] = $dbResult->hasRecord();
			}

			// get number of msges
			if ($message_type_id === MSG_SENT) {
				$dbResult = $db->read('SELECT count(message_id) as message_count FROM message
						WHERE sender_id = :sender_id
							AND game_id = :game_id
							AND message_type_id = :message_type_id
							AND sender_delete = :sender_delete', [
					'sender_id' => $db->escapeNumber($player->getAccountID()),
					'game_id' => $db->escapeNumber($player->getGameID()),
					'message_type_id' => $db->escapeNumber(MSG_PLAYER),
					'sender_delete' => $db->escapeBoolean(false),
				]);
			} else {
				$dbResult = $db->read('SELECT count(message_id) as message_count FROM message
						WHERE account_id = :account_id
							AND game_id = :game_id
							AND message_type_id = :message_type_id
							AND receiver_delete = :receiver_delete', [
					'account_id' => $db->escapeNumber($player->getAccountID()),
					'game_id' => $db->escapeNumber($player->getGameID()),
					'message_type_id' => $db->escapeNumber($message_type_id),
					'receiver_delete' => $db->escapeBoolean(false),
				]);
			}
			$messageBox['MessageCount'] = $dbResult->record()->getInt('message_count');

			$container = new MessageView($message_type_id);
			$messageBox['ViewHref'] = $container->href();

			$container = new MessageBoxDeleteProcessor($message_type_id);
			$messageBox['DeleteHref'] = $container->href();
			$messageBoxes[] = $messageBox;
		}

		$template->assign('MessageBoxes', $messageBoxes);
	}

}
