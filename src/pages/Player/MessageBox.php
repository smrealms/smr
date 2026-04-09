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
				$dbResult = $db->select(
					'message',
					[
						...$player->SQLID,
						'message_type_id' => $message_type_id,
						'msg_read' => $db->escapeBoolean(false),
						'receiver_delete' => $db->escapeBoolean(false),
					],
					limit: 1,
				);
				$messageBox['HasUnread'] = $dbResult->hasRecord();
			}

			// get number of msges
			if ($message_type_id === MSG_SENT) {
				$count = $db->count('message', [
					'sender_id' => $player->getAccountID(),
					'game_id' => $player->getGameID(),
					'message_type_id' => MSG_PLAYER,
					'sender_delete' => $db->escapeBoolean(false),
				]);
			} else {
				$count = $db->count('message', [
					...$player->SQLID,
					'message_type_id' => $message_type_id,
					'receiver_delete' => $db->escapeBoolean(false),
				]);
			}
			$messageBox['MessageCount'] = $count;

			$container = new MessageView($message_type_id);
			$messageBox['ViewHref'] = $container->href();

			$container = new MessageBoxDeleteProcessor($message_type_id);
			$messageBox['DeleteHref'] = $container->href();
			$messageBoxes[] = $messageBox;
		}

		$template->assign('MessageBoxes', $messageBoxes);
	}

}
