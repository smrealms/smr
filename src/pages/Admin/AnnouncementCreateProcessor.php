<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class AnnouncementCreateProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$message = Request::get('message');
		if (Request::get('action') == 'Preview announcement') {
			$container = new AnnouncementCreate($message);
			$container->go();
		}

		// put the msg into the database
		$db = Database::getInstance();
		$db->insert('announcement', [
			'time' => $db->escapeNumber(Epoch::time()),
			'admin_id' => $db->escapeNumber($account->getAccountID()),
			'msg' => $db->escapeString($message),
		]);

		(new AdminTools())->go();
	}

}
