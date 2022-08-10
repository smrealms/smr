<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;

class ReportedMessageDeleteProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		if (!Request::has('notify_id')) {
			create_error('You must choose the messages you want to delete.');
		}

		$db = Database::getInstance();
		$db->write('DELETE FROM message_notify WHERE notify_id IN (' . $db->escapeArray(Request::getIntArray('notify_id')) . ')');

		(new ReportedMessageView())->go();
	}

}
