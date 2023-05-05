<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;

class LoginCheckAnnouncementsProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$lastLogin = $account->getLastLogin();

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM announcement WHERE time >= :last_login LIMIT 1', [
			'last_login' => $db->escapeNumber($lastLogin),
		]);
		// do we have announcements?
		if ($dbResult->hasRecord()) {
			(new LoginAnnouncements())->go();
		}
		(new LoginCheckChangelogProcessor())->go();
	}

}
