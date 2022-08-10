<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Database;
use Smr\Page\AccountPageProcessor;
use SmrAccount;

class LoginCheckAnnouncementsProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		$lastLogin = $account->getLastLogin();

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM announcement WHERE time >= ' . $db->escapeNumber($lastLogin) . ' LIMIT 1');
		// do we have announcements?
		if ($dbResult->hasRecord()) {
			(new LoginAnnouncements())->go();
		}
		(new LoginCheckChangelogProcessor())->go();
	}

}
