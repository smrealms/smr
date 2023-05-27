<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class EnableGame extends AccountPage {

	public string $file = 'admin/enable_game.php';

	public function __construct(
		private readonly ?string $processingMessage = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Enable New Games');

		// If we have just forwarded from the processing file, pass its message.
		$template->assign('ProcessingMsg', $this->processingMessage);

		// Get the list of disabled games
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT game_name, game_id FROM game WHERE enabled = :enabled', [
			'enabled' => $db->escapeBoolean(false),
		]);
		$disabledGames = [];
		foreach ($dbResult->records() as $dbRecord) {
			$disabledGames[$dbRecord->getInt('game_id')] = $dbRecord->getString('game_name');
		}
		krsort($disabledGames);
		$template->assign('DisabledGames', $disabledGames);

		// Create the link to the processing file
		$linkContainer = new EnableGameProcessor();
		$template->assign('EnableGameHREF', $linkContainer->href());
	}

}
