<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;
use SmrAccount;

class ServerStatus extends AccountPage {

	public string $file = 'admin/game_status.php';

	public function build(SmrAccount $account, Template $template): void {
		$processingHREF = (new ServerStatusProcessor())->href();
		$template->assign('ProcessingHREF', $processingHREF);

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM game_disable');
		if (!$dbResult->hasRecord()) {
			$template->assign('PageTopic', 'Close Server');
			$template->assign('ServerIsOpen', true);
		} else {
			$template->assign('PageTopic', 'Open Server');
			$template->assign('ServerIsOpen', false);
		}
	}

}
