<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class MessageReportConfirm extends PlayerPage {

	public string $file = 'message_notify_confirm.php';

	public function __construct(
		private readonly int $folderID,
		private readonly int $messageID,
	) {}

	public function build(Player $player, Template $template): void {
		// get message form db
		$db = Database::getInstance();
		$dbResult = $db->select('message', ['message_id' => $this->messageID], ['message_text']);
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
