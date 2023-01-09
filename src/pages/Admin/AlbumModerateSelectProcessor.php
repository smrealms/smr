<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class AlbumModerateSelectProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$albumAccountID = Request::getInt('account_id');
		(new AlbumModerate($albumAccountID))->go();
	}

}
