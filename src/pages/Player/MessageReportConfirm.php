<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class MessageReportConfirm extends PlayerPage {

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

		$template->pageTopic = 'Report a Message';
		Menu::messages();
		$template->pageRenderer = fn() => MessageReportConfirmRenderer::render(
			MessageText: $dbResult->record()->getString('message_text'),
			ConfirmHREF: new MessageReportProcessor($this->folderID, $this->messageID)->href(),
			CancelHREF: new MessageView($this->folderID)->href(),
		);
	}

}
