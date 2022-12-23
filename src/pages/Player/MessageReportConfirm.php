<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Menu;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Template;

class MessageReportConfirm extends PlayerPage {

	public string $file = 'message_notify_confirm.php';

	public function __construct(
		private readonly int $folderID,
		private readonly int $messageID
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		// get message form db
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT message_text
					FROM message
					WHERE message_id = ' . $db->escapeNumber($this->messageID));
		if (!$dbResult->hasRecord()) {
			create_error('Could not find the message you selected!');
		}

		$template->assign('MessageText', $dbResult->record()->getString('message_text'));

		$container = new MessageReportProcessor($this->folderID, $this->messageID);
		$template->assign('ProcessingHREF', $container->href());

		$template->assign('PageTopic', 'Report a Message');
		Menu::messages();
	}

}
