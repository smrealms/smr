<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class AlbumModerateSelect extends AccountPage {

	public string $file = 'admin/album_moderate_select.php';

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Moderate Photo Album');

		require_once(LIB . 'Album/album_functions.php');

		$moderateHREF = (new AlbumModerateSelectProcessor())->href();
		$template->assign('ModerateHREF', $moderateHREF);

		// Get all accounts that are eligible for moderation
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT account_id FROM album WHERE Approved = \'YES\'');
		$approved = [];
		foreach ($dbResult->records() as $dbRecord) {
			$accountId = $dbRecord->getInt('account_id');
			$approved[$accountId] = get_album_nick($accountId);
		}
		$template->assign('Approved', $approved);
	}

}
