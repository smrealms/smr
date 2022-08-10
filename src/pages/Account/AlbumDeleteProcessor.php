<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;

class AlbumDeleteProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		if (Request::get('action') == 'Yes') {
			$db = Database::getInstance();
			$db->write('DELETE
						FROM album
						WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));

			$db->write('DELETE
						FROM album_has_comments
						WHERE album_id = ' . $db->escapeNumber($account->getAccountID()));
		}

		$container = new AlbumEdit();
		$container->go();
	}

}
