<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class ReportedMessageDeleteProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		if (!Request::has('notify_id')) {
			create_error('You must choose the messages you want to delete.');
		}

		$db = Database::getInstance();
		$db->write('DELETE FROM message_notify WHERE notify_id IN (:notify_ids)', [
			'notify_ids' => $db->escapeArray(Request::getIntArray('notify_id')),
		]);

		(new ReportedMessageView())->go();
	}

}
