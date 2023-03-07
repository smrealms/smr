<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class AlbumDeleteProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		if (Request::getBool('action')) {
			$db = Database::getInstance();
			$db->delete('album', [
				'account_id' => $db->escapeNumber($account->getAccountID()),
			]);

			$db->delete('album_has_comments', [
				'album_id' => $db->escapeNumber($account->getAccountID()),
			]);
		}

		$container = new AlbumEdit();
		$container->go();
	}

}
