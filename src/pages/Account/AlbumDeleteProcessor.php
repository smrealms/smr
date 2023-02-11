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
			$db->write('DELETE
						FROM album
						WHERE account_id = :account_id', [
				'account_id' => $db->escapeNumber($account->getAccountID()),
			]);

			$db->write('DELETE
						FROM album_has_comments
						WHERE album_id = :album_id', [
				'album_id' => $db->escapeNumber($account->getAccountID()),
			]);
		}

		$container = new AlbumEdit();
		$container->go();
	}

}
