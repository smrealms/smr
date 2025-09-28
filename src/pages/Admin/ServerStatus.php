<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class ServerStatus extends AccountPage {

	public string $file = 'admin/game_status.php';

	public function build(Account $account, Template $template): void {
		$template->assign('ProcessingPage', new ServerStatusProcessor());

		$db = Database::getInstance();
		$dbResult = $db->select('game_disable');
		if (!$dbResult->hasRecord()) {
			$template->assign('PageTopic', 'Close Server');
			$template->assign('ServerIsOpen', true);
		} else {
			$template->assign('PageTopic', 'Open Server');
			$template->assign('ServerIsOpen', false);
		}
	}

}
