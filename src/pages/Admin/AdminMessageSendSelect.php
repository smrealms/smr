<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Template;

class AdminMessageSendSelect extends AccountPage {

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Send Admin Message';

		// Get a list of all games that have not yet ended
		$activeGames = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT game_id FROM game WHERE end_time > :now ORDER BY end_time DESC', [
			'now' => $db->escapeNumber(Epoch::time()),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$activeGames[] = Game::getGame($dbRecord->getInt('game_id'));
		}
		$template->pageRenderer = fn() => AdminMessageSendSelectRenderer::render(
			AdminMessageChooseGameFormHref: new AdminMessageSend()->href(),
			ActiveGames: $activeGames,
		);
	}

}
