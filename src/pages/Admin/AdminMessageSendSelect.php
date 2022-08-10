<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPage;
use Smr\Template;
use SmrAccount;
use SmrGame;

class AdminMessageSendSelect extends AccountPage {

	public string $file = 'admin/admin_message_send_select.php';

	public function build(SmrAccount $account, Template $template): void {
		$template->assign('PageTopic', 'Send Admin Message');

		$template->assign('AdminMessageChooseGameFormHref', (new AdminMessageSend())->href());

		// Get a list of all games that have not yet ended
		$activeGames = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT game_id FROM game WHERE end_time > ' . $db->escapeNumber(Epoch::time()) . ' ORDER BY end_time DESC');
		foreach ($dbResult->records() as $dbRecord) {
			$activeGames[] = SmrGame::getGame($dbRecord->getInt('game_id'));
		}
		$template->assign('ActiveGames', $activeGames);
	}

}
