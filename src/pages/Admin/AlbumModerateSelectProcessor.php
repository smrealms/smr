<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;

class AlbumModerateSelectProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		$albumAccountID = Request::getInt('account_id');
		(new AlbumModerate($albumAccountID))->go();
	}

}
