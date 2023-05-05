<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Template;

class AdminMessageSendSelect extends AccountPage {

	public string $file = 'admin/admin_message_send_select.php';

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Send Admin Message');

		$template->assign('AdminMessageChooseGameFormHref', (new AdminMessageSend())->href());

		// Get a list of all games that have not yet ended
		$activeGames = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT game_id FROM game WHERE end_time > :now ORDER BY end_time DESC', [
			'now' => $db->escapeNumber(Epoch::time()),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$activeGames[] = Game::getGame($dbRecord->getInt('game_id'));
		}
		$template->assign('ActiveGames', $activeGames);
	}

}
