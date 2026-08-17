<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class EnableGame extends AccountPage {

	public function __construct(
		private readonly ?string $processingMessage = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Enable New Games';

		// If we have just forwarded from the processing file, pass its message.
		// Get the list of disabled games
		$db = Database::getInstance();
		$dbResult = $db->select(
			'game',
			['enabled' => $db->escapeBoolean(false)],
			['game_name', 'game_id'],
		);
		$disabledGames = [];
		foreach ($dbResult->records() as $dbRecord) {
			$disabledGames[$dbRecord->getInt('game_id')] = $dbRecord->getString('game_name');
		}
		krsort($disabledGames);
		$template->pageRenderer = fn() => EnableGameRenderer::render(
			ProcessingMsg: $this->processingMessage,
			DisabledGames: $disabledGames,
			EnableGameHREF: new EnableGameProcessor()->href(),
		);
	}

}
